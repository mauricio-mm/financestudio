<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Modal from '@/Components/Modal.vue';
import PersonForm from '@/Pages/People/Partials/PersonForm.vue';

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

const loadedPeople = ref([]);
const peoplePage = ref(1);
const peopleTotal = ref(0);
const hasMorePeople = ref(false);
const loadingPeople = ref(false);
const peopleError = ref('');

const showingPersonModal = ref(false);
const editingPerson = ref(null);

const personForm = useForm({
    name: '',
    document: '',
    email: '',
    phone: '',
    person_type_id: '',
});

const modalTitle = computed(() => (editingPerson.value ? 'Editar cadastro' : 'Novo cadastro'));
const submitLabel = computed(() => (editingPerson.value ? 'Salvar alteracoes' : 'Cadastrar'));

const onlyDigits = (value) => String(value || '').replace(/\D/g, '');
const normalizeText = (value) => String(value || '')
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .toLowerCase()
    .trim();

const appendUnique = (items, incoming, getKey) => {
    const existing = new Set(items.map(getKey));

    incoming.forEach((item) => {
        const key = getKey(item);

        if (!existing.has(key)) {
            items.push(item);
            existing.add(key);
        }
    });
};

const syncPeople = (people) => {
    loadedPeople.value = [...(people.data || [])];
    peoplePage.value = people.current_page || 1;
    peopleTotal.value = people.total || loadedPeople.value.length;
    hasMorePeople.value = Boolean(people.has_more);
};

syncPeople(props.people);

watch(() => props.people, (people) => {
    syncPeople(people);
});

const filteredPeople = computed(() => loadedPeople.value.filter((person) => {
    if (filterForm.person_type_id && String(person.person_type_id) !== String(filterForm.person_type_id)) {
        return false;
    }

    const search = normalizeText(filterForm.search);

    if (!search) {
        return true;
    }

    const searchDigits = onlyDigits(filterForm.search);
    const searchableText = normalizeText(`${person.name || ''} ${person.email || ''} ${person.document || ''} ${person.phone || ''}`);
    const searchableDigits = `${person.document_digits || ''} ${person.phone_digits || ''}`;
    const foundByText = searchableText.includes(search);
    const foundByDigits = searchDigits && searchableDigits.includes(searchDigits);

    return foundByText || foundByDigits;
}));

const fetchJson = async (url) => {
    const response = await fetch(url, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    if (!response.ok) {
        throw new Error('Nao foi possivel carregar os dados agora.');
    }

    return response.json();
};

const loadMorePeople = async () => {
    if (loadingPeople.value || !hasMorePeople.value) {
        return;
    }

    loadingPeople.value = true;
    peopleError.value = '';

    try {
        const params = new URLSearchParams({ page: String(peoplePage.value + 1) });
        const payload = await fetchJson(`${route('people.data')}?${params.toString()}`);

        appendUnique(loadedPeople.value, payload.data || [], (person) => person.id);
        peoplePage.value = payload.current_page || peoplePage.value;
        peopleTotal.value = payload.total || peopleTotal.value;
        hasMorePeople.value = Boolean(payload.has_more);
    } catch (error) {
        peopleError.value = error.message;
    } finally {
        loadingPeople.value = false;
    }
};

const resetPersonForm = (person = null) => {
    personForm.clearErrors();
    personForm.name = person?.name || '';
    personForm.document = person?.document || '';
    personForm.email = person?.email || '';
    personForm.phone = person?.phone || '';
    personForm.person_type_id = person?.person_type_id || '';
};

const openCreateModal = () => {
    editingPerson.value = null;
    resetPersonForm();
    showingPersonModal.value = true;
};

const openEditModal = (person) => {
    editingPerson.value = person;
    resetPersonForm(person);
    showingPersonModal.value = true;
};

const closePersonModal = (force = false) => {
    if (personForm.processing && !force) {
        return;
    }

    showingPersonModal.value = false;
    editingPerson.value = null;
    personForm.clearErrors();
};

const normalizedPersonData = (data) => ({
    ...data,
    document: onlyDigits(data.document),
    phone: data.phone ? onlyDigits(data.phone) : null,
});

const submitPerson = () => {
    personForm.transform(normalizedPersonData);

    const options = {
        preserveScroll: true,
        onSuccess: () => closePersonModal(true),
    };

    if (editingPerson.value) {
        personForm.put(route('people.update', editingPerson.value.id), options);
        return;
    }

    personForm.post(route('people.store'), options);
};

const clearFilters = () => {
    filterForm.search = '';
    filterForm.person_type_id = '';
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
                        {{ filteredPeople.length }} cadastros filtrados em {{ loadedPeople.length }} carregados.
                    </p>
                </div>

                <button
                    type="button"
                    class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800"
                    @click="openCreateModal"
                >
                    Novo cadastro
                </button>
            </div>

            <section class="rounded-lg bg-white p-5 shadow-sm ring-1 ring-black/5">
                <form class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_220px_auto]" @submit.prevent>
                    <div>
                        <label for="search" class="text-sm font-medium text-slate-700">
                            Buscar
                        </label>
                        <input
                            id="search"
                            v-model="filterForm.search"
                            type="search"
                            class="mt-2 w-full rounded-lg border-slate-200 text-sm shadow-sm focus:border-emerald-500 focus:ring-emerald-500"
                            placeholder="Nome, CPF/CNPJ, e-mail ou telefone"
                        >
                    </div>

                    <div>
                        <label for="type" class="text-sm font-medium text-slate-700">
                            Cliente/Fornecedor
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
                            <tr v-if="filteredPeople.length === 0">
                                <td colspan="5" class="px-5 py-10 text-center text-sm text-slate-500">
                                    Nenhuma pessoa/empresa encontrada nos dados carregados.
                                </td>
                            </tr>

                            <tr v-for="person in filteredPeople" :key="person.id" class="hover:bg-slate-50/70">
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
                                        <button
                                            type="button"
                                            class="rounded-lg border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-50"
                                            @click="openEditModal(person)"
                                        >
                                            Editar
                                        </button>
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

                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 px-5 py-4">
                    <div>
                        <p class="text-sm text-slate-500">
                            Mostrando {{ filteredPeople.length }} filtrados em {{ loadedPeople.length }} cadastros carregados de {{ peopleTotal }} disponiveis.
                        </p>
                        <p v-if="peopleError" class="mt-2 text-xs font-medium text-rose-600">
                            {{ peopleError }}
                        </p>
                    </div>

                    <button
                        v-if="hasMorePeople"
                        type="button"
                        class="inline-flex justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800 disabled:cursor-not-allowed disabled:bg-slate-300"
                        :disabled="loadingPeople"
                        @click="loadMorePeople"
                    >
                        {{ loadingPeople ? 'Carregando...' : 'Carregar +20 cadastros' }}
                    </button>
                    <span v-else class="text-sm font-medium text-slate-400">
                        Todos os cadastros foram carregados
                    </span>
                </div>
            </section>
        </div>

        <Modal :show="showingPersonModal" max-width="2xl" @close="closePersonModal">
            <div class="border-b border-slate-100 px-6 py-5">
                <h2 class="text-lg font-semibold text-slate-900">
                    {{ modalTitle }}
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Informe os dados principais para usar este cadastro nas movimentacoes.
                </p>
            </div>

            <PersonForm
                :form="personForm"
                :types="types"
                :submit-label="submitLabel"
                @submit="submitPerson"
                @cancel="closePersonModal"
            />
        </Modal>
    </AppLayout>
</template>