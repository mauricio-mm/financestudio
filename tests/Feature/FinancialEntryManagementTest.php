<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialEntryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_receivables_are_created_for_the_authenticated_users_customer(): void
    {
        $user = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');

        $this
            ->actingAs($user)
            ->post(route('financial-entries.store'), [
                'type' => FinancialEntry::TYPE_RECEIVABLE,
                'person_id' => $customer->id,
                'description' => 'Mensalidade de servico',
                'amount' => '1500,75',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
                'settlement_date' => null,
            ])
            ->assertRedirect(route('financial-entries.index', ['type' => FinancialEntry::TYPE_RECEIVABLE]));

        $entry = FinancialEntry::query()->firstOrFail();

        $this->assertSame($user->id, $entry->user_id);
        $this->assertSame($customer->id, $entry->person_id);
        $this->assertSame(FinancialEntry::TYPE_RECEIVABLE, $entry->type);
        $this->assertSame('Mensalidade de servico', $entry->description);
        $this->assertSame('1500.75', $entry->amount);
        $this->assertNull($entry->settlement_date);
    }

    public function test_payables_are_created_for_the_authenticated_users_supplier(): void
    {
        $user = User::factory()->create();
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');

        $this
            ->actingAs($user)
            ->post(route('financial-entries.store'), [
                'type' => FinancialEntry::TYPE_PAYABLE,
                'person_id' => $supplier->id,
                'description' => 'Assinatura de software',
                'amount' => '850,40',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
                'settlement_date' => null,
            ])
            ->assertRedirect(route('financial-entries.index', ['type' => FinancialEntry::TYPE_PAYABLE]));

        $entry = FinancialEntry::query()->firstOrFail();

        $this->assertSame($user->id, $entry->user_id);
        $this->assertSame($supplier->id, $entry->person_id);
        $this->assertSame(FinancialEntry::TYPE_PAYABLE, $entry->type);
        $this->assertSame('Assinatura de software', $entry->description);
        $this->assertSame('850.40', $entry->amount);
        $this->assertNull($entry->settlement_date);
    }

    public function test_entry_person_must_match_the_selected_type(): void
    {
        $user = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');

        $this
            ->actingAs($user)
            ->post(route('financial-entries.store'), [
                'type' => FinancialEntry::TYPE_RECEIVABLE,
                'person_id' => $supplier->id,
                'description' => 'Recebimento invalido',
                'amount' => '100.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
            ])
            ->assertSessionHasErrors(['person_id']);

        $this
            ->actingAs($user)
            ->post(route('financial-entries.store'), [
                'type' => FinancialEntry::TYPE_PAYABLE,
                'person_id' => $customer->id,
                'description' => 'Pagamento invalido',
                'amount' => '100.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
            ])
            ->assertSessionHasErrors(['person_id']);

        $this->assertDatabaseCount('financial_entries', 0);
    }

    public function test_entry_person_must_belong_to_the_authenticated_user(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Restrito', '98765432100');
        $otherSupplier = $this->personFor($otherUser, PersonType::SUPPLIER, 'Fornecedor Restrito', '99888777000166');

        foreach ([
            [FinancialEntry::TYPE_RECEIVABLE, $otherCustomer],
            [FinancialEntry::TYPE_PAYABLE, $otherSupplier],
        ] as [$type, $person]) {
            $this
                ->actingAs($user)
                ->post(route('financial-entries.store'), [
                    'type' => $type,
                    'person_id' => $person->id,
                    'description' => 'Conta invalida',
                    'amount' => '100.00',
                    'issue_date' => '2026-08-15',
                    'due_date' => '2026-08-30',
                    'status' => FinancialEntry::STATUS_PENDING,
                ])
                ->assertSessionHasErrors(['person_id']);
        }

        $this->assertDatabaseCount('financial_entries', 0);
    }

    public function test_settlement_date_is_required_when_entry_is_settled(): void
    {
        $user = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');

        $this
            ->actingAs($user)
            ->post(route('financial-entries.store'), [
                'type' => FinancialEntry::TYPE_RECEIVABLE,
                'person_id' => $customer->id,
                'description' => 'Parcela recebida',
                'amount' => '100.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_RECEIVED,
            ])
            ->assertSessionHasErrors(['settlement_date']);

        $this
            ->actingAs($user)
            ->post(route('financial-entries.store'), [
                'type' => FinancialEntry::TYPE_PAYABLE,
                'person_id' => $supplier->id,
                'description' => 'Parcela paga',
                'amount' => '100.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PAID,
            ])
            ->assertSessionHasErrors(['settlement_date']);
    }

    public function test_user_only_sees_their_own_entries_for_the_active_type(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Visivel', '12345678901');
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Visivel', '11222333000181');
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Oculto', '98765432100');

        FinancialEntry::create($this->entryData($user, $customer, FinancialEntry::TYPE_RECEIVABLE, 'Recebimento visivel'));
        FinancialEntry::create($this->entryData($user, $supplier, FinancialEntry::TYPE_PAYABLE, 'Pagamento visivel'));
        FinancialEntry::create($this->entryData($otherUser, $otherCustomer, FinancialEntry::TYPE_RECEIVABLE, 'Recebimento oculto'));

        $this
            ->actingAs($user)
            ->get(route('financial-entries.index', ['type' => FinancialEntry::TYPE_RECEIVABLE]))
            ->assertOk()
            ->assertSee('Recebimento visivel')
            ->assertDontSee('Pagamento visivel')
            ->assertDontSee('Recebimento oculto');
    }

    public function test_user_cannot_update_or_delete_another_users_entry(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Restrito', '98765432100');

        $entry = FinancialEntry::create($this->entryData($otherUser, $otherCustomer, FinancialEntry::TYPE_RECEIVABLE, 'Conta restrita'));

        $this
            ->actingAs($user)
            ->put(route('financial-entries.update', $entry), [
                'type' => FinancialEntry::TYPE_RECEIVABLE,
                'person_id' => $otherCustomer->id,
                'description' => 'Conta alterada',
                'amount' => '300.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
            ])
            ->assertNotFound();

        $this
            ->actingAs($user)
            ->delete(route('financial-entries.destroy', $entry))
            ->assertNotFound();

        $this->assertDatabaseHas('financial_entries', [
            'id' => $entry->id,
            'description' => 'Conta restrita',
        ]);
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

    private function entryData(User $user, Person $person, string $type, string $description): array
    {
        return [
            'user_id' => $user->id,
            'person_id' => $person->id,
            'type' => $type,
            'description' => $description,
            'amount' => '100.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ];
    }
}