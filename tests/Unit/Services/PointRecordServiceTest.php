<?php

use App\Models\PointRecord;
use App\Models\User;
use App\Models\WorkDay;
use App\Services\HourBankService;
use App\Services\PointRecordService;
use App\Services\WorkDayService;

test('it calculates 09:00 worked hours for standard 4 markings (08:00->12:00->13:00->18:00)', function () {
    $user = User::factory()->create();
    $workDayService = new WorkDayService();
    $hourBankService = new HourBankService();
    $service = new PointRecordService($workDayService, $hourBankService);

    $date = '07/10/2026';

    $service->register($user, ['date' => $date, 'time' => '08:00', 'type' => PointRecord::TYPE_ENTRY, 'source' => 'manual']);
    $service->register($user, ['date' => $date, 'time' => '12:00', 'type' => PointRecord::TYPE_LUNCH_START, 'source' => 'manual']);
    $service->register($user, ['date' => $date, 'time' => '13:00', 'type' => PointRecord::TYPE_LUNCH_END, 'source' => 'manual']);
    $service->register($user, ['date' => $date, 'time' => '18:00', 'type' => PointRecord::TYPE_EXIT, 'source' => 'manual']);

    $workDay = WorkDay::where('user_id', $user->id)->first();

    // 08:00-12:00 = 4h (240m) + 13:00-18:00 = 5h (300m) -> 540m = 9h
    expect($workDay->worked_minutes)->toBe(540);
    expect($workDay->formatted_worked_minutes)->toBe('09:00');
    expect($workDay->status)->toBe('complete');
});

test('it prevents duplicate type for the same day', function () {
    $user = User::factory()->create();
    $workDayService = new WorkDayService();
    $hourBankService = new HourBankService();
    $service = new PointRecordService($workDayService, $hourBankService);

    $date = '07/10/2026';

    $service->register($user, ['date' => $date, 'time' => '08:00', 'type' => PointRecord::TYPE_ENTRY, 'source' => 'manual']);

    expect(fn () => $service->register($user, ['date' => $date, 'time' => '08:05', 'type' => PointRecord::TYPE_ENTRY, 'source' => 'manual']))
        ->toThrow(InvalidArgumentException::class);
});

test('it prevents invalid chronological sequence', function () {
    $user = User::factory()->create();
    $workDayService = new WorkDayService();
    $hourBankService = new HourBankService();
    $service = new PointRecordService($workDayService, $hourBankService);

    $date = '07/10/2026';

    $service->register($user, ['date' => $date, 'time' => '08:00', 'type' => PointRecord::TYPE_ENTRY, 'source' => 'manual']);

    // Lunch start before entry
    expect(fn () => $service->register($user, ['date' => $date, 'time' => '07:30', 'type' => PointRecord::TYPE_LUNCH_START, 'source' => 'manual']))
        ->toThrow(InvalidArgumentException::class);
});
