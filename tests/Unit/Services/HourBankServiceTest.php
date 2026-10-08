<?php

use App\Services\HourBankService;

test('it formats positive minutes to string correctly', function () {
    $service = new HourBankService();

    expect($service->minutesToFormatted(510))->toBe('+08:30');
    expect($service->minutesToFormatted(198))->toBe('+03:18');
    expect($service->minutesToFormatted(60))->toBe('+01:00');
    expect($service->minutesToFormatted(0))->toBe('+00:00');
});

test('it formats negative minutes to string correctly', function () {
    $service = new HourBankService();

    expect($service->minutesToFormatted(-30))->toBe('-00:30');
    expect($service->minutesToFormatted(-75))->toBe('-01:15');
    expect($service->minutesToFormatted(-480))->toBe('-08:00');
});

test('it converts time string to minutes correctly', function () {
    $service = new HourBankService();

    expect($service->timeToMinutes('08:30'))->toBe(510);
    expect($service->timeToMinutes('12:00'))->toBe(720);
    expect($service->timeToMinutes('00:00'))->toBe(0);
    expect($service->timeToMinutes('23:59'))->toBe(1439);
});
