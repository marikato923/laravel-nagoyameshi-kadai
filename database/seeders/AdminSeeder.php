<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 管理者用アカウント
        $admin = new Admin();
        $admin->email = 'kato@example.com';
        $admin->password = Hash::make('nagoyameshi');
        $admin->save();

        // 課題閲覧用管理者アカウント
        $asmin = new Admin();
        $admin->email = 'kadai@example.com';
        $admin->password = Hash::make('1234pass');
        $admin->save();
    }
}
