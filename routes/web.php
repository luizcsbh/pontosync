<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HistoryController;
use App\Http\Controllers\HourBankController;
use App\Http\Controllers\OcrController;
use App\Http\Controllers\PointRecordController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\WorkScheduleController;
use Illuminate\Support\Facades\Route;

// Rotas de Autenticação
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Redirecionamento da raiz
Route::get('/', function () {
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Rotas Autenticadas
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Registro de Ponto (Manual e OCR)
    Route::resource('point-records', PointRecordController::class)->except(['show']);

    // OCR API
    Route::post('/ocr/upload', [OcrController::class, 'upload'])->name('ocr.upload');

    // Jornada de Trabalho
    Route::resource('work-schedules', WorkScheduleController::class)->except(['show']);

    // Histórico de Marcações
    Route::get('/history', [HistoryController::class, 'index'])->name('history.index');

    // Banco de Horas
    Route::get('/hour-bank', [HourBankController::class, 'index'])->name('hour-bank.index');

    // Relatórios
    Route::get('/reports/monthly', [ReportController::class, 'monthly'])->name('reports.monthly');
    Route::get('/reports/weekly', [ReportController::class, 'weekly'])->name('reports.weekly');
    Route::get('/reports/export/csv', [ReportController::class, 'exportCsv'])->name('reports.export.csv');
    Route::get('/reports/export/pdf', [ReportController::class, 'exportPdf'])->name('reports.export.pdf');
});
