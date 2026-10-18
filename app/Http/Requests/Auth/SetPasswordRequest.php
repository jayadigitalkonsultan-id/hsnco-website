<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class SetPasswordRequest extends FormRequest
{
    protected function prepareForValidation(): void { $this->merge(['email' => mb_strtolower(trim((string) $this->input('email')))]); }
    public function authorize(): bool { return true; }
    public function rules(): array { return ['email' => ['required','email'], 'name' => ['required','string','max:255'], 'password' => ['required','confirmed', Password::min(12)->mixedCase()->numbers()->symbols()]]; }
}
