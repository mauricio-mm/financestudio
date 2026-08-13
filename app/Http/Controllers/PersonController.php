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
                $documentSearch = $this->onlyDigits($search);

                $query->where(function ($query) use ($search, $documentSearch) {
                    $query
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");

                    if ($documentSearch !== '') {
                        $query->orWhere('document', 'like', "%{$documentSearch}%");
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

    public function create(): Response
    {
        return Inertia::render('People/Create', [
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

    public function edit(Request $request, Person $person): Response
    {
        $this->authorizePerson($request, $person);

        return Inertia::render('People/Edit', [
            'person' => $this->personPayload($person->load('personType')),
            'types' => PersonType::options(),
        ]);
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
        $request->merge([
            'document' => $this->onlyDigits((string) $request->input('document')),
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
            'phone' => ['nullable', 'string', 'max:30'],
            'person_type_id' => ['required', Rule::exists('person_types', 'id')],
        ], [
            'name.required' => 'Informe o nome ou razao social.',
            'document.required' => 'Informe o CPF ou CNPJ.',
            'document.regex' => 'Informe um CPF com 11 digitos ou CNPJ com 14 digitos.',
            'document.unique' => 'Voce ja cadastrou uma pessoa/empresa com este CPF ou CNPJ.',
            'email.email' => 'Informe um e-mail valido.',
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
            'phone' => $person->phone,
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
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document),
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document),
            default => $document,
        };
    }
}
