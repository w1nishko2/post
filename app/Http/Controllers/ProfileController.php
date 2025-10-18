<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Конструктор контроллера.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Отобразить страницу профиля пользователя.
     */
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    /**
     * Обновить email пользователя.
     */
    public function updateEmail(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'email.required' => 'Поле Email обязательно для заполнения.',
            'email.email' => 'Введите корректный email адрес.',
            'email.unique' => 'Этот email уже используется другим пользователем.',
            'email.max' => 'Email не должен превышать 255 символов.',
        ]);

        $user->email = $request->email;
        $user->save();

        return back()->with('success', 'Email успешно обновлен!');
    }

    /**
     * Обновить пароль пользователя.
     */
    public function updatePassword(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'current_password' => [
                'required', 
                function ($attribute, $value, $fail) use ($user) {
                    if (!Hash::check($value, $user->password)) {
                        $fail('Текущий пароль неверен.');
                    }
                }
            ],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'current_password.required' => 'Поле текущего пароля обязательно для заполнения.',
            'password.required' => 'Поле нового пароля обязательно для заполнения.',
            'password.confirmed' => 'Подтверждение пароля не совпадает.',
            'password.min' => 'Пароль должен содержать минимум 8 символов.',
        ]);
        $user->password = Hash::make($request->password);
        $user->save();

        return back()->with('success', 'Пароль успешно обновлен!');
    }

    /**
     * Обновить имя пользователя.
     */
    public function updateName(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], [
            'name.required' => 'Поле имени обязательно для заполнения.',
            'name.max' => 'Имя не должно превышать 255 символов.',
        ]);

        /** @var \App\Models\User $user */
        $user = Auth::user();
        $user->name = $request->name;
        $user->save();

        return back()->with('success', 'Имя успешно обновлено!');
    }
}