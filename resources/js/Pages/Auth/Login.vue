<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Entrar" />

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

        <div class="relative flex min-h-screen items-center justify-center px-6 py-12">
            <div class="w-full max-w-md">

                <!-- Logo / Brand -->
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
                        Bem-vindo de volta
                    </h1>

                    <p class="mt-2 text-sm text-slate-400">
                        Entre na sua conta para acessar o FinanceStudio.
                    </p>
                </div>

                <!-- Card -->
                <AuthenticationCard>
                    <template #logo>
                        <div></div>
                    </template>

                    <!-- Status -->
                    <div
                        v-if="status"
                        class="mb-5 rounded-lg border border-emerald-400/20 bg-emerald-400/10 px-4 py-3 text-sm font-medium text-emerald-300"
                    >
                        {{ status }}
                    </div>

                    <form @submit.prevent="submit" class="space-y-5">
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
                                autofocus
                                autocomplete="username"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.email"
                            />
                        </div>

                        <!-- Password -->
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
                                autocomplete="current-password"
                            />

                            <InputError
                                class="mt-2"
                                :message="form.errors.password"
                            />
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center justify-between">
                            <label class="flex items-center">
                                <Checkbox
                                    v-model:checked="form.remember"
                                    name="remember"
                                    class="!border-slate-600 !bg-slate-900 checked:!border-emerald-500 checked:!bg-emerald-500 focus:!ring-emerald-400"
                                />

                                <span class="ms-2 text-sm text-slate-400">
                                    Lembrar de mim
                                </span>
                            </label>

                            <Link
                                v-if="canResetPassword"
                                :href="route('password.request')"
                                class="rounded-md text-sm font-medium text-emerald-400 transition hover:text-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-400"
                            >
                                Esqueceu a senha?
                            </Link>
                        </div>

                        <!-- Submit -->
                        <PrimaryButton
                            class="!mt-6 !w-full !justify-center !rounded-xl !border-0 !bg-emerald-500 !px-6 !py-3 !font-semibold !text-slate-950 !shadow-lg !shadow-emerald-500/20 transition hover:!bg-emerald-400"
                            :class="{ 'opacity-50': form.processing }"
                            :disabled="form.processing"
                        >
                            <span v-if="!form.processing">
                                Entrar no FinanceStudio
                            </span>

                            <span v-else>
                                Entrando...
                            </span>
                        </PrimaryButton>
                    </form>

                    <!-- Register -->
                    <div class="mt-6 border-t border-white/10 pt-6 text-center">
                        <p class="text-sm text-slate-500">
                            Ainda não possui uma conta?
                        </p>

                        <Link
                            v-if="$page.props.canRegister"
                            :href="route('register')"
                            class="mt-2 inline-block text-sm font-semibold text-emerald-400 transition hover:text-emerald-300"
                        >
                            Criar uma conta
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