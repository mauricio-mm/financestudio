<script setup>
import { reactive } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    people: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        required: true,
    },
    types: {
        type: Array,
        required: true,
    },
});

const filterForm = reactive({
    search: props.filters.search || '',
    person_type_id: props.filters.person_type_id || '',
});

const applyFilters = () => {
    router.get(route('people.index'), filterForm, {
        preserveState: true,
        replace: true,
    });
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.person_type_id = '';

    router.get(route('people.index'), {}, {
        preserveState: true,
        replace: true,
    });
};

const destroyPerson = (person) => {
    if (! window.confirm(`Remover ${person.name}?`)) {
        return;
    }

    router.delete(route('people.destroy', person.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <AppLayout title="Pessoas/Empresas">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Cadastros
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Pessoas/Empresas
                </h1>
            </div>
        </template>

        <div class="space-y-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-slate-900">
                        Cadastros comerciais
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Clientes e fornecedores usados nas contas a receber e a pagar.
                    </p>
                </div>

                <Link
                    :href="route('people.create')"
                    class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                >
                    Novo cadastro
                </Link>
            </div>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
                <form class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]" @submit.prevent="applyFilters">
                    <div>
                        <label for="search" class="text-sm font-medium text-slate-700">
                            Buscar
                        </label>
                        <input
                            id="search"
                            v-model="filterForm.search"
                            type="text"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Nome, documento, e-mail ou telefone"
                        >
                    </div>

                    <div>
                        <label for="type" class="text-sm font-medium text-slate-700">
                            Tipo
                        </label>
                        <select
                            id="type"
                            v-model="filterForm.person_type_id"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                        >
                            <option value="">
                                Todos
                            </option>
                            <option v-for="type in types" :key="type.value" :value="type.value">
                                {{ type.label }}
                            </option>
                        </select>
                    </div>

                    <div class="flex items-end gap-2">
                        <button
                            type="submit"
                            class="inline-flex w-full justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 lg:w-auto"
                        >
                            Filtrar
                        </button>
                        <button
                            type="button"
                            class="inline-flex w-full justify-center rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm transition hover:bg-slate-50 lg:w-auto"
                            @click="clearFilters"
                        >
                            Limpar
                        </button>
                    </div>
                </form>
            </section>

            <section class="overflow-hidden rounded-lg bg-white shadow-sm ring-1 ring-black/5">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-100">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Nome/Razao Social
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    CPF/CNPJ
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Tipo
                                </th>
                                <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Contato
                                </th>
                                <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wide text-slate-500">
                                    Acoes
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-slate-100">
                            <tr v-if="people.data.length === 0">
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Nenhuma pessoa/empresa encontrada.
                                </td>
                            </tr>

                            <tr v-for="person in people.data" :key="person.id" class="hover:bg-slate-50/70">
                                <td class="px-5 py-4">
                                    <p class="text-sm font-semibold text-slate-900">
                                        {{ person.name }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        Cadastrado em {{ person.created_at }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-sm text-slate-600">
                                    {{ person.document }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">
                                        {{ person.type_label }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-sm text-slate-700">
                                        {{ person.email || '-' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ person.phone || '-' }}
                                    </p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link
                                            :href="route('people.edit', person.id)"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                        >
                                            Editar
                                        </Link>
                                        <button
                                            type="button"
                                            class="rounded-lg border border-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-600 transition hover:bg-rose-50"
                                            @click="destroyPerson(person)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div
                    v-if="people.links.length > 3"
                    class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4"
                >
                    <p class="text-sm text-slate-500">
                        Mostrando {{ people.from }} a {{ people.to }} de {{ people.total }} registros
                    </p>

                    <div class="flex flex-wrap gap-1">
                        <template v-for="link in people.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                preserve-scroll
                                class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
                                :class="link.active ? 'bg-slate-900 text-white' : 'text-slate-600 hover:bg-slate-100'"
                                v-html="link.label"
                            />
                            <span
                                v-else
                                class="rounded-lg px-3 py-1.5 text-sm font-medium text-slate-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </section>
        </div>
    </AppLayout>
</template>
