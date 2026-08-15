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

class PayableController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->only(['search', 'person_id', 'status']);

        $baseQuery = FinancialEntry::query()
            ->where('user_id', $request->user()->id)
            ->where('type', FinancialEntry::TYPE_PAYABLE);

        $payables = (clone $baseQuery)
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
            ->through(fn (FinancialEntry $payable) => $this->payablePayload($payable));

        return Inertia::render('Payables/Index', [
            'payables' => $payables,
            'filters' => [
                'search' => $filters['search'] ?? '',
                'person_id' => $filters['person_id'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'suppliers' => $this->supplierOptions($request),
            'statuses' => $this->statusOptions(),
            'summary' => $this->summaryPayload($baseQuery),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        FinancialEntry::create([
            ...$this->validatedData($request),
            'user_id' => $request->user()->id,
            'type' => FinancialEntry::TYPE_PAYABLE,
        ]);

        return redirect()
            ->route('payables.index')
            ->with('flash.banner', 'Conta a pagar cadastrada com sucesso.');
    }

    public function update(Request $request, FinancialEntry $payable): RedirectResponse
    {
        $this->authorizePayable($request, $payable);

        $payable->update($this->validatedData($request));

        return redirect()
            ->route('payables.index')
            ->with('flash.banner', 'Conta a pagar atualizada com sucesso.');
    }

    public function destroy(Request $request, FinancialEntry $payable): RedirectResponse
    {
        $this->authorizePayable($request, $payable);

        $payable->delete();

        return redirect()
            ->route('payables.index')
            ->with('flash.banner', 'Conta a pagar removida com sucesso.');
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
                        ->where('slug', PersonType::SUPPLIER)
                        ->select('id'))),
            ],
            'description' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:999999999999.99'],
            'issue_date' => ['required', 'date'],
            'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', Rule::in($this->payableStatuses())],
            'settlement_date' => [
                $status === FinancialEntry::STATUS_PAID ? 'required' : 'nullable',
                'date',
                'after_or_equal:issue_date',
            ],
        ], [
            'person_id.required' => 'Selecione o fornecedor.',
            'person_id.exists' => 'Selecione um fornecedor valido.',
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
            'settlement_date.required' => 'Informe a data do pagamento.',
            'settlement_date.date' => 'Informe uma data de pagamento valida.',
            'settlement_date.after_or_equal' => 'O pagamento deve ser igual ou posterior a emissao.',
        ]);

        if ($validated['status'] !== FinancialEntry::STATUS_PAID) {
            $validated['settlement_date'] = null;
        }

        return $validated;
    }

    private function authorizePayable(Request $request, FinancialEntry $payable): void
    {
        abort_unless(
            (int) $payable->user_id === (int) $request->user()->id
            && $payable->type === FinancialEntry::TYPE_PAYABLE,
            404
        );
    }

    private function supplierOptions(Request $request): array
    {
        return Person::query()
            ->where('user_id', $request->user()->id)
            ->whereHas('personType', fn ($query) => $query->where('slug', PersonType::SUPPLIER))
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
            'paid' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_PAID)->sum('amount')),
            'overdue' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_OVERDUE)->sum('amount')),
            'cancelled' => $this->money((clone $query)->where('status', FinancialEntry::STATUS_CANCELLED)->sum('amount')),
        ];
    }

    private function payablePayload(FinancialEntry $payable): array
    {
        return [
            'id' => $payable->id,
            'person_id' => $payable->person_id,
            'supplier_name' => $payable->person?->name,
            'description' => $payable->description,
            'amount' => (string) $payable->amount,
            'amount_formatted' => $this->money($payable->amount),
            'issue_date' => $payable->issue_date?->format('Y-m-d'),
            'issue_date_formatted' => $payable->issue_date?->format('d/m/Y'),
            'due_date' => $payable->due_date?->format('Y-m-d'),
            'due_date_formatted' => $payable->due_date?->format('d/m/Y'),
            'status' => $payable->status,
            'status_label' => $this->statusLabels()[$payable->status] ?? $payable->status,
            'settlement_date' => $payable->settlement_date?->format('Y-m-d'),
            'settlement_date_formatted' => $payable->settlement_date?->format('d/m/Y'),
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
            FinancialEntry::STATUS_PAID => 'Pago',
            FinancialEntry::STATUS_OVERDUE => 'Vencido',
            FinancialEntry::STATUS_CANCELLED => 'Cancelado',
        ];
    }

    private function payableStatuses(): array
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