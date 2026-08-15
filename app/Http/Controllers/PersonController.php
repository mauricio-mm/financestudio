<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'person_type_id']);

        $people = Person::query()
            ->with('personType')
            ->where('user_id', $request->user()->id)
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $digitsSearch = $this->onlyDigits($search);

                $query->where(function ($query) use ($search, $digitsSearch) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");

                    if ($digitsSearch !== '') {
                        $query
                            ->orWhere('document', 'like', "%{$digitsSearch}%")
                            ->orWhere('phone', 'like', "%{$digitsSearch}%");
                    }
                });
            })
            ->when($filters['person_type_id'] ?? null, fn ($query, string $personTypeId) => $query->where('person_type_id', $personTypeId))
            ->latest()
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Person $person) => $this->personPayload($person));

        return Inertia::render('People/Index', [
            'people' => $people,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'person_type_id' => $filters['person_type_id'] ?? '',
            ],
            'types' => PersonType::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Person::create([
            ...$this->validatedData($request),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('people.index')
            ->with('flash.banner', 'Pessoa/empresa cadastrada com sucesso.');
    }

    public function update(Request $request, Person $person): RedirectResponse
    {
        $this->authorizePerson($request, $person);

        $person->update($this->validatedData($request, $person));

        return redirect()
            ->route('people.index')
            ->with('flash.banner', 'Pessoa/empresa atualizada com sucesso.');
    }

    public function destroy(Request $request, Person $person): RedirectResponse
    {
        $this->authorizePerson($request, $person);

        $person->delete();

        return redirect()
            ->route('people.index')
            ->with('flash.banner', 'Pessoa/empresa removida com sucesso.');
    }

    private function validatedData(Request $request, ?Person $person = null): array
    {
        $phone = $this->onlyDigits((string) $request->input('phone'));

        $request->merge([
            'document' => $this->onlyDigits((string) $request->input('document')),
            'phone' => $phone !== '' ? $phone : null,
        ]);

        $documentRule = Rule::unique('people', 'document')
            ->where(fn ($query) => $query->where('user_id', $request->user()->id));

        if ($person) {
            $documentRule->ignore($person->id);
        }

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'document' => ['required', 'regex:/^(\d{11}|\d{14})$/', $documentRule],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'regex:/^\d{10,11}$/'],
            'person_type_id' => ['required', Rule::exists('person_types', 'id')],
        ], [
            'name.required' => 'Informe o nome ou razao social.',
            'document.required' => 'Informe o CPF ou CNPJ.',
            'document.regex' => 'Informe um CPF com 11 digitos ou CNPJ com 14 digitos.',
            'document.unique' => 'Voce ja cadastrou uma pessoa/empresa com este CPF ou CNPJ.',
            'email.email' => 'Informe um e-mail valido.',
            'phone.regex' => 'Informe um telefone com DDD e 10 ou 11 digitos.',
            'person_type_id.required' => 'Selecione o tipo.',
            'person_type_id.exists' => 'Selecione um tipo valido.',
        ]);
    }

    private function authorizePerson(Request $request, Person $person): void
    {
        abort_unless((int) $person->user_id === (int) $request->user()->id, 404);
    }

    private function personPayload(Person $person): array
    {
        return [
            'id' => $person->id,
            'name' => $person->name,
            'document' => $this->formatDocument($person->document),
            'document_digits' => $person->document,
            'email' => $person->email,
            'phone' => $this->formatPhone($person->phone),
            'phone_digits' => $person->phone,
            'person_type_id' => $person->person_type_id,
            'type_label' => $person->personType?->name,
            'created_at' => $person->created_at?->format('d/m/Y'),
        ];
    }

    private function onlyDigits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }

    private function formatDocument(string $document): string
    {
        return match (strlen($document)) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document) ?? $document,
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document) ?? $document,
            default => $document,
        };
    }

    private function formatPhone(?string $phone): ?string
    {
        if ($phone === null || $phone === '') {
            return null;
        }

        return match (strlen($phone)) {
            10 => preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $phone) ?? $phone,
            11 => preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $phone) ?? $phone,
            default => $phone,
        };
    }
}