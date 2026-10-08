<?php

namespace Database\Seeders;

use App\Models\HourBankTransaction;
use App\Models\PointRecord;
use App\Models\User;
use App\Models\WorkDay;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class WorkDaySeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('email', 'luiz@pontosync.test')->first();

        if (!$user) {
            return;
        }

        $schedule = $user->activeWorkSchedule();
        $runningBalance = 0;

        // Gerar 10 dias úteis anteriores até a data atual (ou até ontem)
        $currentDate = Carbon::create(2026, 10, 7); // Data base do sistema
        $dates = [];
        $tempDate = $currentDate->copy()->subDays(14);

        while (count($dates) < 10) {
            if ($tempDate->isWeekday()) {
                $dates[] = $tempDate->copy();
            }
            $tempDate->addDay();
        }

        foreach ($dates as $index => $date) {
            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek;
            $scheduleDay = $schedule?->days()->where('day_of_week', $dayOfWeek)->first();
            $expectedMinutes = $scheduleDay?->expected_minutes ?? ($dayOfWeek === 5 ? 480 : 540);

            // Variação realista para cada dia
            $variations = [
                ['08:02', '12:01', '13:03', '18:07'], // +3 min
                ['07:58', '12:00', '13:00', '18:02'], // +4 min
                ['08:05', '12:05', '13:02', '18:00'], // -2 min
                ['08:00', '12:00', '13:00', '18:15'], // +15 min
                ['08:01', '12:02', '13:01', ($dayOfWeek === 5 ? '17:05' : '18:05')], // +5 min
                ['07:55', '12:00', '13:00', ($dayOfWeek === 5 ? '16:55' : '17:55')], // 0 min
                ['08:10', '12:00', '13:05', ($dayOfWeek === 5 ? '17:10' : '18:10')], // -5 min
                ['08:03', '12:02', '13:01', ($dayOfWeek === 5 ? '17:15' : '18:18')], // +16 min
                ['08:00', '12:00', '13:00', ($dayOfWeek === 5 ? '17:00' : '18:00')], // Exato
                ['08:03', '12:02', '13:01', ($dayOfWeek === 5 ? '17:08' : '18:05')], // +3 min
            ];

            $times = $variations[$index % count($variations)];

            $entryDt = Carbon::parse("{$dateStr} {$times[0]}");
            $lStartDt = Carbon::parse("{$dateStr} {$times[1]}");
            $lEndDt = Carbon::parse("{$dateStr} {$times[2]}");
            $exitDt = Carbon::parse("{$dateStr} {$times[3]}");

            $morningMinutes = $lStartDt->diffInMinutes($entryDt);
            $afternoonMinutes = $exitDt->diffInMinutes($lEndDt);
            $workedMinutes = $morningMinutes + $afternoonMinutes;
            $balanceMinutes = $workedMinutes - $expectedMinutes;

            $workDay = WorkDay::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $dateStr,
                ],
                [
                    'work_schedule_id' => $schedule?->id,
                    'status' => 'complete',
                    'expected_minutes' => $expectedMinutes,
                    'worked_minutes' => $workedMinutes,
                    'balance_minutes' => $balanceMinutes,
                ]
            );

            // Marcações
            $records = [
                ['type' => PointRecord::TYPE_ENTRY, 'time' => $entryDt, 'source' => ($index % 2 === 0 ? 'ocr' : 'manual'), 'conf' => 0.95],
                ['type' => PointRecord::TYPE_LUNCH_START, 'time' => $lStartDt, 'source' => 'manual', 'conf' => null],
                ['type' => PointRecord::TYPE_LUNCH_END, 'time' => $lEndDt, 'source' => 'manual', 'conf' => null],
                ['type' => PointRecord::TYPE_EXIT, 'time' => $exitDt, 'source' => ($index % 3 === 0 ? 'ocr' : 'manual'), 'conf' => 0.92],
            ];

            foreach ($records as $r) {
                PointRecord::updateOrCreate(
                    [
                        'work_day_id' => $workDay->id,
                        'type' => $r['type'],
                    ],
                    [
                        'user_id' => $user->id,
                        'recorded_at' => $r['time'],
                        'source' => $r['source'],
                        'ocr_confidence' => $r['conf'],
                        'ocr_raw_text' => $r['conf'] ? $r['time']->format('d/m/Y H:i') : null,
                        'confirmed_at' => $r['time'],
                    ]
                );
            }

            // Banco de horas
            $runningBalance += $balanceMinutes;

            HourBankTransaction::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'work_day_id' => $workDay->id,
                ],
                [
                    'date' => $dateStr,
                    'description' => "Jornada {$date->format('d/m/Y')}",
                    'minutes' => $balanceMinutes,
                    'balance_minutes' => $runningBalance,
                    'type' => 'daily',
                ]
            );
        }
    }
}
