<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinancialEntryController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['type', 'search', 'person_id', 'status']);
        $activeType = $this->activeType($filters['type'] ?? null);

        $baseQuery = FinancialEntry::query()
            ->where('user_id', $request->user()->id)
            ->where('type', $activeType);

        $entries = (clone $baseQuery)
            ->with('person')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('description', 'like', "%{$search}%")
                        ->orWhereHas('person', fn ($query) => $query->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['person_id'] ?? null, fn ($query, string $personId) => $query->where('person_id', $personId))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (FinancialEntry $entry) => $this->entryPayload($entry));

        return Inertia::render('FinancialEntries/Index', [
            'entries' => $entries,
            'activeType' => $activeType,
            'filters' => [
                'type' => $activeType,
                'search' => $filters['search'] ?? '',
                'person_id' => $filters['person_id'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'people' => $this->personOptions($request, $activeType),
            'statuses' => $this->statusOptions($activeType),
            'typeOptions' => $this->typeOptions(),
            'summary' => $this->summaryPayload($baseQuery, $activeType),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        FinancialEntry::create([
            ...$this->validatedData($request),
            'user_id' => $request->user()->id,
        ]);

        return redirect()
            ->route('financial-entries.index', ['type' => $this->activeType($request->input('type'))])
            ->with('flash.banner', 'Conta cadastrada com sucesso.');
    }

    public function update(Request $request, FinancialEntry $financialEntry): RedirectResponse
    {
        $this->authorizeEntry($request, $financialEntry);

        $financialEntry->update($this->validatedData($request));

        return redirect()
            ->route('financial-entries.index', ['type' => $this->activeType($request->input('type'))])
            ->with('flash.banner', 'Conta atualizada com sucesso.');
    }

    public function destroy(Request $request, FinancialEntry $financialEntry): RedirectResponse
    {
        $this->authorizeEntry($request, $financialEntry);
        $type = $financialEntry->type;

        $financialEntry->delete();

        return redirect()
            ->route('financial-entries.index', ['type' => $this->activeType($type)])
            ->with('flash.banner', 'Conta removida com sucesso.');
    }

    private function validatedData(Request $request): array
    {
        $request->merge([
            'amount' => Format::decimal($request->input('amount')),
        ]);

        $type = $this->activeType($request->input('type'));
        $config = $this->typeConfig($type);
        $status = (string) $request->input('status');

        $validated = $request->validate([
            'type' => ['required', Rule::in(FinancialEntry::types())],
            'person_id' => [
                'required',
                Rule::exists('people', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->whereIn('person_type_id', PersonType::query()
                        ->where('slug', $config['person_type_slug'])
                        ->select('id'))),
            ],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in(array_keys($this->statusLabels($type)))],
            'settlement_date' => [
                $status === $config['settled_status'] ? 'required' : 'nullable',
                'date',
                'after_or_equal:issue_date',
            ],
        ], [
            'type.required' => 'Selecione o tipo da conta.',
            'type.in' => 'Selecione um tipo de conta valido.',
            'person_id.required' => "Selecione o {$config['person_label_lower']}.",
            'person_id.exists' => "Selecione um {$config['person_label_lower']} valido.",
            'description.required' => 'Informe a descricao.',
            'amount.required' => 'Informe o valor.',
            'amount.numeric' => 'Informe um valor valido.',
            'amount.min' => 'O valor deve ser maior que zero.',
            'issue_date.required' => 'Informe a data de emissao.',
            'issue_date.date' => 'Informe uma data de emissao valida.',
            'due_date.required' => 'Informe a data de vencimento.',
            'due_date.date' => 'Informe uma data de vencimento valida.',
            'due_date.after_or_equal' => 'O vencimento deve ser igual ou posterior a emissao.',
            'status.required' => 'Selecione o status.',
            'status.in' => 'Selecione um status valido.',
            'settlement_date.required' => "Informe a data do {$config['settlement_label_lower']}.",
            'settlement_date.date' => "Informe uma data de {$config['settlement_label_lower']} valida.",
            'settlement_date.after_or_equal' => "O {$config['settlement_label_lower']} deve ser igual ou posterior a emissao.",
        ]);

        if ($validated['status'] !== $config['settled_status']) {
            $validated['settlement_date'] = null;
        }

        return $validated;
    }

    private function authorizeEntry(Request $request, FinancialEntry $entry): void
    {
        abort_unless((int) $entry->user_id === (int) $request->user()->id, 404);
    }

    private function personOptions(Request $request, string $type): array
    {
        $config = $this->typeConfig($type);

        return Person::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('personType', fn ($query) => $query->where('slug', $config['person_type_slug']))
            ->orderBy('name')
            ->get(['id', 'name', 'document'])
            ->map(fn (Person $person) => [
                'value' => $person->id,
                'label' => $person->name,
                'document' => Format::document($person->document),
            ])
            ->all();
    }

    private function summaryPayload($query, string $type): array
    {
        $config = $this->typeConfig($type);

        return [
            'pending' => Format::money((clone $query)->where('status', FinancialEntry::STATUS_PENDING)->sum('amount')),
            'settled' => Format::money((clone $query)->where('status', $config['settled_status'])->sum('amount')),
            'overdue' => Format::money((clone $query)->where('status', FinancialEntry::STATUS_OVERDUE)->sum('amount')),
            'cancelled' => Format::money((clone $query)->where('status', FinancialEntry::STATUS_CANCELLED)->sum('amount')),
        ];
    }

    private function entryPayload(FinancialEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'type' => $entry->type,
            'person_id' => $entry->person_id,
            'person_name' => $entry->person?->name,
            'description' => $entry->description,
            'amount' => (string) $entry->amount,
            'amount_formatted' => Format::money($entry->amount),
            'issue_date' => $entry->issue_date?->format('Y-m-d'),
            'issue_date_formatted' => $entry->issue_date?->format('d/m/Y'),
            'due_date' => $entry->due_date?->format('Y-m-d'),
            'due_date_formatted' => $entry->due_date?->format('d/m/Y'),
            'status' => $entry->status,
            'status_label' => $this->statusLabels($entry->type)[$entry->status] ?? $entry->status,
            'settlement_date' => $entry->settlement_date?->format('Y-m-d'),
            'settlement_date_formatted' => $entry->settlement_date?->format('d/m/Y'),
        ];
    }

    private function statusOptions(string $type): array
    {
        return collect($this->statusLabels($type))
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    private function statusLabels(string $type): array
    {
        $config = $this->typeConfig($type);

        return [
            FinancialEntry::STATUS_PENDING => 'Pendente',
            $config['settled_status'] => $config['settled_status_label'],
            FinancialEntry::STATUS_OVERDUE => 'Vencido',
            FinancialEntry::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    private function typeOptions(): array
    {
        return [
            ['value' => FinancialEntry::TYPE_RECEIVABLE, 'label' => 'A Receber'],
            ['value' => FinancialEntry::TYPE_PAYABLE, 'label' => 'A Pagar'],
        ];
    }

    private function activeType(mixed $type): string
    {
        $type = (string) $type;

        return in_array($type, FinancialEntry::types(), true)
            ? $type
            : FinancialEntry::TYPE_RECEIVABLE;
    }

    private function typeConfig(string $type): array
    {
        if ($type === FinancialEntry::TYPE_PAYABLE) {
            return [
                'person_type_slug' => PersonType::SUPPLIER,
                'person_label' => 'Fornecedor',
                'person_label_lower' => 'fornecedor',
                'settled_status' => FinancialEntry::STATUS_PAID,
                'settled_status_label' => 'Pago',
                'settlement_label' => 'Pagamento',
                'settlement_label_lower' => 'pagamento',
            ];
        }

        return [
            'person_type_slug' => PersonType::CUSTOMER,
            'person_label' => 'Cliente',
            'person_label_lower' => 'cliente',
            'settled_status' => FinancialEntry::STATUS_RECEIVED,
            'settled_status_label' => 'Recebido',
            'settlement_label' => 'Recebimento',
            'settlement_label_lower' => 'recebimento',
        ];
    }
}