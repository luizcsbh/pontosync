<?php

namespace App\Http\Controllers;

use App\Models\HourBankTransaction;
use App\Services\HourBankService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HourBankController extends Controller
{
    public function __construct(
        private readonly HourBankService $hourBankService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->input('year', Carbon::today()->year);
        $month = (int) $request->input('month', Carbon::today()->month);

        $currentBalance = $this->hourBankService->getBalance($user);
        $currentBalanceFormatted = $this->hourBankService->getFormattedBalance($user);

        $transactions = HourBankTransaction::forUser($user->id)
            ->forMonth($year, $month)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $monthSummary = $this->hourBankService->getMonthSummary($user, $year, $month);

        return view('hour-bank.index', [
            'transactions' => $transactions,
            'currentBalance' => $currentBalance,
            'currentBalanceFormatted' => $currentBalanceFormatted,
            'selectedYear' => $year,
            'selectedMonth' => $month,
            'monthSummary' => $monthSummary,
        ]);
    }
}
