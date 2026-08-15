<script setup>
import { watch } from 'vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    people: {
        type: Array,
        required: true,
    },
    statuses: {
        type: Array,
        required: true,
    },
    labels: {
        type: Object,
        required: true,
    },
    settledStatus: {
        type: String,
        required: true,
    },
    submitLabel: {
        type: String,
        required: true,
    },
});

const emit = defineEmits(['submit', 'cancel']);

watch(() => props.form.status, (status) => {
    if (status !== props.settledStatus) {
        props.form.settlement_date = '';
    }
});
</script>

<template>
    <form class="p-6" @submit.prevent="emit('submit')">
        <div class="grid gap-5 md:grid-cols-2">
            <div>
                <label for="person_id" class="text-sm font-medium text-slate-700">
                    {{ labels.person }}
                </label>
                <select
                    id="person_id"
                    v-model="form.person_id"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option value="">
                        Selecione
                    </option>
                    <option v-for="person in people" :key="person.value" :value="person.value">
                        {{ person.label }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.person_id" />
            </div>

            <div>
                <label for="amount" class="text-sm font-medium text-slate-700">
                    Valor
                </label>
                <input
                    id="amount"
                    v-model="form.amount"
                    type="number"
                    min="0.01"
                    step="0.01"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="0,00"
                >
                <InputError class="mt-2" :message="form.errors.amount" />
            </div>

            <div class="md:col-span-2">
                <label for="description" class="text-sm font-medium text-slate-700">
                    Descricao
                </label>
                <input
                    id="description"
                    v-model="form.description"
                    type="text"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    :placeholder="labels.descriptionPlaceholder"
                >
                <InputError class="mt-2" :message="form.errors.description" />
            </div>

            <div>
                <label for="issue_date" class="text-sm font-medium text-slate-700">
                    Data de emissao
                </label>
                <input
                    id="issue_date"
                    v-model="form.issue_date"
                    type="date"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                <InputError class="mt-2" :message="form.errors.issue_date" />
            </div>

            <div>
                <label for="due_date" class="text-sm font-medium text-slate-700">
                    Data de vencimento
                </label>
                <input
                    id="due_date"
                    v-model="form.due_date"
                    type="date"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                <InputError class="mt-2" :message="form.errors.due_date" />
            </div>

            <div>
                <label for="status" class="text-sm font-medium text-slate-700">
                    Status
                </label>
                <select
                    id="status"
                    v-model="form.status"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option v-for="status in statuses" :key="status.value" :value="status.value">
                        {{ status.label }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.status" />
            </div>

            <div>
                <label for="settlement_date" class="text-sm font-medium text-slate-700">
                    {{ labels.settlementDate }}
                </label>
                <input
                    id="settlement_date"
                    v-model="form.settlement_date"
                    type="date"
                    :disabled="form.status !== settledStatus"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500 disabled:bg-slate-50 disabled:text-slate-400"
                >
                <InputError class="mt-2" :message="form.errors.settlement_date" />
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <button
                type="button"
                class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
                @click="emit('cancel')"
            >
                Cancelar
            </button>
            <button
                type="submit"
                class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:opacity-50"
                :disabled="form.processing"
            >
                {{ submitLabel }}
            </button>
        </div>
    </form>
</template>