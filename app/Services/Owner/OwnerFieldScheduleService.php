<?php

namespace App\Services\Owner;

use App\Models\Field;
use App\Models\FieldSchedule;
use App\Models\Owner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class OwnerFieldScheduleService
{
    public function addWeeklySchedule(Owner $owner, int $fieldId, array $data): FieldSchedule
    {
        $field = $this->getOwnerField($owner, $fieldId);

        return FieldSchedule::query()->create([
            'field_id' => $field->id,
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'is_active' => true,
        ]);
    }

    public function updateSchedule(Owner $owner, int $scheduleId, array $data): FieldSchedule
    {
        $schedule = FieldSchedule::query()
            ->whereKey($scheduleId)
            ->whereHas('field', fn ($q) => $q->where('owner_id', $owner->id))
            ->first();

        if (! $schedule) {
            throw new ModelNotFoundException('Schedule not found.');
        }

        if (isset($data['start_time'])) {
            $schedule->start_time = $data['start_time'];
        }
        if (isset($data['end_time'])) {
            $schedule->end_time = $data['end_time'];
        }

        $schedule->save();

        return $schedule;
    }

    /**
     * @return Collection<int, FieldSchedule>
     */
    public function listSchedules(Owner $owner, int $fieldId): Collection
    {
        $field = $this->getOwnerField($owner, $fieldId);

        return $field->schedules()->orderBy('day_of_week')->get();
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
}
