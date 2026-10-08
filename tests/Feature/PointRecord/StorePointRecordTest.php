<?php

use App\Models\PointRecord;
use App\Models\User;
use App\Models\WorkDay;
use Illuminate\Http\UploadedFile;

test('authenticated user can view dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('dashboard'));

    $response->assertOk();
    $response->assertSee('Jornada de Hoje');
});

test('user can register manual point record', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('point-records.store'), [
        'date' => '07/10/2026',
        'time' => '08:03',
        'type' => 'entry',
        'source' => 'manual',
    ]);

    $response->assertRedirect(route('dashboard'));
    $this->assertDatabaseHas('point_records', [
        'user_id' => $user->id,
        'type' => 'entry',
        'source' => 'manual',
    ]);
});

test('ocr upload returns json structured with confidence', function () {
    $user = User::factory()->create();

    $file = UploadedFile::fake()->image('ponto.jpg');

    $response = $this->actingAs($user)->postJson(route('ocr.upload'), [
        'photo' => $file,
    ]);

    $response->assertOk();
    $response->assertJsonStructure([
        'success',
        'data' => [
            'date',
            'time',
            'confidence',
            'confidence_percent',
            'is_high_confidence',
            'raw_text',
        ],
    ]);
});
