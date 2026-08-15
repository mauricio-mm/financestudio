<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_authenticated_users_financial_position(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Oculto', '98765432100');

        FinancialEntry::create($this->entryData($user, $customer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_PENDING, '1000.00'));
        FinancialEntry::create($this->entryData($user, $customer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_RECEIVED, '500.00', now()->toDateString()));
        FinancialEntry::create($this->entryData($user, $customer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_OVERDUE, '150.00'));
        FinancialEntry::create($this->entryData($user, $supplier, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_PENDING, '300.00'));
        FinancialEntry::create($this->entryData($user, $supplier, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_PAID, '120.00', now()->toDateString()));
        FinancialEntry::create($this->entryData($user, $supplier, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_OVERDUE, '40.00'));
        FinancialEntry::create($this->entryData($otherUser, $otherCustomer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_PENDING, '9999.00'));

        $this
            ->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Dashboard')
                ->where('people.customers', 1)
                ->where('people.suppliers', 1)
                ->where('people.total', 2)
                ->where('metrics.receivable_pending', 1000)
                ->where('metrics.receivable_received', 500)
                ->where('metrics.receivable_overdue', 150)
                ->where('metrics.payable_pending', 300)
                ->where('metrics.payable_paid', 120)
                ->where('metrics.payable_overdue', 40)
                ->where('metrics.forecast_balance', 810)
                ->where('metrics.realized_balance', 380)
                ->has('cashFlow', 6)
                ->has('upcomingBills')
            );
    }

    private function personFor(User $user, string $typeSlug, string $name, string $document): Person
    {
        $type = PersonType::where('slug', $typeSlug)->firstOrFail();

        return Person::create([
            'user_id' => $user->id,
            'person_type_id' => $type->id,
            'name' => $name,
            'document' => $document,
        ]);
    }

    private function entryData(User $user, Person $person, string $type, string $status, string $amount, ?string $settlementDate = null): array
    {
        return [
            'user_id' => $user->id,
            'person_id' => $person->id,
            'type' => $type,
            'description' => $type === FinancialEntry::TYPE_RECEIVABLE ? 'Conta a receber' : 'Conta a pagar',
            'amount' => $amount,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(3)->toDateString(),
            'status' => $status,
            'settlement_date' => $settlementDate,
        ];
    }
}