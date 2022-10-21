<?php

use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertAuthenticated;
use function Pest\Laravel\assertAuthenticatedAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\assertGuest;
use function Pest\Laravel\followingRedirects;
use function Pest\Laravel\get;

uses(\Tests\TestCase::class);

it('success showing register form', function () {
    //
    get(route('register'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Register')
        ->assertSee('an account to use the app');
});

it('success register in a user given valid data', function () {
    //
    $data = [
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'password' => '12345!@#',
        'password_confirmation' => '12345!@#',
        'birthdate' => date('Y-m-d'),
        'location' => fake()->city(),
        'timezone' => Arr::random(get_all_timezones()),
    ];

    followingRedirects()
        ->from(route('register'))
        ->post(route('register'), $data)
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Dashboard')
        ->assertSee($data['name']);

    $user = User::latest()->first();

    assertAuthenticated('web');
    assertAuthenticatedAs($user);

    assertDatabaseHas((new User)->getTable(), ['email' => $data['email']]);

    expect($user)
        ->name->toBe($data['name'])
        ->email->toBe($data['email'])
        ->location->toBe($data['location'])
        ->timezone->toBe($data['timezone']);
});

it('fails registering a user given invalid data', function () {
    //
    $data = [
        'email' => 'invalid email',
        'password' => '12345!@#',
        'timezone' => 'invalid timezone',
    ];

    followingRedirects()
        ->from(route('register'))
        ->post(route('register'), $data)
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Register')
        ->assertSee('The name field is required.')
        ->assertSee('The email must be a valid email address.')
        ->assertSee('The location field is required.')
        ->assertSee('The selected timezone is invalid.');

    assertGuest('web');

    assertDatabaseMissing((new User)->getTable(), ['email' => $data['email']]);
});

it('success showing login form', function () {
    //
    get(route('login'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Login')
        ->assertSee('to start your session');
});

it('success logging in a user given valid credential', function () {
    //
    $user = new_test_user(['password' => Hash::make('12345!@#')]);

    assertGuest('web');

    followingRedirects()
        ->from(route('login'))
        ->post(route('login'), [
            'email' => $user->email,
            'password' => '12345!@#',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Dashboard')
        ->assertSee($user->name);

    assertAuthenticated('web');
    assertAuthenticatedAs($user);
});

it('fails loggin in a user given invaid credential', function () {
    //
    assertGuest('web');

    followingRedirects()
        ->from(route('login'))
        ->post(route('login'), [
            'email' => 'invalid@email.com',
            'password' => 'random-password',
        ])
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Login')
        ->assertSee('These credentials do not match our records.');

    assertGuest('web');
});

it('success logging out a user given valid session', function () {
    //
    $user = new_test_user();

    actingAs($user);

    assertAuthenticated('web');
    assertAuthenticatedAs($user);

    followingRedirects()
        ->from(route('dashboard'))
        ->post(route('logout'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Login');

    assertGuest('web');
});

it('success logging out a user given invalid session', function () {
    //
    assertGuest('web');

    followingRedirects()
        ->post(route('logout'))
        ->assertStatus(Response::HTTP_OK)
        ->assertSee('Login');

    assertGuest('web');
});
