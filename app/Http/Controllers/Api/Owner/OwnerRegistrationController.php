<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Owner\OwnerPayoutAccountRequest;
use App\Http\Requests\Api\Owner\OwnerRegisterRequest;
use App\Http\Requests\Api\Owner\OwnerVerifyBusinessRequest;
use App\Http\Requests\Api\Owner\OwnerVerifyIdentityRequest;
use App\Http\Requests\Api\Owner\OwnerVerifyOtpRequest;
use App\Http\Resources\Api\OwnerResource;
use App\Services\OwnerRegistrationService;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;

class OwnerRegistrationController extends Controller
{
    public function __construct(
        private OwnerRegistrationService $registrationService
    ) {}

    public function register(OwnerRegisterRequest $request): JsonResponse
    {
        $owner = $this->registrationService->register($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Owner Registered Successfully',
            'data' => [
                'owner' => new OwnerResource($owner),
                'owner_id' => $owner->id,
                'verification_level' => $owner->verification_level->value,
                'status' => $owner->status->value,
            ],
        ]);
    }

    public function verifyOtp(OwnerVerifyOtpRequest $request): JsonResponse
    {
        try {
            $result = $this->registrationService->verifyOtp(
                $request->validated('email'),
                $request->validated('otp_code')
            );
        } catch (InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'owner' => new OwnerResource($result['owner']),
                'token' => $result['token'],
            ],
        ]);
    }

    public function verifyIdentity(OwnerVerifyIdentityRequest $request): JsonResponse
    {
        $owner = $request->user('owner');
        $verification = $this->registrationService->submitIdentityVerification($owner, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Identity Verified Successfully',
            'data' => [
                'status' => $verification->status->value,
            ],
        ]);
    }

    public function verifyBusiness(OwnerVerifyBusinessRequest $request): JsonResponse
    {
        $owner = $request->user('owner');
        $verification = $this->registrationService->submitBusinessVerification($owner, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Business Verified Successfully',
            'data' => [
                'status' => $verification->status->value,
            ],
        ]);
    }

    public function payoutAccount(OwnerPayoutAccountRequest $request): JsonResponse
    {
        $owner = $request->user('owner');
        $payoutAccount = $this->registrationService->setupPayoutAccount($owner, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Payout account set up successfully',
            'data' => [
                'status' => $payoutAccount->status->value,
            ],
        ]);
    }
}
