<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class FinancialReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_financial_report_filters_entries_and_totals(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Oculto', '98765432100');

        FinancialEntry::create($this->entryData($user, $customer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_PENDING, '100.00', '2026-08-20', 'Recebimento filtrado'));
        FinancialEntry::create($this->entryData($user, $customer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_RECEIVED, '50.00', '2026-08-22', 'Recebimento fora do status'));
        FinancialEntry::create($this->entryData($user, $supplier, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_PENDING, '30.00', '2026-08-25', 'Pagamento fora do tipo'));
        FinancialEntry::create($this->entryData($otherUser, $otherCustomer, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_PENDING, '999.00', '2026-08-20', 'Recebimento oculto'));

        $this
            ->actingAs($user)
            ->get(route('reports.index', [
                'date_from' => '2026-08-01',
                'date_to' => '2026-08-31',
                'person_id' => $customer->id,
                'type' => FinancialEntry::TYPE_RECEIVABLE,
                'status' => FinancialEntry::STATUS_PENDING,
            ]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Financial')
                ->where('summary.count', 1)
                ->where('summary.total_amount', 100)
                ->where('summary.receivable_total', 100)
                ->where('summary.payable_total', 0)
                ->where('summary.pending_total', 100)
                ->where('summary.balance', 100)
                ->has('report.data', 1)
                ->where('report.data.0.description', 'Recebimento filtrado')
                ->where('report.data.0.person_name', 'Cliente Atlas')
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

    private function entryData(User $user, Person $person, string $type, string $status, string $amount, string $dueDate, string $description): array
    {
        return [
            'user_id' => $user->id,
            'person_id' => $person->id,
            'type' => $type,
            'description' => $description,
            'amount' => $amount,
            'issue_date' => '2026-08-15',
            'due_date' => $dueDate,
            'status' => $status,
            'settlement_date' => in_array($status, [FinancialEntry::STATUS_RECEIVED, FinancialEntry::STATUS_PAID], true)
                ? $dueDate
                : null,
        ];
    }
}