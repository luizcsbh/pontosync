<?php

namespace App\Http\Controllers;

use App\Models\WorkDay;
use App\Services\HourBankService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly HourBankService $hourBankService,
    ) {}

    public function monthly(Request $request): View
    {
        $user = $request->user();
        $year = (int) $request->input('year', Carbon::today()->year);
        $month = (int) $request->input('month', Carbon::today()->month);

        $workDays = WorkDay::forUser($user->id)
            ->forMonth($year, $month)
            ->with('pointRecords')
            ->orderBy('date')
            ->get();

        $workedDaysCount = $workDays->where('status', 'complete')->count();
        $totalWorkedMinutes = $workDays->sum('worked_minutes') ?? 0;
        $totalExpectedMinutes = $workDays->sum('expected_minutes') ?? 0;
        $monthBalance = $totalWorkedMinutes - $totalExpectedMinutes;

        $accumulatedBalance = $this->hourBankService->getBalance($user);

        return view('reports.monthly', [
            'workDays' => $workDays,
            'year' => $year,
            'month' => $month,
            'monthName' => Carbon::create($year, $month, 1)->locale('pt_BR')->translatedFormat('F/Y'),
            'workedDaysCount' => $workedDaysCount,
            'totalWorkedFormatted' => $this->hourBankService->minutesToFormatted($totalWorkedMinutes),
            'totalExpectedFormatted' => $this->hourBankService->minutesToFormatted($totalExpectedMinutes),
            'monthBalanceFormatted' => $this->hourBankService->minutesToFormatted($monthBalance),
            'accumulatedBalanceFormatted' => $this->hourBankService->minutesToFormatted($accumulatedBalance),
        ]);
    }

    public function weekly(Request $request): View
    {
        $user = $request->user();
        $startDate = $request->input('start_date', Carbon::today()->startOfWeek()->format('Y-m-d'));

        $start = Carbon::parse($startDate)->startOfWeek();
        $end = $start->copy()->endOfWeek();

        $workDays = WorkDay::forUser($user->id)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->with('pointRecords')
            ->orderBy('date')
            ->get();

        $totalWorkedMinutes = $workDays->sum('worked_minutes') ?? 0;
        $totalExpectedMinutes = $workDays->sum('expected_minutes') ?? 0;
        $weekBalance = $totalWorkedMinutes - $totalExpectedMinutes;

        return view('reports.weekly', [
            'workDays' => $workDays,
            'startDate' => $start->format('Y-m-d'),
            'weekLabel' => $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y'),
            'totalWorkedFormatted' => $this->hourBankService->minutesToFormatted($totalWorkedMinutes),
            'totalExpectedFormatted' => $this->hourBankService->minutesToFormatted($totalExpectedMinutes),
            'weekBalanceFormatted' => $this->hourBankService->minutesToFormatted($weekBalance),
        ]);
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $user = $request->user();
        $year = (int) $request->input('year', Carbon::today()->year);
        $month = (int) $request->input('month', Carbon::today()->month);

        $workDays = WorkDay::forUser($user->id)
            ->forMonth($year, $month)
            ->with('pointRecords')
            ->orderBy('date')
            ->get();

        $filename = "espelho_ponto_{$year}_{$month}_{$user->id}.csv";

        return response()->streamDownload(function () use ($workDays, $user) {
            $handle = fopen('php://output', 'w');
            // BOM UTF-8 para Excel abrir com acentuação correta
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Relatório de Espelho de Ponto - PontoSync', 'Colaborador: ' . $user->name], ';');
            fputcsv($handle, ['Data', 'Entrada', 'Saída Almoço', 'Retorno Almoço', 'Saída', 'Previsto', 'Trabalhado', 'Saldo', 'Status'], ';');

            foreach ($workDays as $wd) {
                $records = $wd->pointRecords->keyBy('type');
                fputcsv($handle, [
                    $wd->date->format('d/m/Y'),
                    $records->get('entry')?->formatted_time ?? '--:--',
                    $records->get('lunch_start')?->formatted_time ?? '--:--',
                    $records->get('lunch_end')?->formatted_time ?? '--:--',
                    $records->get('exit')?->formatted_time ?? '--:--',
                    $wd->formatted_expected_minutes,
                    $wd->formatted_worked_minutes,
                    $wd->formatted_balance,
                    $wd->status_label,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $user = $request->user();
        $year = (int) $request->input('year', Carbon::today()->year);
        $month = (int) $request->input('month', Carbon::today()->month);

        $workDays = WorkDay::forUser($user->id)
            ->forMonth($year, $month)
            ->with('pointRecords')
            ->orderBy('date')
            ->get();

        $workedDaysCount = $workDays->where('status', 'complete')->count();
        $totalWorkedMinutes = $workDays->sum('worked_minutes') ?? 0;
        $totalExpectedMinutes = $workDays->sum('expected_minutes') ?? 0;
        $monthBalance = $totalWorkedMinutes - $totalExpectedMinutes;
        $accumulatedBalance = $this->hourBankService->getBalance($user);

        $data = [
            'user' => $user,
            'workDays' => $workDays,
            'year' => $year,
            'month' => $month,
            'monthName' => Carbon::create($year, $month, 1)->locale('pt_BR')->translatedFormat('F \d\e Y'),
            'workedDaysCount' => $workedDaysCount,
            'totalWorkedFormatted' => $this->hourBankService->minutesToFormatted($totalWorkedMinutes),
            'totalExpectedFormatted' => $this->hourBankService->minutesToFormatted($totalExpectedMinutes),
            'monthBalanceFormatted' => $this->hourBankService->minutesToFormatted($monthBalance),
            'accumulatedBalanceFormatted' => $this->hourBankService->minutesToFormatted($accumulatedBalance),
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ];

        $pdf = Pdf::loadView('reports.pdf', $data)->setPaper('a4', 'portrait');

        return $pdf->download("espelho_ponto_{$year}_{$month}.pdf");
    }
}
