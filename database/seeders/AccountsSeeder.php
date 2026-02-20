<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $defaults = [
            ['code' => '1000', 'name' => 'Cash', 'type' => 'asset'],
            ['code' => '4000', 'name' => 'Sales Revenue (Cash Basis)', 'type' => 'revenue'],
            ['code' => '5100', 'name' => 'Inventory Purchases (Cash Basis)', 'type' => 'expense'],
            ['code' => '6000', 'name' => 'Operating Expenses (Cash Basis)', 'type' => 'expense'],
        ];

        foreach ($defaults as $a) {
            Account::firstOrCreate(
                ['code' => $a['code']],
                ['name' => $a['name'], 'type' => $a['type'], 'is_system' => true]
            );
        }
    }
}