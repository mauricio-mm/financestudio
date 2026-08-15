<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';
import CashFlowChart from '@/Components/Dashboard/CashFlowChart.vue';
import ManagementTotalsCard from '@/Components/Dashboard/ManagementTotalsCard.vue';
import StatusBreakdownCard from '@/Components/Dashboard/StatusBreakdownCard.vue';
import SummaryMetricCard from '@/Components/Dashboard/SummaryMetricCard.vue';
import UpcomingBillsCard from '@/Components/Dashboard/UpcomingBillsCard.vue';

const props = defineProps({
    people: {
        type: Object,
        required: true,
    },
    metrics: {
        type: Object,
        required: true,
    },
    status: {
        type: Object,
        required: true,
    },
    cashFlow: {
        type: Array,
        required: true,
    },
    upcomingBills: {
        type: Array,
        required: true,
    },
});

const numberFormatter = new Intl.NumberFormat('pt-BR');
const countLabel = (value, singular, plural) => `${numberFormatter.format(value)} ${value === 1 ? singular : plural}`;
const percentage = (value, total) => (total > 0 ? Math.min(100, Math.round((value / total) * 100)) : 0);

const receivableTotal = computed(() => props.metrics.receivable_pending
    + props.metrics.receivable_received
    + props.metrics.receivable_overdue
    + props.metrics.receivable_cancelled);

const payableTotal = computed(() => props.metrics.payable_pending
    + props.metrics.payable_paid
    + props.metrics.payable_overdue
    + props.metrics.payable_cancelled);

const periodLabel = computed(() => new Intl.DateTimeFormat('pt-BR', { month: 'long' }).format(new Date()));

const summaryCards = computed(() => [
    {
        label: 'Clientes cadastrados',
        value: props.people.customers,
        format: 'number',
        detail: 'Base comercial',
        trend: countLabel(props.people.suppliers, 'fornecedor', 'fornecedores'),
        color: 'text-indigo-600',
        iconBg: 'bg-indigo-50',
        icon: [
            'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2',
            'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
            'M22 21v-2a4 4 0 0 0-3-3.87',
            'M16 3.13a4 4 0 0 1 0 7.75',
        ],
    },
    {
        label: 'Total a receber',
        value: props.metrics.receivable_pending,
        detail: 'Titulos pendentes',
        trend: countLabel(props.metrics.receivable_count, 'conta', 'contas'),
        color: 'text-amber-600',
        iconBg: 'bg-amber-50',
        icon: [
            'M12 2v20',
            'M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6',
        ],
    },
    {
        label: 'Total recebido',
        value: props.metrics.receivable_received,
        detail: 'Entradas realizadas',
        trend: 'Recebido',
        color: 'text-emerald-600',
        iconBg: 'bg-emerald-50',
        icon: [
            'M20 6 9 17l-5-5',
        ],
    },
    {
        label: 'Vencido a receber',
        value: props.metrics.receivable_overdue,
        detail: 'Recebimentos atrasados',
        trend: 'Vencido',
        color: 'text-rose-600',
        iconBg: 'bg-rose-50',
        icon: [
            'M12 8v4l3 3',
            'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        ],
    },
    {
        label: 'Total a pagar',
        value: props.metrics.payable_pending,
        detail: 'Compromissos em aberto',
        trend: countLabel(props.metrics.payable_count, 'conta', 'contas'),
        color: 'text-sky-600',
        iconBg: 'bg-sky-50',
        icon: [
            'M3 6h18',
            'M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2',
            'M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6',
        ],
    },
    {
        label: 'Total pago',
        value: props.metrics.payable_paid,
        detail: 'Saidas realizadas',
        trend: 'Pago',
        color: 'text-emerald-600',
        iconBg: 'bg-emerald-50',
        icon: [
            'M9 12l2 2 4-4',
            'M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        ],
    },
    {
        label: 'Vencido a pagar',
        value: props.metrics.payable_overdue,
        detail: 'Pagamentos atrasados',
        trend: 'Vencido',
        color: 'text-rose-600',
        iconBg: 'bg-rose-50',
        icon: [
            'M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z',
            'M12 9v4',
            'M12 17h.01',
        ],
    },
    {
        label: 'Saldo previsto',
        value: props.metrics.forecast_balance,
        detail: 'Receber menos pagar',
        trend: 'Previsto',
        color: props.metrics.forecast_balance >= 0 ? 'text-sky-600' : 'text-rose-600',
        iconBg: props.metrics.forecast_balance >= 0 ? 'bg-sky-50' : 'bg-rose-50',
        icon: [
            'M3 3v18h18',
            'M7 15l4-4 3 3 5-7',
        ],
    },
    {
        label: 'Saldo realizado',
        value: props.metrics.realized_balance,
        detail: 'Recebido menos pago',
        trend: 'Realizado',
        color: props.metrics.realized_balance >= 0 ? 'text-emerald-600' : 'text-rose-600',
        iconBg: props.metrics.realized_balance >= 0 ? 'bg-emerald-50' : 'bg-rose-50',
        icon: [
            'M12 2v20',
            'M5 7h14',
            'M5 17h14',
        ],
    },
]);

const managementTotals = computed(() => [
    {
        label: 'Recebido',
        value: props.metrics.receivable_received,
        percentage: percentage(props.metrics.receivable_received, receivableTotal.value),
        color: 'bg-emerald-500',
    },
    {
        label: 'Vencido a receber',
        value: props.metrics.receivable_overdue,
        percentage: percentage(props.metrics.receivable_overdue, receivableTotal.value),
        color: 'bg-amber-500',
    },
    {
        label: 'Pago',
        value: props.metrics.payable_paid,
        percentage: percentage(props.metrics.payable_paid, payableTotal.value),
        color: 'bg-sky-500',
    },
    {
        label: 'Vencido a pagar',
        value: props.metrics.payable_overdue,
        percentage: percentage(props.metrics.payable_overdue, payableTotal.value),
        color: 'bg-rose-500',
    },
]);
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Painel financeiro
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Visao geral
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-5">
                <SummaryMetricCard
                    v-for="card in summaryCards"
                    :key="card.label"
                    :card="card"
                />
            </section>

            <section class="grid gap-6 xl:grid-cols-[minmax(0,1.65fr)_minmax(360px,0.95fr)]">
                <CashFlowChart :items="cashFlow" />
                <ManagementTotalsCard
                    :items="managementTotals"
                    :realized-balance="metrics.realized_balance"
                    :period-label="periodLabel"
                />
            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(340px,0.9fr)]">
                <StatusBreakdownCard title="Contas a receber" :items="status.receivable" />
                <StatusBreakdownCard title="Contas a pagar" :items="status.payable" />
                <UpcomingBillsCard :bills="upcomingBills" period-label="7 dias" />
            </section>
        </div>
    </AppLayout>
</template>