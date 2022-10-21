<?php

namespace App\Http\Controllers\Web;

use App\Enums\GreetingType;
use App\Http\Controllers\Controller;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthenticationWebController extends Controller
{
    public function register(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|max:64',
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::default(), 'confirmed'],
            'birthdate' => 'required|date',
            'location' => 'required|max:128',
            'timezone' => ['required', Rule::in(get_all_timezones())],
        ]);

        $user = User::create(array_merge($validatedData, [
            'password' => Hash::make($validatedData['password']),
        ]));

        Greeting::factory()->withUser($user)
            ->create(['type' => GreetingType::BIRTHDAY]);

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (! Auth::attempt($validatedData)) {
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
