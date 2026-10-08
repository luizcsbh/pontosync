<?php

namespace App\Http\Controllers;

use App\Http\Requests\WorkSchedule\StoreWorkScheduleRequest;
use App\Models\WorkSchedule;
use App\Models\WorkScheduleDay;
use App\Services\HourBankService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkScheduleController extends Controller
{
    public function __construct(
        private readonly HourBankService $hourBankService,
    ) {}

    public function index(Request $request): View
    {
        $schedules = $request->user()->workSchedules()->with('days')->get();

        return view('work-schedules.index', [
            'schedules' => $schedules,
        ]);
    }

    public function create(): View
    {
        $daysOfWeek = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return view('work-schedules.create', [
            'daysOfWeek' => $daysOfWeek,
        ]);
    }

    public function store(StoreWorkScheduleRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        if (!empty($validated['is_active'])) {
            $user->workSchedules()->update(['is_active' => false]);
        }

        $schedule = WorkSchedule::create([
            'user_id' => $user->id,
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? true,
        ]);

        foreach ($validated['days'] as $dayOfWeek => $data) {
            $isWorkday = (bool) ($data['is_workday'] ?? false);

            $entryMinutes = (!empty($data['entry']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['entry']) : null;
            $lunchStartMinutes = (!empty($data['lunch_start']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['lunch_start']) : null;
            $lunchEndMinutes = (!empty($data['lunch_end']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['lunch_end']) : null;
            $exitMinutes = (!empty($data['exit']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['exit']) : null;

            $expectedMinutes = null;
            if ($isWorkday && $entryMinutes !== null && $exitMinutes !== null) {
                if ($lunchStartMinutes !== null && $lunchEndMinutes !== null) {
                    $morning = $lunchStartMinutes - $entryMinutes;
                    $afternoon = $exitMinutes - $lunchEndMinutes;
                    $expectedMinutes = $morning + $afternoon;
                } else {
                    $expectedMinutes = $exitMinutes - $entryMinutes;
                }
            }

            WorkScheduleDay::create([
                'work_schedule_id' => $schedule->id,
                'day_of_week' => (int) $dayOfWeek,
                'is_workday' => $isWorkday,
                'entry_time_minutes' => $entryMinutes,
                'lunch_start_minutes' => $lunchStartMinutes,
                'lunch_end_minutes' => $lunchEndMinutes,
                'exit_time_minutes' => $exitMinutes,
                'expected_minutes' => $expectedMinutes,
            ]);
        }

        return redirect()->route('work-schedules.index')->with('success', 'Jornada cadastrada com sucesso!');
    }

    public function edit(Request $request, WorkSchedule $workSchedule): View
    {
        abort_if($workSchedule->user_id !== $request->user()->id, 403);
        $workSchedule->load('days');

        $daysOfWeek = [
            0 => 'Domingo',
            1 => 'Segunda-feira',
            2 => 'Terça-feira',
            3 => 'Quarta-feira',
            4 => 'Quinta-feira',
            5 => 'Sexta-feira',
            6 => 'Sábado',
        ];

        return view('work-schedules.edit', [
            'schedule' => $workSchedule,
            'daysOfWeek' => $daysOfWeek,
            'scheduleDays' => $workSchedule->days->keyBy('day_of_week'),
        ]);
    }

    public function update(StoreWorkScheduleRequest $request, WorkSchedule $workSchedule): RedirectResponse
    {
        abort_if($workSchedule->user_id !== $request->user()->id, 403);

        $validated = $request->validated();

        if (!empty($validated['is_active'])) {
            $request->user()->workSchedules()->where('id', '!=', $workSchedule->id)->update(['is_active' => false]);
        }

        $workSchedule->update([
            'name' => $validated['name'],
            'is_active' => $validated['is_active'] ?? $workSchedule->is_active,
        ]);

        foreach ($validated['days'] as $dayOfWeek => $data) {
            $isWorkday = (bool) ($data['is_workday'] ?? false);

            $entryMinutes = (!empty($data['entry']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['entry']) : null;
            $lunchStartMinutes = (!empty($data['lunch_start']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['lunch_start']) : null;
            $lunchEndMinutes = (!empty($data['lunch_end']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['lunch_end']) : null;
            $exitMinutes = (!empty($data['exit']) && $isWorkday) ? $this->hourBankService->timeToMinutes($data['exit']) : null;

            $expectedMinutes = null;
            if ($isWorkday && $entryMinutes !== null && $exitMinutes !== null) {
                if ($lunchStartMinutes !== null && $lunchEndMinutes !== null) {
                    $morning = $lunchStartMinutes - $entryMinutes;
                    $afternoon = $exitMinutes - $lunchEndMinutes;
                    $expectedMinutes = $morning + $afternoon;
                } else {
                    $expectedMinutes = $exitMinutes - $entryMinutes;
                }
            }

            WorkScheduleDay::updateOrCreate(
                [
                    'work_schedule_id' => $workSchedule->id,
                    'day_of_week' => (int) $dayOfWeek,
                ],
                [
                    'is_workday' => $isWorkday,
                    'entry_time_minutes' => $entryMinutes,
                    'lunch_start_minutes' => $lunchStartMinutes,
                    'lunch_end_minutes' => $lunchEndMinutes,
                    'exit_time_minutes' => $exitMinutes,
                    'expected_minutes' => $expectedMinutes,
                ]
            );
        }

        return redirect()->route('work-schedules.index')->with('success', 'Jornada atualizada com sucesso!');
    }
}
