<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    protected function prepareForValidation(): void { $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]); }
    public function authorize(): bool { return true; }
    public function rules(): array { return ['email' => ['required', 'email', 'max:254'], 'password' => ['required', 'string', 'max:4096']]; }
}
