<?php

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\deleteJson;
use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;
use function Pest\Laravel\putJson;

uses(Tests\TestCase::class);

it('success creating a user given valid data', function () {
    //
    $data = User::factory()->make()->only([
        'first_name', 'last_name', 'email',
        'birthdate', 'location', 'timezone',
    ]);

    postJson(route('api.users.store'), array_merge($data, [
        'password' => '12345!@#',
        'password_confirmation' => '12345!@#',
    ]))
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Successfully create a user')
        ->assertJsonPath('data.first_name', $data['first_name'])
        ->assertJsonPath('data.last_name', $data['last_name'])
        ->assertJsonPath('data.email', $data['email']);

    assertDatabaseHas((new User)->getTable(), $data);

    $user = User::latest()->first();

    $birthdate = Carbon::parse($data['birthdate'])->format('Y-m-d');

    expect($user)
        ->first_name->toBe($data['first_name'])
        ->last_name->toBe($data['last_name'])
        ->full_name->toBe($data['first_name'].' '.$data['last_name'])
        ->email->toBe($data['email'])
        ->birthdate->format('Y-m-d')->toBe($birthdate)
        ->location->toBe($data['location'])
        ->timezone->toBe($data['timezone']);

    expect($user->greetings()->count())->toBe(1);
});

it('fails creating a user given invaid data', function () {
    //
    $data = [
        'email' => 'invalid email',
        'password' => '12345',
        'timezone' => 'invalid timezone',
        'birthdate' => 'invalid date',
    ];

    postJson(route('api.users.store'), $data)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('success', false)
        ->assertInvalid([
            'first_name' => 'The first name field is required.',
            'last_name' => 'The last name field is required.',
            'email' => 'The email must be a valid email address.',
            'password' => 'The password must be at least 8 characters.',
            'birthdate' => 'The birthdate is not a valid date.',
            'location' => 'The location field is required.',
            'timezone' => 'The selected timezone is invalid.',
        ]);

    assertDatabaseMissing((new User)->getTable(), Arr::except($data, 'password'));
});

it('success getting a user given valid id', function () {
    //
    $user = new_test_user();

    getJson(route('api.users.show', $user))
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Succesfully fetch a user')
        ->assertJsonPath('data', [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'location' => $user->location,
            'timezone' => $user->timezone,
            'birthdate' => $user->birthdate->toISOString(),
            'full_name' => $user->full_name,
        ]);
});

it('fails getting a user given invalid id', function () {
    //
    getJson(route('api.users.show', 1337))->assertApiResponseNotFound();
});

it('success updating a user given valid data', function () {
    //
    $user = new_test_user();

    $data = [
        'first_name' => 'Ismail',
        'last_name' => 'Marjuki',
        'email' => 'ismail@marjuki.com',
        'birthdate' => $user->birthdate->format('Y-m-d'),
        'location' => fake()->city(),
        'timezone' => $user->timezone,
    ];

    putJson(route('api.users.update', $user->id), $data)
        ->assertStatus(Response::HTTP_ACCEPTED)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Successfully update a user')
        ->assertJsonPath('data.location', $data['location'])
        ->assertJsonPath('data.timezone', $data['timezone']);

    expect($user->refresh())
        ->location->toBe($data['location'])
        ->timezone->toBe($data['timezone']);
});

it('fails updating a user given invalid data', function () {
    //
    $user1 = new_test_user();
    $user2 = new_test_user();

    $data = [
        'first_name' => Str::random(64),
        'last_name' => Str::random(64),
        'email' => $user2->email,
        'location' => $user1->location,
        'timezone' => 'invalid timezone',
    ];

    putJson(route('api.users.update', $user1->id), $data)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('success', false)
        ->assertInvalid([
            'first_name' => 'The first name must not be greater than 32 characters.',
            'last_name' => 'The last name must not be greater than 32 characters.',
            'email' => 'The email has already been taken.',
            'timezone' => 'The selected timezone is invalid.',
        ]);

    expect($user1->refresh())
        ->first_name->not->toBe($data['first_name'])
        ->last_name->not->toBe($data['last_name'])
        ->email->not->toBe($data['email'])
        ->location->toBe($data['location'])
        ->timezone->not->toBe($data['timezone']);
});

it('success deleting a user given valid id', function () {
    //
    $user = new_test_user();

    deleteJson(route('api.users.destroy', $user->id))
        ->assertStatus(Response::HTTP_OK)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Succesfully delete a user');

    assertDatabaseMissing((new User)->getTable(), ['id' => $user->id]);
});

it('fails deleting a user given invalid id', function () {
    //
    deleteJson(route('api.users.destroy', 1337))->assertApiResponseNotFound();
});

it('success creating new greeting given user update their birthday or timezone', function () {
    //
    $user = new_test_user();

    $data = [
        'first_name' => $user->first_name,
        'last_name' => $user->last_name,
        'email' => $user->email,
        'location' => $user->location,
        'birthdate' => today()->addMonth()->format('Y-m-d'),
        'timezone' => Arr::random(get_all_timezones()),
    ];

    expect($user->greetings()->count())->toBe(0);

    putJson(route('api.users.update', $user->id), $data)
        ->assertStatus(Response::HTTP_ACCEPTED)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Successfully update a user')
        ->assertJsonPath('data.location', $data['location'])
        ->assertJsonPath('data.timezone', $data['timezone']);

    expect($user->greetings()->count())->toBe(1);

    expect($user->refresh())
        ->location->toBe($data['location'])
        ->timezone->toBe($data['timezone']);
});
