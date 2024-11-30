<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();
        $user->name = "桃子";
        $user->kana = "モモコ";
        $user->email = 'momo@example.com';
        $user->email_verified_at = Carbon::now();
        $user->password = Hash::make('password');
        $user->postal_code = "2222222";
        $user->address = "大阪府";
        $user->phone_number = "222-2222-2222";
        $user->save();
    }
}
