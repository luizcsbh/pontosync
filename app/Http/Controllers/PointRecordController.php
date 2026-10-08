<?php

namespace App\Http\Controllers;

use App\Http\Requests\PointRecord\StorePointRecordRequest;
use App\Models\PointImage;
use App\Models\PointRecord;
use App\Services\HourBankService;
use App\Services\ImageService;
use App\Services\PointRecordService;
use App\Services\WorkDayService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PointRecordController extends Controller
{
    public function __construct(
        private readonly PointRecordService $pointRecordService,
        private readonly WorkDayService $workDayService,
        private readonly HourBankService $hourBankService,
        private readonly ImageService $imageService,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        $date = $request->input('date', Carbon::today()->format('Y-m-d'));

        $workDay = $this->workDayService->getOrCreateWorkDay($user, $date);
        $workDay->load(['pointRecords.image']);

        return view('point-records.index', [
            'workDay' => $workDay,
            'records' => $workDay->pointRecords,
            'selectedDate' => Carbon::parse($date),
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        $defaultType = $request->input('type');
        $mode = $request->input('mode', 'manual'); // 'manual' ou 'ocr'

        $tz = config('app.timezone', 'America/Sao_Paulo');

        $now = Carbon::now($tz);
        $today = $now->format('Y-m-d');
        $workDay = $this->workDayService->getOrCreateWorkDay($user, $today);
        $workDay->load('pointRecords');

        if (!$defaultType) {
            $defaultType = $this->pointRecordService->getNextExpectedType($workDay) ?? PointRecord::TYPE_ENTRY;
        }

        $yesterday = Carbon::yesterday($tz);

        return view('point-records.create', [
            'defaultType'    => $defaultType,
            'mode'           => $mode,
            'todayDate'      => $now->format('d/m/Y'),
            'todayYmd'       => $today,
            'todayYear'      => (int) $now->format('Y'),
            'todayMonth'     => (int) $now->format('n'),
            'todayDay'       => (int) $now->format('j'),
            'yesterdayDate'  => $yesterday->format('d/m/Y'),
            'yesterdayYear'  => (int) $yesterday->format('Y'),
            'yesterdayMonth' => (int) $yesterday->format('n'),
            'yesterdayDay'   => (int) $yesterday->format('j'),
            'currentTime'    => $now->format('H:i'),
            'typeOptions'    => PointRecord::typeOptions(),
        ]);
    }

    public function store(StorePointRecordRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();

        try {
            $record = $this->pointRecordService->register($user, $validated);

            // Se enviou foto
            if ($request->hasFile('photo')) {
                $photo = $request->file('photo');
                $dateYmd = Carbon::createFromFormat('d/m/Y', $validated['date'])->format('Y-m-d');
                $savedPath = $this->imageService->store($photo, $dateYmd, $record->type);

                PointImage::create([
                    'point_record_id' => $record->id,
                    'user_id' => $user->id,
                    'path' => $savedPath,
                    'disk' => 'local',
                    'original_name' => $photo->getClientOriginalName(),
                    'mime_type' => $photo->getClientMimeType(),
                    'size' => $photo->getSize(),
                    'ocr_processed' => $validated['source'] === PointRecord::SOURCE_OCR,
                    'ocr_result' => [
                        'confidence' => $validated['ocr_confidence'] ?? null,
                        'raw_text' => $validated['ocr_raw_text'] ?? null,
                    ],
                ]);
            }

            return redirect()->route('dashboard')->with('success', 'Ponto registrado com sucesso!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function edit(Request $request, PointRecord $pointRecord): View
    {
        abort_if($pointRecord->user_id !== $request->user()->id, 403);

        return view('point-records.edit', [
            'record' => $pointRecord,
            'workDay' => $pointRecord->workDay,
            'typeOptions' => PointRecord::typeOptions(),
        ]);
    }

    public function update(Request $request, PointRecord $pointRecord): RedirectResponse
    {
        abort_if($pointRecord->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'time' => ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'time.required' => 'O horário é obrigatório.',
            'time.regex' => 'Horário inválido (deve ser HH:MM no formato 24h).',
        ]);

        try {
            $this->pointRecordService->correct($pointRecord, $validated);

            return redirect()->route('dashboard')->with('success', 'Marcação de ponto corrigida com sucesso!');
        } catch (Exception $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function destroy(Request $request, PointRecord $pointRecord): RedirectResponse
    {
        abort_if($pointRecord->user_id !== $request->user()->id, 403);

        $workDay = $pointRecord->workDay;
        $pointRecord->delete();

        $this->workDayService->updateWorkDayStats($workDay);

        $workDay->refresh();
        if ($workDay->isComplete()) {
            $this->hourBankService->syncDailyTransaction($workDay);
        }

        return redirect()->route('dashboard')->with('success', 'Marcação removida com sucesso.');
    }
}
