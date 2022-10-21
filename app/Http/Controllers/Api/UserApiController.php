<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
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
            'name' => 'required|max:64',
            'email' => 'required|email|unique:users',
            'password' => ['required', Password::default()],
            'location' => 'required|max:128',
            'timezone' => ['required', Rule::in(get_all_timezones())],
        ]);

        $user = User::create($validatedData);

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
            'name' => 'required|max:64',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => ['nullable', Password::default()],
            'location' => 'required|max:128',
            'timezone' => ['required', Rule::in(get_all_timezones())],
        ]);

        $user->update($validatedData);

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
