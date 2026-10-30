<?php

namespace Database\Seeders;

use App\Models\Dataset;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Default Administrator
        $admin = User::firstOrCreate(
            ['email' => 'admin@marketbasket.com'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ]
        );

        // 2. Seed Standard Benchmark Dataset
        if (Dataset::where('name', 'Dataset Contoh (5 Transaksi Standar)')->doesntExist()) {
            $dataset = Dataset::create([
                'user_id' => $admin->id,
                'name' => 'Dataset Contoh (5 Transaksi Standar)',
                'description' => 'Dataset acuan pengujian algoritma Apriori dengan 5 transaksi (Roti, Susu, Mentega).',
                'original_filename' => 'transaksi_benchmark.csv',
                'file_type' => 'csv',
                'total_rows' => 11,
                'total_transactions' => 5,
                'total_items' => 3,
                'status' => 'imported',
                'imported_at' => now(),
            ]);

            $roti = Item::create(['dataset_id' => $dataset->id, 'name' => 'Roti', 'normalized_name' => 'roti']);
            $susu = Item::create(['dataset_id' => $dataset->id, 'name' => 'Susu', 'normalized_name' => 'susu']);
            $mentega = Item::create(['dataset_id' => $dataset->id, 'name' => 'Mentega', 'normalized_name' => 'mentega']);

            $transactions = [
                'TR001' => [$roti, $susu],
                'TR002' => [$roti, $mentega],
                'TR003' => [$roti, $susu, $mentega],
                'TR004' => [$susu, $mentega],
                'TR005' => [$roti, $susu],
            ];

            foreach ($transactions as $code => $items) {
                $tx = Transaction::create([
                    'dataset_id' => $dataset->id,
                    'transaction_code' => $code,
                    'transaction_at' => now()->subDays(rand(1, 10)),
                ]);

                foreach ($items as $item) {
                    TransactionItem::create([
                        'transaction_id' => $tx->id,
                        'item_id' => $item->id,
                        'quantity' => 1,
                    ]);
                }
            }
        }
    }
}
