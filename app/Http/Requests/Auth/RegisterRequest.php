<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

/** Registro público: solo crea clientas. Las profesionales las crea el admin. */
class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // Celular colombiano guardado con indicativo (57) para los enlaces de WhatsApp.
            'telefono' => ['required', 'string', 'regex:/^573\d{9}$/'],
            'password' => ['required', 'string', Password::min(8)],
            'acepta_terminos' => ['accepted'],
            'dispositivo' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.regex' => 'El teléfono debe ser un celular colombiano de 10 dígitos.',
            'acepta_terminos.accepted' => 'Debes aceptar los términos y la política de datos.',
            'email.unique' => 'Ya existe una cuenta con ese correo.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => mb_strtolower(trim((string) $this->input('email'))),
            'telefono' => $this->normalizarTelefono((string) $this->input('telefono')),
        ]);
    }

    /** "300 123 4567", "+57 300-123-4567" y "573001234567" quedan como "573001234567". */
    private function normalizarTelefono(string $telefono): string
    {
        $digitos = preg_replace('/\D/', '', $telefono);

        return strlen($digitos) === 10 ? '57'.$digitos : $digitos;
    }
}
