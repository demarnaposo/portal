<?php

namespace Modules\Assessment\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Validasi bentuk saja (batas ukuran) sebelum diteruskan ke CMS; validasi isi (pertanyaan & opsi yang
 * dikenal, kelengkapan) dilakukan CMS dan 422-nya diteruskan apa adanya.
 */
final class ScoreAssessmentRequest extends FormRequest
{
    public const MAX_ANSWERS = 100;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'answers' => ['required', 'array', 'max:'.self::MAX_ANSWERS],
            'answers.*' => ['required', 'string', 'max:100'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            foreach (array_keys((array) $this->input('answers')) as $key) {
                if (! is_string($key) || ! preg_match('/^[\w\-]{1,100}$/', $key)) {
                    $validator->errors()->add('answers', 'Format jawaban tidak valid.');

                    return;
                }
            }
        }];
    }

    public function messages(): array
    {
        return [
            'answers.required' => 'Jawaban wajib diisi.',
            'answers.array' => 'Format jawaban tidak valid.',
            'answers.max' => 'Format jawaban tidak valid.',
            'answers.*.required' => 'Pertanyaan ini wajib dijawab.',
            'answers.*.string' => 'Pilihan jawaban tidak valid.',
            'answers.*.max' => 'Pilihan jawaban tidak valid.',
        ];
    }

    /** @return array<string, string> */
    public function answers(): array
    {
        return array_map('strval', (array) $this->validated('answers'));
    }
}
