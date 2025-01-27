<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::factory(5)->create();
        $users->each(function($user){
            $events = Event::factory(random_int(1,3))->create([
                'user_id' => $user->id
            ]);

            $events->each(function($event){
                Guest::factory(random_int(5, 30))->create([
                    'event_id' => $event->id
                ]);
            });
        });
    }
}
