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

    public function test_financial_report_loads_initial_entries_and_filter_options(): void
    {
        $user = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', $this->cpf(1));
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', $this->cnpj(1));

        for ($index = 1; $index <= 21; $index++) {
            FinancialEntry::create($this->entryData(
                $user,
                $index % 2 === 0 ? $supplier : $customer,
                $index % 2 === 0 ? FinancialEntry::TYPE_PAYABLE : FinancialEntry::TYPE_RECEIVABLE,
                FinancialEntry::STATUS_PENDING,
                (string) (100 + $index),
                sprintf('2026-08-%02d', $index),
                "Conta {$index}",
            ));
        }

        $this
            ->actingAs($user)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Reports/Financial')
                ->has('initialEntries.data', 20)
                ->where('initialEntries.total', 21)
                ->where('initialEntries.has_more', true)
                ->where('initialEntries.data.0.person_type_slug', PersonType::CUSTOMER)
                ->where('initialEntries.data.0.person_document', '000.000.000-01')
                ->where('filters.person_type', '')
                ->where('filters.person_search', '')
                ->where('filters.type', '')
                ->has('personTypeOptions', 2)
                ->where('personTypeOptions.0.value', PersonType::CUSTOMER)
                ->where('personTypeOptions.1.value', PersonType::SUPPLIER)
            );
    }

    public function test_report_entries_endpoint_loads_more_entries_without_leaking_other_users_data(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', $this->cpf(1));
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Oculto', $this->cpf(1));

        for ($index = 1; $index <= 21; $index++) {
            FinancialEntry::create($this->entryData(
                $user,
                $customer,
                FinancialEntry::TYPE_RECEIVABLE,
                FinancialEntry::STATUS_PENDING,
                (string) (100 + $index),
                sprintf('2026-08-%02d', $index),
                "Conta {$index}",
            ));
        }

        FinancialEntry::create($this->entryData(
            $otherUser,
            $otherCustomer,
            FinancialEntry::TYPE_RECEIVABLE,
            FinancialEntry::STATUS_PENDING,
            '999.00',
            '2026-08-01',
            'Conta oculta',
        ));

        $this
            ->actingAs($user)
            ->getJson(route('reports.entries', ['page' => 2]))
            ->assertOk()
            ->assertJsonPath('current_page', 2)
            ->assertJsonPath('total', 21)
            ->assertJsonPath('has_more', false)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.description', 'Conta 21');
    }

    public function test_report_entries_payload_contains_person_data_for_local_search(): void
    {
        $user = User::factory()->create();
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Busca', $this->cnpj(7));

        FinancialEntry::create($this->entryData(
            $user,
            $supplier,
            FinancialEntry::TYPE_PAYABLE,
            FinancialEntry::STATUS_PAID,
            '250.00',
            '2026-08-10',
            'Pagamento buscavel',
        ));

        $this
            ->actingAs($user)
            ->getJson(route('reports.entries'))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.person_name', 'Fornecedor Busca')
            ->assertJsonPath('data.0.person_type_slug', PersonType::SUPPLIER)
            ->assertJsonPath('data.0.person_document', '00.000.000/0000-07')
            ->assertJsonPath('data.0.person_document_digits', $this->cnpj(7));
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

    private function cpf(int $index): string
    {
        return str_pad((string) $index, 11, '0', STR_PAD_LEFT);
    }

    private function cnpj(int $index): string
    {
        return str_pad((string) $index, 14, '0', STR_PAD_LEFT);
    }
}