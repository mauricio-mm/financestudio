<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ReceivableController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'person_id', 'status']);

        $baseQuery = FinancialEntry::query()
            ->where('user_id', $request->user()->id)
            ->where('type', FinancialEntry::TYPE_RECEIVABLE);

        $receivables = (clone $baseQuery)
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
            ->through(fn (FinancialEntry $receivable) => $this->receivablePayload($receivable));

        return Inertia::render('Receivables/Index', [
            'receivables' => $receivables,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'person_id' => $filters['person_id'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'customers' => $this->customerOptions($request),
            'statuses' => $this->statusOptions(),
            'summary' => $this->summaryPayload($baseQuery),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        FinancialEntry::create([
            ...$this->validatedData($request),
            'user_id' => $request->user()->id,
            'type' => FinancialEntry::TYPE_RECEIVABLE,
        ]);

        return redirect()
            ->route('receivables.index')
            ->with('flash.banner', 'Conta a receber cadastrada com sucesso.');
    }

    public function update(Request $request, FinancialEntry $receivable): RedirectResponse
    {
        $this->authorizeReceivable($request, $receivable);

        $receivable->update($this->validatedData($request));

        return redirect()
            ->route('receivables.index')
            ->with('flash.banner', 'Conta a receber atualizada com sucesso.');
    }

    public function destroy(Request $request, FinancialEntry $receivable): RedirectResponse
    {
        $this->authorizeReceivable($request, $receivable);

        $receivable->delete();

        return redirect()
            ->route('receivables.index')
            ->with('flash.banner', 'Conta a receber removida com sucesso.');
    }

    private function validatedData(Request $request): array
    {
        $request->merge([
            'amount' => $this->normalizeAmount($request->input('amount')),
        ]);

        $status = (string) $request->input('status');

        $validated = $request->validate([
            'person_id' => [
                'required',
                Rule::exists('people', 'id')->where(fn ($query) => $query
                    ->where('user_id', $request->user()->id)
                    ->whereIn('person_type_id', PersonType::query()
                        ->where('slug', PersonType::CUSTOMER)
                        ->select('id'))),
            ],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in($this->receivableStatuses())],
            'settlement_date' => [
                $status === FinancialEntry::STATUS_RECEIVED ? 'required' : 'nullable',
                'date',
                'after_or_equal:issue_date',
            ],
        ], [
            'person_id.required' => 'Selecione o cliente.',
            'person_id.exists' => 'Selecione um cliente valido.',
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
            'settlement_date.required' => 'Informe a data do recebimento.',
            'settlement_date.date' => 'Informe uma data de recebimento valida.',
            'settlement_date.after_or_equal' => 'O recebimento deve ser igual ou posterior a emissao.',
        ]);

        if ($validated['status'] !== FinancialEntry::STATUS_RECEIVED) {
            $validated['settlement_date'] = null;
        }

        return $validated;
    }

    private function authorizeReceivable(Request $request, FinancialEntry $receivable): void
    {
        abort_unless(
            (int) $receivable->user_id === (int) $request->user()->id
            && $receivable->type === FinancialEntry::TYPE_RECEIVABLE,
            404
        );
    }

    private function customerOptions(Request $request): array
    {
        return Person::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('personType', fn ($query) => $query->where('slug', PersonType::CUSTOMER))
            ->orderBy('name')
            ->get(['id', 'name', 'document'])
            ->map(fn (Person $person) => [
                'value' => $person->id,
                'label' => $person->name,
                'document' => $this->formatDocument($person->document),
            ])
            ->all();
    }

    private function summaryPayload($query): array
    {
        return [
            'pending' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_PENDING)->sum('amount')),
            'received' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_RECEIVED)->sum('amount')),
            'overdue' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_OVERDUE)->sum('amount')),
            'cancelled' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_CANCELLED)->sum('amount')),
        ];
    }

    private function receivablePayload(FinancialEntry $receivable): array
    {
        return [
            'id' => $receivable->id,
            'person_id' => $receivable->person_id,
            'customer_name' => $receivable->person?->name,
            'description' => $receivable->description,
            'amount' => (string) $receivable->amount,
            'amount_formatted' => $this->money($receivable->amount),
            'issue_date' => $receivable->issue_date?->format('Y-m-d'),
            'issue_date_formatted' => $receivable->issue_date?->format('d/m/Y'),
            'due_date' => $receivable->due_date?->format('Y-m-d'),
            'due_date_formatted' => $receivable->due_date?->format('d/m/Y'),
            'status' => $receivable->status,
            'status_label' => $this->statusLabels()[$receivable->status] ?? $receivable->status,
            'settlement_date' => $receivable->settlement_date?->format('Y-m-d'),
            'settlement_date_formatted' => $receivable->settlement_date?->format('d/m/Y'),
        ];
    }

    private function statusOptions(): array
    {
        return collect($this->statusLabels())
            ->map(fn (string $label, string $value) => [
                'value' => $value,
                'label' => $label,
            ])
            ->values()
            ->all();
    }

    private function statusLabels(): array
    {
        return [
            FinancialEntry::STATUS_PENDING => 'Pendente',
            FinancialEntry::STATUS_RECEIVED => 'Recebido',
            FinancialEntry::STATUS_OVERDUE => 'Vencido',
            FinancialEntry::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    private function receivableStatuses(): array
    {
        return array_keys($this->statusLabels());
    }

    private function normalizeAmount(mixed $value): string
    {
        $value = trim((string) $value);

        if (str_contains($value, ',') && str_contains($value, '.')) {
            $value = str_replace('.', '', $value);
        }

        return str_replace(',', '.', $value);
    }

    private function money(mixed $value): string
    {
        return 'R$ '.number_format((float) $value, 2, ',', '.');
    }

    private function formatDocument(string $document): string
    {
        return match (strlen($document)) {
            11 => preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document) ?? $document,
            14 => preg_replace('/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document) ?? $document,
            default => $document,
        };
    }
}