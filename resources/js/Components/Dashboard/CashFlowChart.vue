<script setup>
import { computed } from 'vue';
import {
    BarElement,
    CategoryScale,
    Chart as ChartJS,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';
import { Bar } from 'vue-chartjs';
import { money } from '@/Utils/formatters';

ChartJS.register(CategoryScale, LinearScale, BarElement, Tooltip, Legend);

const props = defineProps({
    items: {
        type: Array,
        required: true,
    },
});

const chartData = computed(() => ({
    labels: props.items.map((item) => item.month),
    datasets: [
        {
            label: 'Recebido',
            data: props.items.map((item) => item.incoming),
            backgroundColor: '#10b981',
            borderRadius: 6,
        },
        {
            label: 'Pago',
            data: props.items.map((item) => item.outgoing),
            backgroundColor: '#f43f5e',
            borderRadius: 6,
        },
    ],
}));

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                boxWidth: 10,
                boxHeight: 10,
                usePointStyle: true,
            },
        },
        tooltip: {
            callbacks: {
                label: (context) => `${context.dataset.label}: ${money(context.parsed.y)}`,
            },
        },
    },
    scales: {
        x: {
            grid: {
                display: false,
            },
        },
        y: {
            beginAtZero: true,
            ticks: {
                callback: (value) => money(value),
            },
        },
    },
};
</script>

<template>
    <article class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
        <div>
            <p class="text-sm font-medium text-slate-500">
                Fluxo de caixa
            </p>
            <h2 class="mt-1 text-base font-semibold text-slate-900">
                Recebido vs pago por mes
            </h2>
        </div>

        <div class="mt-5 h-72">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </article>
</template>