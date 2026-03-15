<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\TrustedUserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OwnerTrustedUserController extends Controller
{
    public function __construct(
        private TrustedUserService $trustedUserService,
    ) {}

    /**
     * POST /api/v1/owner/users/{id}/mark-trusted
     *
     * Mark a user as trusted for the authenticated owner.
     */
    public function markTrusted(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        /** @var \App\Models\Owner $owner */
        $owner = $request->user();

        $this->trustedUserService->markTrusted($owner, $user);

        return response()->json([
            'message' => 'User marked as trusted.',
            'data'    => ['trusted' => true],
        ]);
    }

    /**
     * POST /api/v1/owner/users/{id}/remove-trusted
     *
     * Remove trusted status for a user under the authenticated owner.
     */
    public function removeTrusted(Request $request, int $id): JsonResponse
    {
        $user = User::find($id);

        if (! $user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        /** @var \App\Models\Owner $owner */
        $owner = $request->user();

        $this->trustedUserService->removeTrusted($owner, $user);

        return response()->json([
            'message' => 'User trust removed.',
            'data'    => ['trusted' => false],
        ]);
    }
}
