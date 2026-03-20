<?php

namespace App\Services\Owner;

use App\Enums\FieldTimeSlotStatus;
use App\Models\Field;
use App\Models\FieldSchedule;
use App\Models\FieldTimeSlot;
use App\Models\Owner;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

class OwnerFieldTimeSlotService
{
    /**
     * @return array{created: int, skipped: int}
     */
    public function generateSlots(Owner $owner, int $fieldId, string $startDate, string $endDate): array
    {
        $field = $this->getOwnerField($owner, $fieldId);
        $slotMinutes = (int) ($field->slot_duration?->value ?? $field->slot_duration);
        $pricePerHour = (float) $field->price_per_hour;
        $slotPrice = round($pricePerHour * ($slotMinutes / 60), 2);

        $schedules = FieldSchedule::query()
            ->where('field_id', $field->id)
            ->where('is_active', true)
            ->get()
            ->groupBy('day_of_week');

        $created = 0;
        $skipped = 0;

        $start = CarbonImmutable::parse($startDate)->startOfDay();
        $end = CarbonImmutable::parse($endDate)->startOfDay();

        DB::transaction(function () use ($field, $slotMinutes, $slotPrice, $schedules, $start, $end, &$created, &$skipped) {
            for ($date = $start; $date->lte($end); $date = $date->addDay()) {
                $dow = (int) $date->dayOfWeek; // 0-6

                /** @var Collection<int, FieldSchedule> $daySchedules */
                $daySchedules = $schedules->get($dow, collect());
                if ($daySchedules->isEmpty()) {
                    continue;
                }

                foreach ($daySchedules as $schedule) {
                    $windowStart = CarbonImmutable::parse($date->toDateString().' '.$schedule->start_time);
                    $windowEnd = CarbonImmutable::parse($date->toDateString().' '.$schedule->end_time);

                    for ($cursor = $windowStart; $cursor->addMinutes($slotMinutes)->lte($windowEnd); $cursor = $cursor->addMinutes($slotMinutes)) {
                        $slotStart = $cursor->format('H:i:s');
                        $slotEnd = $cursor->addMinutes($slotMinutes)->format('H:i:s');

                        $exists = FieldTimeSlot::query()
                            ->where('field_id', $field->id)
                            ->where('slot_date', $date->toDateString())
                            ->where('start_time', $slotStart)
                            ->exists();

                        if ($exists) {
                            $skipped++;

                            continue;
                        }

                        FieldTimeSlot::query()->create([
                            'field_id' => $field->id,
                            'slot_date' => $date->toDateString(),
                            'start_time' => $slotStart,
                            'end_time' => $slotEnd,
                            'price' => $slotPrice,
                            'status' => FieldTimeSlotStatus::Available,
                        ]);

                        $created++;
                    }
                }
            }
        });

        return ['created' => $created, 'skipped' => $skipped];
    }

    public function createManualSlot(Owner $owner, int $fieldId, array $data): FieldTimeSlot
    {
        $field = $this->getOwnerField($owner, $fieldId);
        $slotMinutes = (int) ($field->slot_duration?->value ?? $field->slot_duration);
        $pricePerHour = (float) $field->price_per_hour;
        $computedPrice = round($pricePerHour * ($slotMinutes / 60), 2);

        $price = isset($data['price']) ? (float) $data['price'] : $computedPrice;

        $exists = FieldTimeSlot::query()
            ->where('field_id', $field->id)
            ->where('slot_date', $data['slot_date'])
            ->where('start_time', $data['start_time'].':00')
            ->exists();

        if ($exists) {
            throw new \InvalidArgumentException('Slot already exists for this time.');
        }

        return FieldTimeSlot::query()->create([
            'field_id' => $field->id,
            'slot_date' => $data['slot_date'],
            'start_time' => $data['start_time'].':00',
            'end_time' => $data['end_time'].':00',
            'price' => $price,
            'status' => FieldTimeSlotStatus::Available,
        ]);
    }

    /**
     * @return Collection<int, FieldTimeSlot>
     */
    public function getSlotsByDate(Owner $owner, int $fieldId, string $date): Collection
    {
        $field = $this->getOwnerField($owner, $fieldId);

        return FieldTimeSlot::query()
            ->where('field_id', $field->id)
            ->where('slot_date', $date)
            ->orderBy('start_time')
            ->get();
    }

    public function blockSlot(Owner $owner, int $slotId, ?string $reason): FieldTimeSlot
    {
        $slot = $this->getOwnerSlot($owner, $slotId);

        if ($slot->status === FieldTimeSlotStatus::Booked) {
            throw new \InvalidArgumentException('Booked slot cannot be blocked.');
        }

        $slot->status = FieldTimeSlotStatus::Blocked;
        $slot->block_reason = $reason;
        $slot->save();

        return $slot;
    }

    public function unblockSlot(Owner $owner, int $slotId): FieldTimeSlot
    {
        $slot = $this->getOwnerSlot($owner, $slotId);

        if ($slot->status !== FieldTimeSlotStatus::Blocked) {
            return $slot;
        }

        $slot->status = FieldTimeSlotStatus::Available;
        $slot->block_reason = null;
        $slot->save();

        return $slot;
    }

    /**
     * @return array{updated: int}
     */
    public function bulkBlock(Owner $owner, int $fieldId, array $data): array
    {
        $field = $this->getOwnerField($owner, $fieldId);

        $query = FieldTimeSlot::query()
            ->where('field_id', $field->id)
            ->whereBetween('slot_date', [$data['start_date'], $data['end_date']])
            ->where('status', '!=', FieldTimeSlotStatus::Booked->value);

        if (! empty($data['start_time'])) {
            $query->where('start_time', '>=', $data['start_time'].':00');
        }
        if (! empty($data['end_time'])) {
            $query->where('end_time', '<=', $data['end_time'].':00');
        }

        $updated = $query->update([
            'status' => FieldTimeSlotStatus::Blocked->value,
            'block_reason' => $data['reason'] ?? null,
        ]);

        return ['updated' => $updated];
    }

    public function deleteSlot(Owner $owner, int $slotId): void
    {
        $slot = $this->getOwnerSlot($owner, $slotId);

        if ($slot->status === FieldTimeSlotStatus::Booked || $slot->bookingSlots()->exists()) {
            throw new \InvalidArgumentException('Booked slot cannot be deleted.');
        }

        $slot->delete();
    }

    private function getOwnerField(Owner $owner, int $fieldId): Field
    {
        $field = Field::query()
            ->whereKey($fieldId)
            ->where('owner_id', $owner->id)
            ->first();

        if (! $field) {
            throw new ModelNotFoundException('Field not found.');
        }

        return $field;
    }

    private function getOwnerSlot(Owner $owner, int $slotId): FieldTimeSlot
    {
        $slot = FieldTimeSlot::query()
            ->whereKey($slotId)
            ->whereHas('field', fn ($q) => $q->where('owner_id', $owner->id))
            ->first();

        if (! $slot) {
            throw new ModelNotFoundException('Slot not found.');
        }

        return $slot;
    }

    // No time parsing helper needed.
}
