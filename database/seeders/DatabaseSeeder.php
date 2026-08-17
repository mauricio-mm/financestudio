<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $userId = $user->id;

        $customerTypeId = DB::table('person_types')->updateOrInsert(
            ['slug' => 'customer'],
            [
                'name' => 'Cliente',
                'slug' => 'customer',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $supplierTypeId = DB::table('person_types')->updateOrInsert(
            ['slug' => 'supplier'],
            [
                'name' => 'Fornecedor',
                'slug' => 'supplier',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $customerTypeId = DB::table('person_types')
            ->where('slug', 'customer')
            ->value('id');

        $supplierTypeId = DB::table('person_types')
            ->where('slug', 'supplier')
            ->value('id');

        
        $customers = [
            [
                'name' => 'Empresa Alpha Ltda',
                'document' => '12345678000101',
                'email' => 'contato@alpha.com',
                'phone' => '(51) 99999-1001',
            ],
            [
                'name' => 'Empresa Beta Ltda',
                'document' => '23456789000102',
                'email' => 'contato@beta.com',
                'phone' => '(51) 99999-1002',
            ],
            [
                'name' => 'Empresa Gamma Ltda',
                'document' => '34567890000103',
                'email' => 'contato@gamma.com',
                'phone' => '(51) 99999-1003',
            ],
            [
                'name' => 'Empresa Delta Ltda',
                'document' => '45678901000104',
                'email' => 'contato@delta.com',
                'phone' => '(51) 99999-1004',
            ],
            [
                'name' => 'Empresa Epsilon Ltda',
                'document' => '56789012000105',
                'email' => 'contato@epsilon.com',
                'phone' => '(51) 99999-1005',
            ],
        ];

        $suppliers = [
            [
                'name' => 'Fornecedor Alfa',
                'document' => '67890123000106',
                'email' => 'contato@fornecedor-alfa.com',
                'phone' => '(51) 98888-2001',
            ],
            [
                'name' => 'Fornecedor Beta',
                'document' => '78901234000107',
                'email' => 'contato@fornecedor-beta.com',
                'phone' => '(51) 98888-2002',
            ],
            [
                'name' => 'Fornecedor Gamma',
                'document' => '89012345000108',
                'email' => 'contato@fornecedor-gamma.com',
                'phone' => '(51) 98888-2003',
            ],
            [
                'name' => 'Fornecedor Delta',
                'document' => '90123456000109',
                'email' => 'contato@fornecedor-delta.com',
                'phone' => '(51) 98888-2004',
            ],
            [
                'name' => 'Fornecedor Epsilon',
                'document' => '01234567000110',
                'email' => 'contato@fornecedor-epsilon.com',
                'phone' => '(51) 98888-2005',
            ],
        ];

        $people = [];

        foreach ($customers as $customer) {
            $people[] = DB::table('people')->insertGetId([
                'user_id' => $userId,
                'person_type_id' => $customerTypeId,
                'name' => $customer['name'],
                'document' => $customer['document'],
                'email' => $customer['email'],
                'phone' => $customer['phone'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($suppliers as $supplier) {
            $people[] = DB::table('people')->insertGetId([
                'user_id' => $userId,
                'person_type_id' => $supplierTypeId,
                'name' => $supplier['name'],
                'document' => $supplier['document'],
                'email' => $supplier['email'],
                'phone' => $supplier['phone'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach ($people as $index => $personId) {
            $isCustomer = $index < count($customers);

            DB::table('financial_entries')->insert([
                'user_id' => $userId,
                'person_id' => $personId,
                'type' => $isCustomer ? 'receivable' : 'payable',
                'description' => $isCustomer
                    ? 'Venda de produtos/serviços'
                    : 'Compra de produtos/serviços',
                'amount' => match ($index % 5) {
                    0 => 1500.00,
                    1 => 2750.00,
                    2 => 3200.00,
                    3 => 4800.00,
                    default => 6250.00,
                },
                'issue_date' => Carbon::now()->subDays($index + 1)->toDateString(),
                'due_date' => Carbon::now()->addDays(15 + $index)->toDateString(),
                'status' => 'pending',
                'settlement_date' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
