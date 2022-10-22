<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        /** @var User $this */
        $data = $this->only([
            'id', 'first_name', 'last_name', 'email', 'location', 'timezone',
        ]);

        return array_merge($data, [
            'birthdate' => $this->birthdate->toISOString(),
            'full_name' => $this->full_name,
        ]);
    }
}
