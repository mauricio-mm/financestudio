<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\PersonType;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PersonController extends Controller
{
    private const PAGE_SIZE = 20;

    public function index(Request $request): Response
    {
        return Inertia::render('People/Index', [
            'people' => $this->peoplePage($request),
            'filters' => [
                'search' => '',
                'person_type_id' => '',
            ],
            'types' => PersonType::options(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($this->peoplePage($request));
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

    private function peoplePage(Request $request): array
    {
        $people = Person::query()
            ->with('personType')
            ->where('user_id', $request->user()->id)
            ->latest('id')
            ->paginate(self::PAGE_SIZE)
            ->through(fn (Person $person) => $this->personPayload($person));

        return $this->paginatorPayload($people);
    }

    private function paginatorPayload(LengthAwarePaginator $paginator): array
    {
        return [
            'data' => $paginator->items(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more' => $paginator->hasMorePages(),
        ];
    }

    private function validatedData(Request $request, ?Person $person = null): array
    {
        $phone = Format::onlyDigits((string) $request->input('phone'));

        $request->merge([
            'document' => Format::onlyDigits((string) $request->input('document')),
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
            'document' => Format::document($person->document),
            'document_digits' => $person->document,
            'email' => $person->email,
            'phone' => Format::phone($person->phone),
            'phone_digits' => $person->phone,
            'person_type_id' => $person->person_type_id,
            'type_label' => $person->personType?->name,
            'type_slug' => $person->personType?->slug,
            'created_at' => $person->created_at?->format('d/m/Y'),
        ];
    }
}