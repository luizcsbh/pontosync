<?php

namespace App\Http\Requests\PointRecord;

use Illuminate\Foundation\Http\FormRequest;

class StorePointRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:d/m/Y'],
            'time' => ['required', 'regex:/^([01][0-9]|2[0-3]):[0-5][0-9]$/'],
            'type' => ['required', 'in:entry,lunch_start,lunch_end,exit'],
            'source' => ['required', 'in:manual,ocr,correction'],
            'notes' => ['nullable', 'string', 'max:500'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            'ocr_confidence' => ['nullable', 'numeric', 'min:0', 'max:1'],
            'ocr_raw_text' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date_format' => 'A data deve estar no formato DD/MM/AAAA válido.',
            'time.required' => 'O horário é obrigatório.',
            'time.regex' => 'O horário deve estar no formato 24h válido (HH:MM de 00:00 a 23:59).',
            'type.required' => 'O tipo de marcação é obrigatório.',
            'type.in' => 'Tipo de marcação inválido.',
            'photo.image' => 'O arquivo enviado deve ser uma imagem.',
            'photo.max' => 'A imagem não pode ultrapassar 10MB.',
        ];
    }
}
