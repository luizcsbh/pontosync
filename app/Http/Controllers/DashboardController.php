<?php

namespace App\Http\Controllers;

use App\Models\PointRecord;
use App\Services\HourBankService;
use App\Services\PointRecordService;
use App\Services\WorkDayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly WorkDayService $workDayService,
        private readonly PointRecordService $pointRecordService,
        private readonly HourBankService $hourBankService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $today = Carbon::today()->format('Y-m-d');

        $workDay = $this->workDayService->getOrCreateWorkDay($user, $today);
        $workDay->load('pointRecords');

        $records = $workDay->pointRecords->keyBy('type');

        $nextExpectedType = $this->pointRecordService->getNextExpectedType($workDay);
        $hourBankBalance = $this->hourBankService->getBalance($user);
        $hourBankFormatted = $this->hourBankService->getFormattedBalance($user);

        return view('dashboard.index', [
            'user' => $user,
            'workDay' => $workDay,
            'records' => $records,
            'nextExpectedType' => $nextExpectedType,
            'hourBankBalance' => $hourBankBalance,
            'hourBankFormatted' => $hourBankFormatted,
            'todayDate' => Carbon::today(),
        ]);
    }
}
