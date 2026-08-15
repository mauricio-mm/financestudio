<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\PersonType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Inertia\Inertia;
use Inertia\Response;

class FinancialReportController extends Controller
{
    private const PAGE_SIZE = 20;

    public function __invoke(Request $request): Response
    {
        return Inertia::render('Reports/Financial', [
            'initialEntries' => $this->entryPage($request),
            'filters' => [
                'date_from' => '',
                'date_to' => '',
                'person_type' => '',
                'person_search' => '',
                'type' => '',
                'status' => '',
            ],
            'personTypeOptions' => $this->personTypeOptions(),
            'typeOptions' => $this->typeOptions(),
            'statusOptions' => $this->statusOptions(),
        ]);
    }

    public function entries(Request $request): JsonResponse
    {
        $request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        return response()->json($this->entryPage($request));
    }

    private function entryPage(Request $request): array
    {
        $entries = FinancialEntry::query()
            ->with(['person.personType'])
            ->where('user_id', $request->user()->id)
            ->orderBy('due_date')
            ->latest('id')
            ->paginate(self::PAGE_SIZE)
            ->through(fn (FinancialEntry $entry) => $this->entryPayload($entry));

        return $this->paginatorPayload($entries);
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

    private function entryPayload(FinancialEntry $entry): array
    {
        return [
            'id' => $entry->id,
            'type' => $entry->type,
            'type_label' => $this->typeLabel($entry->type),
            'person_id' => $entry->person_id,
            'person_name' => $entry->person?->name,
            'person_type_label' => $entry->person?->personType?->name,
            'person_type_slug' => $entry->person?->personType?->slug,
            'person_document' => $entry->person ? $this->formatDocument($entry->person->document) : null,
            'person_document_digits' => $entry->person?->document,
            'description' => $entry->description,
            'amount' => (float) $entry->amount,
            'amount_formatted' => $this->money($entry->amount),
            'issue_date' => $entry->issue_date?->format('Y-m-d'),
            'issue_date_formatted' => $entry->issue_date?->format('d/m/Y'),
            'due_date' => $entry->due_date?->format('Y-m-d'),
            'due_date_formatted' => $entry->due_date?->format('d/m/Y'),
            'status' => $entry->status,
            'status_label' => $this->statusLabel($entry->status),
            'settlement_date' => $entry->settlement_date?->format('Y-m-d'),
            'settlement_date_formatted' => $entry->settlement_date?->format('d/m/Y'),
        ];
    }

    private function personTypeOptions(): array
    {
        return [
            ['value' => PersonType::CUSTOMER, 'label' => 'Cliente'],
            ['value' => PersonType::SUPPLIER, 'label' => 'Fornecedor'],
        ];
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

    private function statusLabel(string $status): string
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