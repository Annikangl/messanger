<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserChatRoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users_chat_rooms')->insert([
            "user_id" => 2,
            "chat_room_id" => 13,
            "created_at" => Carbon::now()
        ]);
    }
}
