<?php

namespace App\Http\Controllers;

use App\Models\WorkDay;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HistoryController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $month = $request->input('month', Carbon::today()->month);
        $year = $request->input('year', Carbon::today()->year);
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $query = WorkDay::forUser($user->id)->with('pointRecords');

        if ($startDate && $endDate) {
            $query->whereBetween('date', [$startDate, $endDate]);
        } else {
            $query->forMonth((int) $year, (int) $month);
        }

        $workDays = $query->orderBy('date', 'desc')->paginate(15)->withQueryString();

        $totalWorkedMinutes = 0;
        $totalExpectedMinutes = 0;
        $totalBalanceMinutes = 0;

        foreach ($workDays as $wd) {
            if ($wd->worked_minutes !== null) {
                $totalWorkedMinutes += $wd->worked_minutes;
            }
            $totalExpectedMinutes += $wd->expected_minutes;
            if ($wd->balance_minutes !== null) {
                $totalBalanceMinutes += $wd->balance_minutes;
            }
        }

        $formatMin = function(int $m, bool $withSign = false) {
            $abs = abs($m);
            $h = intdiv($abs, 60);
            $min = $abs % 60;
            $sign = $withSign ? ($m >= 0 ? '+' : '-') : '';
            return sprintf('%s%02d:%02d', $sign, $h, $min);
        };

        return view('history.index', [
            'workDays' => $workDays,
            'selectedMonth' => (int) $month,
            'selectedYear' => (int) $year,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalWorkedFormatted' => $formatMin($totalWorkedMinutes),
            'totalExpectedFormatted' => $formatMin($totalExpectedMinutes),
            'totalBalanceFormatted' => $formatMin($totalBalanceMinutes, true),
        ]);
    }
}
