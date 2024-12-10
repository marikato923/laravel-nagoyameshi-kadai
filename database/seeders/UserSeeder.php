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
        User::factory()->count(100)->create();

        // 閲覧用アカウントを1件追加
        User::create([
            'name' => '侍太郎',         
            'email' => 'viewer@example.com',  
            'password' => 'password',
            'postal_code' => '1111111',
            'address' => '東京都千代田区霞ヶ関１−１',
            'phone_number' => '00000000000',
            'birthday'=> '19950505',
            'occupation' => 'エンジニア',
        ]);
    }
}
