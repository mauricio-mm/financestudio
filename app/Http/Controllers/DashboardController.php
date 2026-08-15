<?php

namespace App\Http\Controllers;

use App\Models\FinancialEntry;
use App\Models\Person;
use App\Models\PersonType;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $userId = $request->user()->id;
        $metrics = $this->metrics($userId);

        return Inertia::render('Dashboard', [
            'people' => $this->peopleCounts($userId),
            'metrics' => $metrics,
            'status' => $this->statusBreakdown($metrics),
            'cashFlow' => $this->cashFlow($userId),
            'upcomingBills' => $this->upcomingBills($userId),
        ]);
    }

    private function peopleCounts(int $userId): array
    {
        $baseQuery = Person::query()->where('user_id', $userId);

        return [
            'customers' => (clone $baseQuery)
                ->whereHas('personType', fn ($query) => $query->where('slug', PersonType::CUSTOMER))
                ->count(),
            'suppliers' => (clone $baseQuery)
                ->whereHas('personType', fn ($query) => $query->where('slug', PersonType::SUPPLIER))
                ->count(),
            'total' => (clone $baseQuery)->count(),
        ];
    }

    private function metrics(int $userId): array
    {
        $baseQuery = FinancialEntry::query()->where('user_id', $userId);

        $receivablePending = $this->sumBy($baseQuery, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_PENDING);
        $receivableReceived = $this->sumBy($baseQuery, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_RECEIVED);
        $receivableOverdue = $this->sumBy($baseQuery, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_OVERDUE);
        $receivableCancelled = $this->sumBy($baseQuery, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_CANCELLED);
        $payablePending = $this->sumBy($baseQuery, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_PENDING);
        $payablePaid = $this->sumBy($baseQuery, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_PAID);
        $payableOverdue = $this->sumBy($baseQuery, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_OVERDUE);
        $payableCancelled = $this->sumBy($baseQuery, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_CANCELLED);

        return [
            'receivable_pending' => $receivablePending,
            'receivable_received' => $receivableReceived,
            'receivable_overdue' => $receivableOverdue,
            'receivable_cancelled' => $receivableCancelled,
            'receivable_count' => (clone $baseQuery)->where('type', FinancialEntry::TYPE_RECEIVABLE)->count(),
            'payable_pending' => $payablePending,
            'payable_paid' => $payablePaid,
            'payable_overdue' => $payableOverdue,
            'payable_cancelled' => $payableCancelled,
            'payable_count' => (clone $baseQuery)->where('type', FinancialEntry::TYPE_PAYABLE)->count(),
            'forecast_balance' => ($receivablePending + $receivableOverdue) - ($payablePending + $payableOverdue),
            'realized_balance' => $receivableReceived - $payablePaid,
        ];
    }

    private function statusBreakdown(array $metrics): array
    {
        return [
            'receivable' => [
                ['label' => 'Pendente', 'amount' => $metrics['receivable_pending'], 'color' => 'bg-amber-400'],
                ['label' => 'Recebido', 'amount' => $metrics['receivable_received'], 'color' => 'bg-emerald-500'],
                ['label' => 'Vencido', 'amount' => $metrics['receivable_overdue'], 'color' => 'bg-rose-500'],
                ['label' => 'Cancelado', 'amount' => $metrics['receivable_cancelled'], 'color' => 'bg-slate-300'],
            ],
            'payable' => [
                ['label' => 'Pendente', 'amount' => $metrics['payable_pending'], 'color' => 'bg-sky-500'],
                ['label' => 'Pago', 'amount' => $metrics['payable_paid'], 'color' => 'bg-emerald-500'],
                ['label' => 'Vencido', 'amount' => $metrics['payable_overdue'], 'color' => 'bg-rose-500'],
                ['label' => 'Cancelado', 'amount' => $metrics['payable_cancelled'], 'color' => 'bg-slate-300'],
            ],
        ];
    }

    private function cashFlow(int $userId): array
    {
        return collect(range(5, 0))
            ->map(function (int $monthsAgo) use ($userId) {
                $month = CarbonImmutable::now()->startOfMonth()->subMonths($monthsAgo);
                $start = $month->toDateString();
                $end = $month->endOfMonth()->toDateString();

                return [
                    'month' => $this->monthLabel($month),
                    'incoming' => $this->realizedSum($userId, FinancialEntry::TYPE_RECEIVABLE, FinancialEntry::STATUS_RECEIVED, $start, $end),
                    'outgoing' => $this->realizedSum($userId, FinancialEntry::TYPE_PAYABLE, FinancialEntry::STATUS_PAID, $start, $end),
                ];
            })
            ->all();
    }

    private function upcomingBills(int $userId): array
    {
        $today = CarbonImmutable::today();
        $limit = $today->addDays(7);

        return FinancialEntry::query()
            ->with('person')
            ->where('user_id', $userId)
            ->whereIn('status', [FinancialEntry::STATUS_PENDING, FinancialEntry::STATUS_OVERDUE])
            ->where(function ($query) use ($today, $limit) {
                $query
                    ->whereBetween('due_date', [$today->toDateString(), $limit->toDateString()])
                    ->orWhere('status', FinancialEntry::STATUS_OVERDUE);
            })
            ->orderBy('due_date')
            ->limit(5)
            ->get()
            ->map(fn (FinancialEntry $entry) => [
                'id' => $entry->id,
                'title' => $entry->description.' - '.$entry->person?->name,
                'type' => $entry->type === FinancialEntry::TYPE_RECEIVABLE ? 'Receber' : 'Pagar',
                'due' => $entry->due_date?->format('d/m'),
                'value' => (float) $entry->amount,
                'status' => $entry->status === FinancialEntry::STATUS_OVERDUE ? 'Vencido' : 'Pendente',
            ])
            ->all();
    }

    private function sumBy($query, string $type, string $status): float
    {
        return (float) (clone $query)
            ->where('type', $type)
            ->where('status', $status)
            ->sum('amount');
    }

    private function realizedSum(int $userId, string $type, string $status, string $start, string $end): float
    {
        return (float) FinancialEntry::query()
            ->where('user_id', $userId)
            ->where('type', $type)
            ->where('status', $status)
            ->whereBetween('settlement_date', [$start, $end])
            ->sum('amount');
    }

    private function monthLabel(CarbonImmutable $month): string
    {
        return [
            1 => 'Jan',
            2 => 'Fev',
            3 => 'Mar',
            4 => 'Abr',
            5 => 'Mai',
            6 => 'Jun',
            7 => 'Jul',
            8 => 'Ago',
            9 => 'Set',
            10 => 'Out',
            11 => 'Nov',
            12 => 'Dez',
        ][(int) $month->format('n')];
    }
}