<?php

namespace App\Services;

use App\Enums\OwnerPayoutAccountStatus;
use App\Enums\OwnerStatus;
use App\Enums\OwnerVerificationLevel;
use App\Enums\OwnerVerificationStatus;
use App\Mail\OwnerOtpMail;
use App\Models\Owner;
use App\Models\OwnerPayoutAccount;
use App\Models\OwnerVerification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;

class OwnerRegistrationService
{
    private const OTP_TTL_MINUTES = 15;

    private const OTP_CACHE_PREFIX = 'owner_otp:';

    public function register(array $data): Owner
    {
        $owner = Owner::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
            'status' => OwnerStatus::Pending,
            'verification_level' => OwnerVerificationLevel::Basic,
        ]);

        $code = $this->generateOtp();
        Cache::put(self::OTP_CACHE_PREFIX . $owner->email, $code, now()->addMinutes(self::OTP_TTL_MINUTES));
        Mail::to($owner->email)->send(new OwnerOtpMail($code));

        return $owner;
    }

    /**
     * @return array{owner: Owner, token: string}
     */
    public function verifyOtp(string $email, string $otpCode): array
    {
        $key = self::OTP_CACHE_PREFIX . $email;
        $cached = Cache::get($key);

        if ($cached === null || $cached !== $otpCode) {
            throw new InvalidArgumentException('Invalid or expired OTP.');
        }

        Cache::forget($key);

        $owner = Owner::query()->where('email', $email)->firstOrFail();
        $owner->update(['email_verified_at' => now()]);
        $token = $owner->createToken('owner-api')->plainTextToken;

        return [
            'owner' => $owner,
            'token' => $token,
        ];
    }

    /**
     * @param  array{nrc_number: string, nrc_front_image: UploadedFile, nrc_back_image: UploadedFile, selfie_with_id: UploadedFile}  $validated
     */
    public function submitIdentityVerification(Owner $owner, array $validated): OwnerVerification
    {
        $disk = 's3'; // MinIO/S3 only; do not use default disk
        $basePath = "owners/{$owner->id}/verification/identity";

        $nrcFrontPath = $this->storeFile($validated['nrc_front_image'], $disk, $basePath);
        $nrcBackPath = $this->storeFile($validated['nrc_back_image'], $disk, $basePath);
        $selfiePath = $this->storeFile($validated['selfie_with_id'], $disk, $basePath);

        $verification = OwnerVerification::query()->firstOrNew(['owner_id' => $owner->id]);
        $verification->nrc_number = $validated['nrc_number'];
        $verification->nrc_front_image = $nrcFrontPath;
        $verification->nrc_back_image = $nrcBackPath;
        $verification->selfie_with_id = $selfiePath;
        $verification->status = OwnerVerificationStatus::Pending;
        $verification->save();

        $owner->update(['verification_level' => OwnerVerificationLevel::IdentityVerified]);

        return $verification;
    }

    /**
     * @param  array{business_name: string, business_license_image?: UploadedFile, field_location_lat?: string|float, field_location_lng?: string|float, utility_bill_image?: UploadedFile}  $validated
     */
    public function submitBusinessVerification(Owner $owner, array $validated): OwnerVerification
    {
        $disk = 's3'; // MinIO/S3 only; do not use default disk
        $basePath = "owners/{$owner->id}/verification/business";

        $licensePath = isset($validated['business_license_image']) && $validated['business_license_image'] instanceof UploadedFile
            ? $this->storeFile($validated['business_license_image'], $disk, $basePath)
            : null;
        $utilityPath = isset($validated['utility_bill_image']) && $validated['utility_bill_image'] instanceof UploadedFile
            ? $this->storeFile($validated['utility_bill_image'], $disk, $basePath)
            : null;

        $verification = OwnerVerification::query()->firstOrNew(['owner_id' => $owner->id]);
        $verification->business_name = $validated['business_name'];
        $verification->field_location_lat = isset($validated['field_location_lat']) ? (float) $validated['field_location_lat'] : null;
        $verification->field_location_lng = isset($validated['field_location_lng']) ? (float) $validated['field_location_lng'] : null;
        $verification->status = OwnerVerificationStatus::Pending;
        if ($licensePath !== null) {
            $verification->business_license_image = $licensePath;
        }
        if ($utilityPath !== null) {
            $verification->utility_bill_image = $utilityPath;
        }
        $verification->save();

        $owner->update(['verification_level' => OwnerVerificationLevel::FullyVerified]);

        return $verification;
    }

    /**
     * @param  array{bank_name: string, account_name: string, account_number: string, qr_code?: UploadedFile|string|null}  $validated
     */
    public function setupPayoutAccount(Owner $owner, array $validated): OwnerPayoutAccount
    {
        $disk = 's3'; // MinIO/S3 only; do not use default disk
        $qrPath = null;

        if (isset($validated['qr_code'])) {
            if ($validated['qr_code'] instanceof UploadedFile) {
                $qrPath = $this->storeFile($validated['qr_code'], $disk, "owners/{$owner->id}/payout");
            } else {
                $qrPath = is_string($validated['qr_code']) ? $validated['qr_code'] : null;
            }
        }

        return OwnerPayoutAccount::query()->create([
            'owner_id' => $owner->id,
            'bank_name' => $validated['bank_name'],
            'account_name' => $validated['account_name'],
            'account_number' => $validated['account_number'],
            'qr_code' => $qrPath,
            'status' => OwnerPayoutAccountStatus::Pending,
        ]);
    }

    private function generateOtp(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function storeFile(UploadedFile $file, string $disk, string $directory): string
    {
        $path = $file->store($directory, $disk);

        if (! is_string($path)) {
            throw new InvalidArgumentException('File storage failed.');
        }

        return $path;
    }
}
