<?php

namespace Database\Factories;

use App\Enums\GreetingType;
use App\Models\Greeting;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Greeting>
 */
class GreetingFactory extends Factory
{
    protected $model = Greeting::class;

    public function definition(): array
    {
        return ['type' => GreetingType::BIRTHDAY];
    }

    public function withUser(User $user): self
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
            'message' => $attributes['type']->getMessage($user->full_name),
            'for_date' => $user->nextBirthday(),
            'available_at' => $user->nextBirthdayInLocalTimeConvertedToUTC(),
            'metadata' => [
                'original' => [
                    'email' => $user->email,
                    'timezone' => $user->timezone,
                    'localtime' => $user->nextBirthdayShouldBeGreetedAt()->format('Y-m-d H:i:s'),
                    'utctime' => $user->nextBirthdayInLocalTimeConvertedToUTC()->format('Y-m-d H:i:s'),
                ],
            ],
        ]);
    }
}
