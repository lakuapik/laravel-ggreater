<?php

namespace App\Http\Controllers\Api;

use App\Enums\GreetingType;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use KodePandai\ApiResponse\ApiResponse;

class UserApiController extends Controller
{
    public function store(): ApiResponse
    {
        $validatedData = ApiResponse::validateOrFail([
            'first_name' => 'required|max:32',
            'last_name' => 'required|max:32',
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::default()],
            'birthdate' => 'required|date',
            'location' => 'required|max:128',
            'timezone' => ['required', Rule::in(get_all_timezones())],
        ]);

        $user = User::create($validatedData);

        Greeting::factory()->withUser($user)
            ->create(['type' => GreetingType::BIRTHDAY]);

        // TBD: send email verification to email?
        // TBD: send welcome notification to email?

        return ApiResponse::success(UserResource::make($user))
            ->statusCode(Response::HTTP_CREATED)
            ->title('User')
            ->message('Successfully create a user');
    }

    public function show(User $user): ApiResponse
    {
        return ApiResponse::success(UserResource::make($user))
            ->title('User')
            ->message('Succesfully fetch a user');
    }

    public function update(User $user): ApiResponse
    {
        $validatedData = ApiResponse::validateOrFail([
            'first_name' => 'required|max:32',
            'last_name' => 'required|max:32',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => ['nullable', Password::default()],
            'birthdate' => 'required|date',
            'location' => 'required|max:128',
            'timezone' => ['required', Rule::in(get_all_timezones())],
        ]);

        $user->update($validatedData);

        if ($user->wasChanged('birthdate') || $user->wasChanged('timezone')) {
            //.
            $user->greetings()->ready()
                ->where('type', GreetingType::BIRTHDAY->value)->delete();

            Greeting::factory()->withUser($user)
                ->create(['type' => GreetingType::BIRTHDAY]);
        }

        // TBD: if email was changed, resend email verification?

        return ApiResponse::success(UserResource::make($user->refresh()))
            ->statusCode(Response::HTTP_ACCEPTED)
            ->title('User')
            ->message('Successfully update a user');
    }

    public function destroy(User $user): ApiResponse
    {
        // TBD: validate what kind of user is deleteable?

        // TODO: delete user related data

        $user->delete();

        return ApiResponse::success()
            ->title('User')
            ->message('Succesfully delete a user');
    }
}
