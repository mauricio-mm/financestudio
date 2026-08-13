<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonForm from '@/Pages/People/Partials/PersonForm.vue';

const props = defineProps({
    person: {
        type: Object,
        required: true,
    },
    types: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: props.person.name,
    document: props.person.document,
    email: props.person.email || '',
    phone: props.person.phone || '',
    person_type_id: props.person.person_type_id,
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        document: data.document.replace(/\D/g, ''),
    })).put(route('people.update', props.person.id));
};
</script>

<template>
    <AppLayout title="Editar pessoa/empresa">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Pessoas/Empresas
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Editar cadastro
                </h1>
            </div>
        </template>

        <PersonForm
            :form="form"
            :types="types"
            submit-label="Salvar alteracoes"
            :cancel-href="route('people.index')"
            @submit="submit"
        />
    </AppLayout>
</template>
