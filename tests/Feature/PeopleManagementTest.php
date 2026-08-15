<?php

namespace Tests\Feature;

use App\Models\Person;
use App\Models\PersonType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PeopleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_people_are_created_for_the_authenticated_user_with_normalized_document_and_phone(): void
    {
        $user = User::factory()->create();
        $type = PersonType::where('slug', PersonType::CUSTOMER)->firstOrFail();

        $this
            ->actingAs($user)
            ->post(route('people.store'), [
                'name' => 'Cliente Atlas',
                'document' => '123.456.789-01',
                'email' => 'atlas@example.com',
                'phone' => '(11) 99999-0000',
                'person_type_id' => $type->id,
            ])
            ->assertRedirect(route('people.index'));

        $this->assertDatabaseHas('people', [
            'user_id' => $user->id,
            'person_type_id' => $type->id,
            'name' => 'Cliente Atlas',
            'document' => '12345678901',
            'phone' => '11999990000',
        ]);
    }

    public function test_person_document_and_phone_must_have_valid_digit_lengths(): void
    {
        $user = User::factory()->create();
        $type = PersonType::where('slug', PersonType::CUSTOMER)->firstOrFail();

        $this
            ->actingAs($user)
            ->post(route('people.store'), [
                'name' => 'Cliente Invalido',
                'document' => '12345',
                'phone' => '(11) 999',
                'person_type_id' => $type->id,
            ])
            ->assertSessionHasErrors(['document', 'phone']);
    }

    public function test_user_only_sees_their_own_people(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $type = PersonType::where('slug', PersonType::CUSTOMER)->firstOrFail();

        Person::create([
            'user_id' => $user->id,
            'person_type_id' => $type->id,
            'name' => 'Cliente Visivel',
            'document' => '12345678901',
        ]);

        Person::create([
            'user_id' => $otherUser->id,
            'person_type_id' => $type->id,
            'name' => 'Cliente De Outro Usuario',
            'document' => '98765432100',
        ]);

        $this
            ->actingAs($user)
            ->get(route('people.index'))
            ->assertOk()
            ->assertSee('Cliente Visivel')
            ->assertDontSee('Cliente De Outro Usuario');
    }

    public function test_user_cannot_update_or_delete_another_users_person(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $type = PersonType::where('slug', PersonType::SUPPLIER)->firstOrFail();

        $person = Person::create([
            'user_id' => $otherUser->id,
            'person_type_id' => $type->id,
            'name' => 'Fornecedor Restrito',
            'document' => '11222333000181',
        ]);

        $this
            ->actingAs($user)
            ->put(route('people.update', $person), [
                'name' => 'Fornecedor Alterado',
                'document' => '11222333000181',
                'person_type_id' => $type->id,
            ])
            ->assertNotFound();

        $this
            ->actingAs($user)
            ->delete(route('people.destroy', $person))
            ->assertNotFound();

        $this->assertDatabaseHas('people', [
            'id' => $person->id,
            'name' => 'Fornecedor Restrito',
        ]);
    }
}