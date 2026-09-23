<?php

namespace Database\Factories\Domains\TeacherPortal\Models;

use App\Domains\Accounts\Models\User;
use App\Domains\TeacherPortal\Models\TeacherMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TeacherMessage>
 */
class TeacherMessageFactory extends Factory
{
    protected $model = TeacherMessage::class;

    public function definition(): array
    {
        return [
            'sender_id' => User::factory(),
            'recipient_type' => 'teacher',
            'recipient_id' => User::factory(),
            'class_room_id' => null,
            'subject' => fake()->sentence(4),
            'body' => fake()->paragraph(),
            'message_type' => 'individual',
            'status' => 'sent',
        ];
    }
}
