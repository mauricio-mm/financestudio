<?php

namespace Tests\Feature;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayableManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_payables_are_created_for_the_authenticated_users_supplier(): void
    {
        $user = User::factory()->create();
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');

        $this
            ->actingAs($user)
            ->post(route('payables.store'), [
                'person_id' => $supplier->id,
                'description' => 'Assinatura de software',
                'amount' => '850,40',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
                'settlement_date' => null,
            ])
            ->assertRedirect(route('payables.index'));

        $payable = FinancialEntry::query()->firstOrFail();

        $this->assertSame($user->id, $payable->user_id);
        $this->assertSame($supplier->id, $payable->person_id);
        $this->assertSame(FinancialEntry::TYPE_PAYABLE, $payable->type);
        $this->assertSame('Assinatura de software', $payable->description);
        $this->assertSame('850.40', $payable->amount);
        $this->assertNull($payable->settlement_date);
    }

    public function test_payable_must_belong_to_an_authenticated_users_supplier(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $customer = $this->personFor($user, PersonType::CUSTOMER, 'Cliente Atlas', '12345678901');
        $otherSupplier = $this->personFor($otherUser, PersonType::SUPPLIER, 'Fornecedor Restrito', '99888777000166');

        foreach ([$customer, $otherSupplier] as $person) {
            $this
                ->actingAs($user)
                ->post(route('payables.store'), [
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

    public function test_payable_requires_settlement_date_when_paid(): void
    {
        $user = User::factory()->create();
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Atlas', '11222333000181');

        $this
            ->actingAs($user)
            ->post(route('payables.store'), [
                'person_id' => $supplier->id,
                'description' => 'Parcela paga',
                'amount' => '100.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PAID,
            ])
            ->assertSessionHasErrors(['settlement_date']);
    }

    public function test_user_only_sees_their_own_payables(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $supplier = $this->personFor($user, PersonType::SUPPLIER, 'Fornecedor Visivel', '11222333000181');
        $otherSupplier = $this->personFor($otherUser, PersonType::SUPPLIER, 'Fornecedor Oculto', '99888777000166');

        FinancialEntry::create([
            'user_id' => $user->id,
            'person_id' => $supplier->id,
            'type' => FinancialEntry::TYPE_PAYABLE,
            'description' => 'Pagamento visivel',
            'amount' => '100.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ]);

        FinancialEntry::create([
            'user_id' => $otherUser->id,
            'person_id' => $otherSupplier->id,
            'type' => FinancialEntry::TYPE_PAYABLE,
            'description' => 'Pagamento oculto',
            'amount' => '200.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ]);

        $this
            ->actingAs($user)
            ->get(route('payables.index'))
            ->assertOk()
            ->assertSee('Pagamento visivel')
            ->assertDontSee('Pagamento oculto');
    }

    public function test_user_cannot_update_or_delete_another_users_payable(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $otherSupplier = $this->personFor($otherUser, PersonType::SUPPLIER, 'Fornecedor Restrito', '99888777000166');

        $payable = FinancialEntry::create([
            'user_id' => $otherUser->id,
            'person_id' => $otherSupplier->id,
            'type' => FinancialEntry::TYPE_PAYABLE,
            'description' => 'Pagamento restrito',
            'amount' => '200.00',
            'issue_date' => '2026-08-15',
            'due_date' => '2026-08-30',
            'status' => FinancialEntry::STATUS_PENDING,
        ]);

        $this
            ->actingAs($user)
            ->put(route('payables.update', $payable), [
                'person_id' => $otherSupplier->id,
                'description' => 'Pagamento alterado',
                'amount' => '300.00',
                'issue_date' => '2026-08-15',
                'due_date' => '2026-08-30',
                'status' => FinancialEntry::STATUS_PENDING,
            ])
            ->assertNotFound();

        $this
            ->actingAs($user)
            ->delete(route('payables.destroy', $payable))
            ->assertNotFound();

        $this->assertDatabaseHas('financial_entries', [
            'id' => $payable->id,
            'description' => 'Pagamento restrito',
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