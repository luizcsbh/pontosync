<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use Illuminate\Database\Seeder;

class WorkScheduleSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        foreach ($users as $user) {
            $schedule = WorkSchedule::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'name' => 'Jornada Padrão 44h',
                ],
                [
                    'is_active' => true,
                ]
            );

            $daysConfig = [
                // 0 = Domingo
                0 => ['is_workday' => false, 'entry' => null, 'l_start' => null, 'l_end' => null, 'exit' => null, 'expected' => 0],
                // 1 = Segunda
                1 => ['is_workday' => true, 'entry' => 480, 'l_start' => 720, 'l_end' => 780, 'exit' => 1080, 'expected' => 540], // 08:00-12:00, 13:00-18:00 (9h)
                // 2 = Terça
                2 => ['is_workday' => true, 'entry' => 480, 'l_start' => 720, 'l_end' => 780, 'exit' => 1080, 'expected' => 540],
                // 3 = Quarta
                3 => ['is_workday' => true, 'entry' => 480, 'l_start' => 720, 'l_end' => 780, 'exit' => 1080, 'expected' => 540],
                // 4 = Quinta
                4 => ['is_workday' => true, 'entry' => 480, 'l_start' => 720, 'l_end' => 780, 'exit' => 1080, 'expected' => 540],
                // 5 = Sexta
                5 => ['is_workday' => true, 'entry' => 480, 'l_start' => 720, 'l_end' => 780, 'exit' => 1020, 'expected' => 480], // 08:00-12:00, 13:00-17:00 (8h)
                // 6 = Sábado
                6 => ['is_workday' => false, 'entry' => null, 'l_start' => null, 'l_end' => null, 'exit' => null, 'expected' => 0],
            ];

            foreach ($daysConfig as $dayOfWeek => $config) {
                WorkScheduleDay::updateOrCreate(
                    [
                        'work_schedule_id' => $schedule->id,
                        'day_of_week' => $dayOfWeek,
                    ],
                    [
                        'is_workday' => $config['is_workday'],
                        'entry_time_minutes' => $config['entry'],
                        'lunch_start_minutes' => $config['l_start'],
                        'lunch_end_minutes' => $config['l_end'],
                        'exit_time_minutes' => $config['exit'],
                        'expected_minutes' => $config['expected'],
                    ]
                );
            }
        }
    }
}
