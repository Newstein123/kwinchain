<?php

use App\Enums\OwnerStatus;
use App\Enums\OwnerVerificationLevel;
use App\Mail\OwnerOtpMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

it('registers a new owner and returns success with owner data', function () {
    Mail::fake();

    $response = $this->postJson('/api/v1/owners/register', [
        'name' => 'Ko Aung',
        'email' => 'owner@example.com',
        'password' => 'password123',
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'Owner Registered Successfully',
            'data' => [
                'owner_id' => 1,
                'verification_level' => OwnerVerificationLevel::Basic->value,
                'status' => OwnerStatus::Pending->value,
            ],
        ])
        ->assertJsonPath('data.owner.name', 'Ko Aung')
        ->assertJsonPath('data.owner.email', 'owner@example.com');

    Mail::assertSent(OwnerOtpMail::class);
});

it('verify otp returns token and owner when code is valid', function () {
    Mail::fake();

    $this->postJson('/api/v1/owners/register', [
        'name' => 'Ko Aung',
        'email' => 'owner@example.com',
        'password' => 'password123',
    ]);

    $mail = Mail::sent(OwnerOtpMail::class)->first();
    $code = $mail->code;

    $response = $this->postJson('/api/v1/owners/verify-otp', [
        'email' => 'owner@example.com',
        'otp_code' => $code,
    ]);

    $response->assertOk()
        ->assertJson([
            'success' => true,
            'message' => 'OTP verified successfully',
        ])
        ->assertJsonStructure([
            'data' => [
                'owner' => ['id', 'name', 'email', 'verification_level', 'status'],
                'token',
            ],
        ]);
});
