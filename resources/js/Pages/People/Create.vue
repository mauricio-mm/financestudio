<script setup>
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PersonForm from '@/Pages/People/Partials/PersonForm.vue';

defineProps({
    types: {
        type: Array,
        required: true,
    },
});

const form = useForm({
    name: '',
    document: '',
    email: '',
    phone: '',
    person_type_id: '',
});

const submit = () => {
    form.transform((data) => ({
        ...data,
        document: data.document.replace(/\D/g, ''),
    })).post(route('people.store'));
};
</script>

<template>
    <AppLayout title="Nova pessoa/empresa">
        <template #header>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                    Pessoas/Empresas
                </p>
                <h1 class="text-xl font-semibold text-slate-900">
                    Novo cadastro
                </h1>
            </div>
        </template>

        <PersonForm
            :form="form"
            :types="types"
            submit-label="Cadastrar"
            :cancel-href="route('people.index')"
            @submit="submit"
        />
    </AppLayout>
</template>
