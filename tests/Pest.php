<?php

use App\Models\User;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

//

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

//

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
*/

function new_test_user(array $data = []): User
{
    return User::factory()->create($data);
}