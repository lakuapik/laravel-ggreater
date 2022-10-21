<?php

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
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
    $data = [
        'name' => fake()->name(),
        'email' => fake()->email(),
        'password' => Str::random(),
        'location' => fake()->city(),
        'timezone' => Arr::random(get_all_timezones()),
    ];

    postJson(route('api.users.store'), $data)
        ->assertStatus(Response::HTTP_CREATED)
        ->assertJsonPath('success', true)
        ->assertJsonPath('message', 'Successfully create a user')
        ->assertJsonPath('data.name', $data['name'])
        ->assertJsonPath('data.email', $data['email']);

    assertDatabaseHas((new User)->getTable(), Arr::except($data, 'password'));

    expect(User::latest()->first())
        ->name->toBe($data['name'])
        ->email->toBe($data['email'])
        ->location->toBe($data['location'])
        ->timezone->toBe($data['timezone']);
});

it('fails creating a user given invaid data', function () {
    //
    $data = [
        'email' => 'invalid email',
        'password' => '12345',
        'timezone' => 'invalid timezone',
    ];

    postJson(route('api.users.store'), $data)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('success', false)
        ->assertInvalid([
            'name' => 'The name field is required.',
            'email' => 'The email must be a valid email address.',
            'password' => 'The password must be at least 8 characters.',
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
            'name' => $user->name,
            'email' => $user->email,
            'location' => $user->location,
            'timezone' => $user->timezone,
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
        'name' => $user->name,
        'email' => $user->email,
        'location' => fake()->city(),
        'timezone' => Arr::random(get_all_timezones()),
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
        'name' => Str::random(128),
        'email' => $user2->email,
        'location' => $user1->location,
        'timezone' => 'invalid timezone',
    ];

    putJson(route('api.users.update', $user1->id), $data)
        ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
        ->assertJsonPath('success', false)
        ->assertInvalid([
            'name' => 'The name must not be greater than 64 characters.',
            'email' => 'The email has already been taken.',
            'timezone' => 'The selected timezone is invalid.',
        ]);

    expect($user1->refresh())
        ->name->not->toBe($data['name'])
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
