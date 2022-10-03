<?php

namespace Database\Factories;

use App\Models\Message\Message;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MessageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Message::class;

    /**
     * Define the model's default state.
     *
     * @return array
     * @throws \Exception
     */
    public function definition()
    {
        return [
            "chat_room_id" => 4,
            "sender_id" => $sender_id = random_int(1,2) > 1 ? 1 : 2,
            'receiver_id' => $sender_id = 1 ? 2 : 1,
            "message" => $this->faker->sentence(random_int(2,9)),
            "audio" => null,
            "created_at" => Carbon::now()
        ];
    }
}
