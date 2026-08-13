<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    types: {
        type: Array,
        required: true,
    },
    submitLabel: {
        type: String,
        required: true,
    },
    cancelHref: {
        type: String,
        required: true,
    },
});

defineEmits(['submit']);

const onlyDigits = (value) => value.replace(/\D/g, '').slice(0, 14);

const formatCpf = (digits) => digits
    .replace(/^(\d{3})(\d)/, '$1.$2')
    .replace(/^(\d{3})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/^(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');

const formatCnpj = (digits) => digits
    .replace(/^(\d{2})(\d)/, '$1.$2')
    .replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3')
    .replace(/^(\d{2})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3/$4')
    .replace(/^(\d{2})\.(\d{3})\.(\d{3})\/(\d{4})(\d)/, '$1.$2.$3/$4-$5');

const formatDocument = (value) => {
    const digits = onlyDigits(value);

    if (digits.length <= 11) {
        return formatCpf(digits);
    }

    return formatCnpj(digits);
};

const documentKindLabel = computed(() => {
    const length = onlyDigits(props.form.document).length;

    if (length === 0) {
        return 'Digite CPF ou CNPJ';
    }

    if (length <= 11) {
        return length + '/11 digitos para CPF';
    }

    return length + '/14 digitos para CNPJ';
});

const updateDocument = (event) => {
    props.form.document = formatDocument(event.target.value);
};
</script>

<template>
    <form class="rounded-lg bg-white p-6 shadow-sm ring-1 ring-black/5" @submit.prevent="$emit('submit')">
        <div class="grid gap-5 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="name" class="text-sm font-medium text-slate-700">
                    Nome/Razao Social
                </label>
                <input
                    id="name"
                    v-model="form.name"
                    type="text"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    autocomplete="organization"
                >
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <label for="document" class="text-sm font-medium text-slate-700">
                    CPF/CNPJ
                </label>
                <input
                    id="document"
                    :value="form.document"
                    type="text"
                    inputmode="numeric"
                    maxlength="18"
                    pattern="(\d{3}\.\d{3}\.\d{3}-\d{2}|\d{2}\.\d{3}\.\d{3}/\d{4}-\d{2}|\d{11}|\d{14})"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    placeholder="000.000.000-00 ou 00.000.000/0000-00"
                    @input="updateDocument"
                >
                <p class="mt-1 text-xs text-slate-400">
                    {{ documentKindLabel }}
                </p>
                <InputError class="mt-2" :message="form.errors.document" />
            </div>

            <div>
                <label for="person_type_id" class="text-sm font-medium text-slate-700">
                    Tipo
                </label>
                <select
                    id="person_type_id"
                    v-model="form.person_type_id"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                >
                    <option value="">
                        Selecione
                    </option>
                    <option v-for="type in types" :key="type.value" :value="type.value">
                        {{ type.label }}
                    </option>
                </select>
                <InputError class="mt-2" :message="form.errors.person_type_id" />
            </div>

            <div>
                <label for="email" class="text-sm font-medium text-slate-700">
                    E-mail
                </label>
                <input
                    id="email"
                    v-model="form.email"
                    type="email"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    autocomplete="email"
                >
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div>
                <label for="phone" class="text-sm font-medium text-slate-700">
                    Telefone
                </label>
                <input
                    id="phone"
                    v-model="form.phone"
                    type="text"
                    class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                    autocomplete="tel"
                >
                <InputError class="mt-2" :message="form.errors.phone" />
            </div>
        </div>

        <div class="mt-6 flex flex-col-reverse gap-3 border-t border-slate-100 pt-5 sm:flex-row sm:justify-end">
            <Link
                :href="cancelHref"
                class="inline-flex justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50"
            >
                Cancelar
            </Link>
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
