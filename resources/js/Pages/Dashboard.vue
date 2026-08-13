<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import CashFlowChart from '@/Components/Dashboard/CashFlowChart.vue';
import ManagementTotalsCard from '@/Components/Dashboard/ManagementTotalsCard.vue';
import StatusBreakdownCard from '@/Components/Dashboard/StatusBreakdownCard.vue';
import SummaryMetricCard from '@/Components/Dashboard/SummaryMetricCard.vue';
import UpcomingBillsCard from '@/Components/Dashboard/UpcomingBillsCard.vue';

const summaryCards = [
    {
        label: 'Total a receber',
        value: 42880,
        detail: 'Titulos pendentes',
        trend: '+12,4%',
        color: 'text-amber-600',
        iconBg: 'bg-amber-50',
        icon: [
            'M12 2v20',
            'M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6',
        ],
    },
    {
        label: 'Total recebido',
        value: 28740,
        detail: 'Entradas realizadas',
        trend: '+8,2%',
        color: 'text-emerald-600',
        iconBg: 'bg-emerald-50',
        icon: [
            'M20 6 9 17l-5-5',
        ],
    },
    {
        label: 'Total a pagar',
        value: 31390,
        detail: 'Compromissos em aberto',
        trend: '-3,1%',
        color: 'text-rose-600',
        iconBg: 'bg-rose-50',
        icon: [
            'M3 6h18',
            'M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2',
            'M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6',
        ],
    },
    {
        label: 'Saldo previsto',
        value: 11490,
        detail: 'Receber menos pagar',
        trend: '+5,7%',
        color: 'text-sky-600',
        iconBg: 'bg-sky-50',
        icon: [
            'M3 3v18h18',
            'M7 15l4-4 3 3 5-7',
        ],
    },
];

const managementTotals = [
    { label: 'Recebido', value: 28740, percentage: 67, color: 'bg-emerald-500' },
    { label: 'Vencido a receber', value: 6120, percentage: 14, color: 'bg-amber-500' },
    { label: 'Pago', value: 19480, percentage: 62, color: 'bg-sky-500' },
    { label: 'Vencido a pagar', value: 4380, percentage: 10, color: 'bg-rose-500' },
];

const cashFlow = [
    { month: 'Mar', incoming: 12.4, outgoing: 8.2 },
    { month: 'Abr', incoming: 16.1, outgoing: 10.4 },
    { month: 'Mai', incoming: 14.7, outgoing: 12.2 },
    { month: 'Jun', incoming: 19.5, outgoing: 13.1 },
    { month: 'Jul', incoming: 22.8, outgoing: 14.6 },
    { month: 'Ago', incoming: 25.2, outgoing: 15.8 },
];

const receivableStatus = [
    { label: 'Pendente', amount: 42880, color: 'bg-amber-400' },
    { label: 'Recebido', amount: 28740, color: 'bg-emerald-500' },
    { label: 'Vencido', amount: 6120, color: 'bg-rose-500' },
    { label: 'Cancelado', amount: 980, color: 'bg-slate-300' },
];

const payableStatus = [
    { label: 'Pendente', amount: 31390, color: 'bg-sky-500' },
    { label: 'Pago', amount: 19480, color: 'bg-emerald-500' },
    { label: 'Vencido', amount: 4380, color: 'bg-rose-500' },
    { label: 'Cancelado', amount: 1220, color: 'bg-slate-300' },
];

const upcomingBills = [
    { title: 'NF 1024 - Cliente Atlas', type: 'Receber', due: '15/08', value: 4250, status: 'Pendente' },
    { title: 'Hospedagem Cloud', type: 'Pagar', due: '16/08', value: 680, status: 'Pendente' },
    { title: 'Contrato Manutencao', type: 'Receber', due: '18/08', value: 3900, status: 'Pendente' },
    { title: 'Fornecedor Logistica', type: 'Pagar', due: '20/08', value: 2150, status: 'Vencido' },
];
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
            <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
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
                    :realized-balance="9260"
                    period-label="Agosto"
                />
            </section>

            <section class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(340px,0.9fr)]">
                <StatusBreakdownCard title="Contas a receber" :items="receivableStatus" />
                <StatusBreakdownCard title="Contas a pagar" :items="payableStatus" />
                <UpcomingBillsCard :bills="upcomingBills" period-label="7 dias" />
            </section>
        </div>
    </AppLayout>
</template>
