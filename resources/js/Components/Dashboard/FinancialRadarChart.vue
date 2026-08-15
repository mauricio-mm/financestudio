<script setup>
import { computed } from 'vue';
import {
    Chart as ChartJS,
    Filler,
    Legend,
    LineElement,
    PointElement,
    RadialLinearScale,
    Tooltip,
} from 'chart.js';
import { Radar } from 'vue-chartjs';
import { money } from '@/Utils/formatters';

ChartJS.register(RadialLinearScale, PointElement, LineElement, Filler, Tooltip, Legend);

const props = defineProps({
    metrics: {
        type: Object,
        required: true,
    },
});

const radarItems = computed(() => [
    { label: 'A receber', value: props.metrics.receivable_pending + props.metrics.receivable_overdue },
    { label: 'Recebido', value: props.metrics.receivable_received },
    { label: 'A pagar', value: props.metrics.payable_pending + props.metrics.payable_overdue },
    { label: 'Pago', value: props.metrics.payable_paid },
    { label: 'Vencido', value: props.metrics.receivable_overdue + props.metrics.payable_overdue },
]);

const maxValue = computed(() => Math.max(...radarItems.value.map((item) => item.value), 1));

const chartData = computed(() => ({
    labels: radarItems.value.map((item) => item.label),
    datasets: [
        {
            label: 'Indice financeiro',
            data: radarItems.value.map((item) => Math.round((item.value / maxValue.value) * 100)),
            rawValues: radarItems.value.map((item) => item.value),
            backgroundColor: 'rgba(16, 185, 129, 0.16)',
            borderColor: '#10b981',
            borderWidth: 2,
            pointBackgroundColor: '#0f172a',
            pointBorderColor: '#ffffff',
            pointHoverRadius: 5,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    scales: {
        r: {
            beginAtZero: true,
            max: 100,
            ticks: {
                display: false,
                stepSize: 25,
            },
            grid: {
                color: '#e2e8f0',
            },
            angleLines: {
                color: '#e2e8f0',
            },
            pointLabels: {
                color: '#475569',
                font: {
                    size: 12,
                    weight: '600',
                },
            },
        },
    },
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            callbacks: {
                label: (context) => {
                    const rawValue = context.dataset.rawValues?.[context.dataIndex] ?? 0;

                    return `${context.label}: ${money(rawValue)}`;
                },
            },
        },
    },
};
</script>

<template>
    <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
        <div>
            <p class="text-sm font-medium text-slate-500">
                Leitura gerencial
            </p>
            <h2 class="mt-1 text-base font-semibold text-slate-900">
                Distribuicao das metricas
            </h2>
        </div>

        <div class="mt-5 h-72">
            <Radar :data="chartData" :options="chartOptions" />
        </div>
    </article>
</template>