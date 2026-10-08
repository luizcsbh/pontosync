<?php

use App\Models\User;
use App\Models\WorkSchedule;

test('authenticated user can view work schedules list and create page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('work-schedules.index'));
    $response->assertOk();
    $response->assertSee('Jornadas de Trabalho');

    $responseCreate = $this->actingAs($user)->get(route('work-schedules.create'));
    $responseCreate->assertOk();
    $responseCreate->assertSee('Cadastrar Nova Jornada');
    $responseCreate->assertSee('Ferramenta de Repetição Rápida');
});

test('user can store a new work schedule with replicated days', function () {
    $user = User::factory()->create();

    $payload = [
        'name' => 'Jornada 44h Comercial',
        'is_active' => 1,
        'days' => [
            0 => ['is_workday' => 0, 'entry' => null, 'lunch_start' => null, 'lunch_end' => null, 'exit' => null],
            1 => ['is_workday' => 1, 'entry' => '08:00', 'lunch_start' => '12:00', 'lunch_end' => '13:00', 'exit' => '18:00'],
            2 => ['is_workday' => 1, 'entry' => '08:00', 'lunch_start' => '12:00', 'lunch_end' => '13:00', 'exit' => '18:00'],
            3 => ['is_workday' => 1, 'entry' => '08:00', 'lunch_start' => '12:00', 'lunch_end' => '13:00', 'exit' => '18:00'],
            4 => ['is_workday' => 1, 'entry' => '08:00', 'lunch_start' => '12:00', 'lunch_end' => '13:00', 'exit' => '18:00'],
            5 => ['is_workday' => 1, 'entry' => '08:00', 'lunch_start' => '12:00', 'lunch_end' => '13:00', 'exit' => '17:00'],
            6 => ['is_workday' => 0, 'entry' => null, 'lunch_start' => null, 'lunch_end' => null, 'exit' => null],
        ],
    ];

    $response = $this->actingAs($user)->post(route('work-schedules.store'), $payload);

    $response->assertRedirect(route('work-schedules.index'));
    $this->assertDatabaseHas('work_schedules', [
        'user_id' => $user->id,
        'name' => 'Jornada 44h Comercial',
        'is_active' => true,
    ]);

    $schedule = WorkSchedule::where('user_id', $user->id)->first();
    expect($schedule->days)->toHaveCount(7);
});
