<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReceivableManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_receivables_are_created_for_the_authenticated_users_customer(): void
    {
        $user = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');

        $this
            ->actingAs($user)
            ->post(route('receivables.store'), [
                'person_id' => $customer->id,
                'description' => 'Mensalidade de servico',
                'amount' => '1500,75',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
                'settlement_date' => null,
            ])
            ->assertRedirect(route('receivables.index'));

        $receivable = FinancialEntry::query()->firstOrFail();

        $this->assertSame($user->id, $receivable->user_id);
        $this->assertSame($customer->id, $receivable->person_id);
        $this->assertSame(FinancialEntry::TYPE_RECEIVABLE, $receivable->type);
        $this->assertSame('Mensalidade de servico', $receivable->description);
        $this->assertSame('1500.75', $receivable->amount);
        $this->assertNull($receivable->settlement_date);
    }

    public function test_receivable_must_belong_to_an_authenticated_users_customer(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Restrito', '98765432100');

        foreach ([$supplier, $otherCustomer] as $person) {
            $this
                ->actingAs($user)
                ->post(route('receivables.store'), [
                    'person_id' => $person->id,
                    'description' => 'Titulo invalido',
                    'amount' => '100.00',
                    'issue_date' => '2026-08-15',
                    'due_date' => '2026-08-30',
                    'status' => FinancialEntry::STATUS_PENDING,
                ])
                ->assertSessionHasErrors(['person_id']);
        }

        $this->assertDatabaseCount('financial_entries', 0);
    }

    public function test_receivable_requires_settlement_date_when_received(): void
    {
        $user = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');

        $this
            ->actingAs($user)
            ->post(route('receivables.store'), [
                'person_id' => $customer->id,
                'description' => 'Parcela recebida',
                'amount' => '100.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_RECEIVED,
            ])
            ->assertSessionHasErrors(['settlement_date']);
    }

    public function test_user_only_sees_their_own_receivables(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Visivel', '12345678901');
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Oculto', '98765432100');

        FinancialEntry::create([
            'user_id' => $user->id,
            'person_id' => $customer->id,
            'type' => FinancialEntry::TYPE_RECEIVABLE,
            'description' => 'Recebivel visivel',
            'amount' => '100.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ]);

        FinancialEntry::create([
            'user_id' => $otherUser->id,
            'person_id' => $otherCustomer->id,
            'type' => FinancialEntry::TYPE_RECEIVABLE,
            'description' => 'Recebivel oculto',
            'amount' => '200.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ]);

        $this
            ->actingAs($user)
            ->get(route('receivables.index'))
            ->assertOk()
            ->assertSee('Recebivel visivel')
            ->assertDontSee('Recebivel oculto');
    }

    public function test_user_cannot_update_or_delete_another_users_receivable(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherCustomer = $this->personFor($otherUser, PersonType::CUSTOMER, 'Cliente Restrito', '98765432100');

        $receivable = FinancialEntry::create([
            'user_id' => $otherUser->id,
            'person_id' => $otherCustomer->id,
            'type' => FinancialEntry::TYPE_RECEIVABLE,
            'description' => 'Recebivel restrito',
            'amount' => '200.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ]);

        $this
            ->actingAs($user)
            ->put(route('receivables.update', $receivable), [
                'person_id' => $otherCustomer->id,
                'description' => 'Recebivel alterado',
                'amount' => '300.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
            ])
            ->assertNotFound();

        $this
            ->actingAs($user)
            ->delete(route('receivables.destroy', $receivable))
            ->assertNotFound();

        $this->assertDatabaseHas('financial_entries', [
            'id' => $receivable->id,
            'description' => 'Recebivel restrito',
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
}