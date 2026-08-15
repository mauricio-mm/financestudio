<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\Person;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class FinancialReportController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'person_id' => [
                'nullable',
                Rule::exists('people', 'id')->where(fn ($query) => $query->where('user_id', $request->user()->id)),
            ],
            'type' => ['nullable', Rule::in(FinancialEntry::types())],
            'status' => ['nullable', Rule::in(FinancialEntry::statuses())],
        ]);

        $query = $this->filteredQuery($request->user()->id, $filters);

        $report = (clone $query)
            ->with(['person.personType'])
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(15)
            ->withQueryString()
            ->through(fn (FinancialEntry $entry) => $this->entryPayload($entry));

        return Inertia::render('Reports/Financial', [
            'report' => $report,
            'filters' => [
                'date_from' => $filters['date_from'] ?? '',
                'date_to' => $filters['date_to'] ?? '',
                'person_id' => $filters['person_id'] ?? '',
                'type' => $filters['type'] ?? '',
                'status' => $filters['status'] ?? '',
            ],
            'people' => $this->personOptions($request->user()->id),
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => $this->statusOptions(),
            'summary' => $this->summaryPayload($query),
        ]);
    }

    private function filteredQuery(int $userId, array $filters)
    {
        return FinancialEntry::query()
            ->where('user_id', $userId)
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('due_date', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('due_date', '<=', $date))
            ->when($filters['person_id'] ?? null, fn ($query, string $personId) => $query->where('person_id', $personId))
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status));
    }

    private function summaryPayload($query): array
    {
        $receivableTotal = (float) (clone $query)->where('type', FinancialEntry::TYPE_RECEIVABLE)->sum('amount');
        $payableTotal = (float) (clone $query)->where('type', FinancialEntry::TYPE_PAYABLE)->sum('amount');

        return [
            'count' => (clone $query)->count(),
            'total_amount' => (float) (clone $query)->sum('amount'),
            'receivable_total' => $receivableTotal,
            'payable_total' => $payableTotal,
            'received_total' => (float) (clone $query)->where('status', FinancialEntry::STATUS_RECEIVED)->sum('amount'),
            'paid_total' => (float) (clone $query)->where('status', FinancialEntry::STATUS_PAID)->sum('amount'),
            'pending_total' => (float) (clone $query)->where('status', FinancialEntry::STATUS_PENDING)->sum('amount'),
            'overdue_total' => (float) (clone $query)->where('status', FinancialEntry::STATUS_OVERDUE)->sum('amount'),
            'cancelled_total' => (float) (clone $query)->where('status', FinancialEntry::STATUS_CANCELLED)->sum('amount'),
            'balance' => $receivableTotal - $payableTotal,
        ];
    }

    private function entryPayload(FinancialEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'type' => $entry->type,
            'type_label' => $this->typeLabel($entry->type),
            'person_id' => $entry->person_id,
            'person_name' => $entry->person?->name,
            'person_type_label' => $entry->person?->personType?->name,
            'description' => $entry->description,
            'amount' => (float) $entry->amount,
            'amount_formatted' => $this->money($entry->amount),
            'issue_date_formatted' => $entry->issue_date?->format('d/m/Y'),
            'due_date_formatted' => $entry->due_date?->format('d/m/Y'),
            'status' => $entry->status,
            'status_label' => $this->statusLabel($entry->status, $entry->type),
            'settlement_date_formatted' => $entry->settlement_date?->format('d/m/Y'),
        ];
    }

    private function personOptions(int $userId): array
    {
        return Person::query()
            ->with('personType')
            ->where('user_id', $userId)
            ->orderBy('name')
            ->get(['id', 'person_type_id', 'name', 'document'])
            ->map(fn (Person $person) => [
                'value' => $person->id,
                'label' => $person->name,
                'type_label' => $person->personType?->name,
                'type_slug' => $person->personType?->slug,
                'document' => $this->formatDocument($person->document),
            ])
            ->all();
    }

    private function typeOptions(): array
    {
        return [
            ['value' => FinancialEntry::TYPE_RECEIVABLE, 'label' => 'A Receber'],
            ['value' => FinancialEntry::TYPE_PAYABLE, 'label' => 'A Pagar'],
        ];
    }

    private function statusOptions(): array
    {
        return [
            ['value' => FinancialEntry::STATUS_PENDING, 'label' => 'Pendente'],
            ['value' => FinancialEntry::STATUS_RECEIVED, 'label' => 'Recebido'],
            ['value' => FinancialEntry::STATUS_PAID, 'label' => 'Pago'],
            ['value' => FinancialEntry::STATUS_OVERDUE, 'label' => 'Vencido'],
            ['value' => FinancialEntry::STATUS_CANCELLED, 'label' => 'Cancelado'],
        ];
    }

    private function typeLabel(string $type): string
    {
        return $type === FinancialEntry::TYPE_PAYABLE ? 'A Pagar' : 'A Receber';
    }

    private function statusLabel(string $status, string $type): string
    {
        return match ($status) {
            FinancialEntry::STATUS_PENDING => 'Pendente',
            FinancialEntry::STATUS_RECEIVED => 'Recebido',
            FinancialEntry::STATUS_PAID => 'Pago',
            FinancialEntry::STATUS_OVERDUE => 'Vencido',
            FinancialEntry::STATUS_CANCELLED => 'Cancelado',
            default => $status,
        };
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