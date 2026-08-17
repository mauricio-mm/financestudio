<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Checkbox from '@/Components/Checkbox.vue';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Criar conta" />

    <div class="relative min-h-screen overflow-hidden bg-slate-950 text-white">
        <!-- Background -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"
            ></div>

            <div
                class="absolute -bottom-40 -right-40 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"
            ></div>

            <div
                class="absolute left-1/2 top-1/2 h-80 w-80 -translate-x-1/2 -translate-y-1/2 rounded-full bg-emerald-500/5 blur-3xl"
            ></div>
        </div>

        <div class="relative flex min-h-screen items-center justify-center px-6 py-10">
            <div class="w-full max-w-md">

                <!-- Logo -->
                <div class="mb-8 text-center">
                    <Link
                        href="/"
                        class="inline-flex items-center gap-3"
                    >
                        <div
                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-500 shadow-lg shadow-emerald-500/20"
                        >
                            <svg
                                class="h-6 w-6 text-slate-950"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M12 3v18M3 12h18"
                                />
                            </svg>
                        </div>

                        <div class="text-left">
                            <div class="text-xl font-bold tracking-tight">
                                FinanceStudio
                            </div>

                            <div class="text-xs text-slate-400">
                                Gestão financeira
                            </div>
                        </div>
                    </Link>

                    <h1 class="mt-8 text-2xl font-bold tracking-tight">
                        Crie sua conta
                    </h1>

                    <p class="mt-2 text-sm text-slate-400">
                        Comece a organizar suas finanças com o FinanceStudio.
                    </p>
                </div>

                <!-- Card -->
                <AuthenticationCard
                    class="!max-w-none !border !border-white/10 !bg-white/[0.04] !shadow-2xl !shadow-black/20 !backdrop-blur"
                >
                    <template #logo>
                        <div></div>
                    </template>

                    <form @submit.prevent="submit" class="space-y-5">
                        <!-- Nome -->
                        <div>
                            <InputLabel
                                for="name"
                                value="Nome"
                                class="!text-sm !font-medium !text-slate-300"
                            />

                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-2 block w-full !rounded-xl !border-white/10 !bg-slate-900/70 !text-white placeholder:!text-slate-600 focus:!border-emerald-400 focus:!ring-emerald-400"
                                placeholder="Seu nome"
                                required
                                autofocus
                                autocomplete="name"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.name"
                            />
                        </div>

                        <!-- Email -->
                        <div>
                            <InputLabel
                                for="email"
                                value="E-mail"
                                class="!text-sm !font-medium !text-slate-300"
                            />

                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-2 block w-full !rounded-xl !border-white/10 !bg-slate-900/70 !text-white placeholder:!text-slate-600 focus:!border-emerald-400 focus:!ring-emerald-400"
                                placeholder="voce@empresa.com"
                                required
                                autocomplete="username"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Senha -->
                        <div>
                            <InputLabel
                                for="password"
                                value="Senha"
                                class="!text-sm !font-medium !text-slate-300"
                            />

                            <TextInput
                                id="password"
                                v-model="form.password"
                                type="password"
                                class="mt-2 block w-full !rounded-xl !border-white/10 !bg-slate-900/70 !text-white placeholder:!text-slate-600 focus:!border-emerald-400 focus:!ring-emerald-400"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Confirmar senha -->
                        <div>
                            <InputLabel
                                for="password_confirmation"
                                value="Confirmar senha"
                                class="!text-sm !font-medium !text-slate-300"
                            />

                            <TextInput
                                id="password_confirmation"
                                v-model="form.password_confirmation"
                                type="password"
                                class="mt-2 block w-full !rounded-xl !border-white/10 !bg-slate-900/70 !text-white placeholder:!text-slate-600 focus:!border-emerald-400 focus:!ring-emerald-400"
                                placeholder="••••••••"
                                required
                                autocomplete="new-password"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.password_confirmation"
                            />
                        </div>

                        <!-- Terms -->
                        <div
                            v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature"
                            class="rounded-xl border border-white/5 bg-white/[0.02] p-4"
                        >
                            <label
                                for="terms"
                                class="flex items-start"
                            >
                                <Checkbox
                                    id="terms"
                                    v-model:checked="form.terms"
                                    name="terms"
                                    class="mt-0.5 !border-slate-600 !bg-slate-900 checked:!border-emerald-500 checked:!bg-emerald-500 focus:!ring-emerald-400"
                                    required
                                />

                                <span class="ms-3 text-sm leading-6 text-slate-400">
                                    Eu concordo com os
                                    <a
                                        target="_blank"
                                        :href="route('terms.show')"
                                        class="font-medium text-emerald-400 transition hover:text-emerald-300"
                                    >
                                        Termos de Serviço
                                    </a>
                                    e com a
                                    <a
                                        target="_blank"
                                        :href="route('policy.show')"
                                        class="font-medium text-emerald-400 transition hover:text-emerald-300"
                                    >
                                        Política de Privacidade
                                    </a>.
                                </span>
                            </label>

                            <InputError
                                class="mt-2"
                                :message="form.errors.terms"
                            />
                        </div>

                        <!-- Actions -->
                        <div class="pt-2">
                            <PrimaryButton
                                class="!w-full !justify-center !rounded-xl !border-0 !bg-emerald-500 !px-6 !py-3 !font-semibold !text-slate-950 !shadow-lg !shadow-emerald-500/20 transition hover:!bg-emerald-400"
                                :class="{ 'opacity-50': form.processing }"
                                :disabled="form.processing"
                            >
                                <span v-if="!form.processing">
                                    Criar minha conta
                                </span>

                                <span v-else>
                                    Criando conta...
                                </span>
                            </PrimaryButton>
                        </div>
                    </form>

                    <!-- Login -->
                    <div class="mt-6 border-t border-white/10 pt-6 text-center">
                        <p class="text-sm text-slate-500">
                            Já possui uma conta?
                        </p>

                        <Link
                            :href="route('login')"
                            class="mt-2 inline-block text-sm font-semibold text-emerald-400 transition hover:text-emerald-300"
                        >
                            Entrar no FinanceStudio
                        </Link>
                    </div>
                </AuthenticationCard>

                <!-- Footer -->
                <div class="mt-6 text-center">
                    <Link
                        href="/"
                        class="text-sm text-slate-500 transition hover:text-slate-300"
                    >
                        ← Voltar para o início
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>