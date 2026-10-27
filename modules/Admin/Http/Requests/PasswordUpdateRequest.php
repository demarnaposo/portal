<?php

namespace Modules\Admin\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

final class PasswordUpdateRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password:admin'],
            'password' => ['required', 'string', 'confirmed', Password::min(12)],
        ];
    }

    public function attributes(): array
    {
        return ['current_password' => 'kata sandi saat ini', 'password' => 'kata sandi'];
    }
}
