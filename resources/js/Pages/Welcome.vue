<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="FinanceStudio" />

    <div class="min-h-screen bg-slate-950 text-white">
        <!-- Background -->
        <div class="pointer-events-none absolute inset-0 overflow-hidden">
            <div
                class="absolute -left-40 -top-40 h-96 w-96 rounded-full bg-emerald-500/10 blur-3xl"
            ></div>

            <div
                class="absolute right-0 top-1/3 h-96 w-96 rounded-full bg-blue-500/10 blur-3xl"
            ></div>

            <div
                class="absolute bottom-0 left-1/3 h-80 w-80 rounded-full bg-purple-500/10 blur-3xl"
            ></div>
        </div>

        <div class="relative">
            <!-- Navbar -->
            <header class="border-b border-white/10 bg-slate-950/70 backdrop-blur">
                <div
                    class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5 lg:px-8"
                >
                    <div class="flex items-center gap-3">
                        <div
                            class="flex h-10 w-10 items-center justify-center rounded-xl bg-emerald-500"
                        >
                            <svg
                                class="h-5 w-5 text-slate-950"
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

                        <div>
                            <div class="text-lg font-bold tracking-tight">
                                FinanceStudio
                            </div>

                            <div class="text-xs text-slate-400">
                                Gestão financeira
                            </div>
                        </div>
                    </div>

                    <nav v-if="canLogin" class="flex items-center gap-2">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                            >
                                Dashboard
                            </Link>
                        </template>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-lg px-4 py-2 text-sm font-medium text-slate-300 transition hover:bg-white/5 hover:text-white"
                            >
                                Entrar
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-emerald-500 px-4 py-2 text-sm font-semibold text-slate-950 transition hover:bg-emerald-400"
                            >
                                Criar conta
                            </Link>
                        </template>
                    </nav>
                </div>
            </header>

            <!-- Hero -->
            <main>
                <section class="mx-auto max-w-7xl px-6 pb-20 pt-20 lg:px-8 lg:pb-28 lg:pt-28">
                    <div class="grid items-center gap-16 lg:grid-cols-2">
                        <!-- Left -->
                        <div>
                            <div
                                class="mb-6 inline-flex items-center gap-2 rounded-full border border-emerald-400/20 bg-emerald-400/10 px-3 py-1 text-sm text-emerald-300"
                            >
                                <span
                                    class="h-2 w-2 rounded-full bg-emerald-400"
                                ></span>

                                Controle financeiro simplificado
                            </div>

                            <h1
                                class="max-w-3xl text-4xl font-bold tracking-tight text-white sm:text-5xl lg:text-6xl"
                            >
                                Tenha controle sobre
                                <span class="text-emerald-400">
                                    suas finanças.
                                </span>
                            </h1>

                            <p
                                class="mt-6 max-w-2xl text-lg leading-8 text-slate-400"
                            >
                                Gerencie contas a pagar e receber, acompanhe
                                seus clientes e fornecedores e tenha uma visão
                                clara da saúde financeira da sua empresa.
                            </p>

                            <div class="mt-8 flex flex-wrap gap-4">
                                <Link
                                    v-if="canLogin"
                                    :href="
                                        $page.props.auth.user
                                            ? route('dashboard')
                                            : route('login')
                                    "
                                    class="inline-flex items-center gap-2 rounded-xl bg-emerald-500 px-6 py-3.5 font-semibold text-slate-950 shadow-lg shadow-emerald-500/20 transition hover:bg-emerald-400"
                                >
                                    Acessar sistema

                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 12h14M13 6l6 6-6 6"
                                        />
                                    </svg>
                                </Link>

                                <a
                                    href="#recursos"
                                    class="rounded-xl border border-white/10 px-6 py-3.5 font-semibold text-slate-300 transition hover:bg-white/5 hover:text-white"
                                >
                                    Conhecer recursos
                                </a>
                            </div>

                            <div class="mt-10 flex flex-wrap gap-x-8 gap-y-3 text-sm text-slate-400">
                                <div class="flex items-center gap-2">
                                    <svg
                                        class="h-4 w-4 text-emerald-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>

                                    Contas a pagar
                                </div>

                                <div class="flex items-center gap-2">
                                    <svg
                                        class="h-4 w-4 text-emerald-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>

                                    Contas a receber
                                </div>

                                <div class="flex items-center gap-2">
                                    <svg
                                        class="h-4 w-4 text-emerald-400"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M5 13l4 4L19 7"
                                        />
                                    </svg>

                                    Dashboard gerencial
                                </div>
                            </div>
                        </div>

                        <!-- Dashboard Preview -->
                        <div class="relative">
                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.03] p-4 shadow-2xl backdrop-blur"
                            >
                                <!-- Browser top -->
                                <div
                                    class="mb-4 flex items-center justify-between border-b border-white/10 pb-4"
                                >
                                    <div class="flex gap-2">
                                        <span class="h-2.5 w-2.5 rounded-full bg-red-400/80"></span>
                                        <span class="h-2.5 w-2.5 rounded-full bg-yellow-400/80"></span>
                                        <span class="h-2.5 w-2.5 rounded-full bg-green-400/80"></span>
                                    </div>

                                    <div class="rounded-md bg-white/5 px-3 py-1 text-xs text-slate-500">
                                        FinanceStudio
                                    </div>

                                    <div class="w-10"></div>
                                </div>

                                <!-- Fake dashboard -->
                                <div class="rounded-xl bg-slate-900 p-5">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="text-xs text-slate-500">
                                                Visão geral
                                            </p>

                                            <h2 class="mt-1 text-xl font-semibold">
                                                Dashboard financeiro
                                            </h2>
                                        </div>

                                        <div
                                            class="rounded-lg bg-emerald-500/10 px-3 py-1.5 text-xs font-medium text-emerald-400"
                                        >
                                            Este mês
                                        </div>
                                    </div>

                                    <div class="mt-6 grid grid-cols-2 gap-3">
                                        <div
                                            class="rounded-xl border border-white/5 bg-white/[0.03] p-4"
                                        >
                                            <p class="text-xs text-slate-500">
                                                A receber
                                            </p>

                                            <p class="mt-2 text-lg font-semibold">
                                                R$ 42.500
                                            </p>
                                        </div>

                                        <div
                                            class="rounded-xl border border-white/5 bg-white/[0.03] p-4"
                                        >
                                            <p class="text-xs text-slate-500">
                                                A pagar
                                            </p>

                                            <p class="mt-2 text-lg font-semibold">
                                                R$ 18.750
                                            </p>
                                        </div>

                                        <div
                                            class="rounded-xl border border-white/5 bg-white/[0.03] p-4"
                                        >
                                            <p class="text-xs text-slate-500">
                                                Recebido
                                            </p>

                                            <p class="mt-2 text-lg font-semibold text-emerald-400">
                                                R$ 31.200
                                            </p>
                                        </div>

                                        <div
                                            class="rounded-xl border border-white/5 bg-white/[0.03] p-4"
                                        >
                                            <p class="text-xs text-slate-500">
                                                Saldo previsto
                                            </p>

                                            <p class="mt-2 text-lg font-semibold text-blue-400">
                                                R$ 23.750
                                            </p>
                                        </div>
                                    </div>

                                    <!-- Chart -->
                                    <div class="mt-4 rounded-xl border border-white/5 bg-white/[0.03] p-4">
                                        <div class="flex items-center justify-between">
                                            <p class="text-xs text-slate-500">
                                                Fluxo financeiro
                                            </p>

                                            <span class="text-xs text-emerald-400">
                                                +12.8%
                                            </span>
                                        </div>

                                        <div class="mt-6 flex h-32 items-end gap-2">
                                            <div class="h-[35%] flex-1 rounded-t bg-emerald-500/30"></div>
                                            <div class="h-[48%] flex-1 rounded-t bg-emerald-500/40"></div>
                                            <div class="h-[42%] flex-1 rounded-t bg-emerald-500/40"></div>
                                            <div class="h-[62%] flex-1 rounded-t bg-emerald-500/50"></div>
                                            <div class="h-[55%] flex-1 rounded-t bg-emerald-500/50"></div>
                                            <div class="h-[78%] flex-1 rounded-t bg-emerald-500/60"></div>
                                            <div class="h-[90%] flex-1 rounded-t bg-emerald-500"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="absolute -bottom-6 -right-6 -z-10 h-40 w-40 rounded-full bg-emerald-500/20 blur-3xl"
                            ></div>
                        </div>
                    </div>
                </section>

                <!-- Features -->
                <section
                    id="recursos"
                    class="border-y border-white/10 bg-white/[0.02]"
                >
                    <div
                        class="mx-auto max-w-7xl px-6 py-20 lg:px-8"
                    >
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold uppercase tracking-widest text-emerald-400">
                                Recursos
                            </p>

                            <h2 class="mt-3 text-3xl font-bold tracking-tight">
                                Tudo que você precisa para acompanhar suas finanças
                            </h2>

                            <p class="mt-4 text-slate-400">
                                Uma visão simples e centralizada das movimentações
                                financeiras da sua empresa.
                            </p>
                        </div>

                        <div class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 transition hover:border-emerald-400/30 hover:bg-white/[0.05]"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-emerald-500/10 text-emerald-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 10h18M7 15h2m2 0h2m2 0h2M5 5h14a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-5 font-semibold">
                                    Contas a pagar
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    Controle fornecedores, vencimentos e pagamentos.
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 transition hover:border-emerald-400/30 hover:bg-white/[0.05]"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-blue-500/10 text-blue-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3 1.343 3 3-1.343 3-3 3m0-16a9 9 0 110 18 9 9 0 010-18z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-5 font-semibold">
                                    Contas a receber
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    Acompanhe clientes, recebimentos e inadimplência.
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 transition hover:border-emerald-400/30 hover:bg-white/[0.05]"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-purple-500/10 text-purple-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M3 3v18h18M7 16l4-5 3 3 5-7"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-5 font-semibold">
                                    Dashboard
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    Visualize sua posição financeira em poucos segundos.
                                </p>
                            </div>

                            <div
                                class="rounded-2xl border border-white/10 bg-white/[0.03] p-6 transition hover:border-emerald-400/30 hover:bg-white/[0.05]"
                            >
                                <div
                                    class="flex h-11 w-11 items-center justify-center rounded-xl bg-orange-500/10 text-orange-400"
                                >
                                    <svg
                                        class="h-5 w-5"
                                        fill="none"
                                        viewBox="0 0 24 24"
                                        stroke="currentColor"
                                        stroke-width="2"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M9 17v-6m3 6V7m3 10v-3m3 5H6a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2v12a2 2 0 01-2 2z"
                                        />
                                    </svg>
                                </div>

                                <h3 class="mt-5 font-semibold">
                                    Relatórios
                                </h3>

                                <p class="mt-2 text-sm leading-6 text-slate-400">
                                    Filtre e analise suas movimentações financeiras.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- CTA -->
                <section class="mx-auto max-w-7xl px-6 py-20 lg:px-8">
                    <div
                        class="overflow-hidden rounded-3xl border border-emerald-400/20 bg-gradient-to-br from-emerald-500/10 via-white/[0.03] to-blue-500/10 px-6 py-12 sm:px-10"
                    >
                        <div
                            class="flex flex-col items-start justify-between gap-8 md:flex-row md:items-center"
                        >
                            <div class="max-w-2xl">
                                <h2 class="text-2xl font-bold sm:text-3xl">
                                    Tenha uma visão mais clara do seu negócio.
                                </h2>

                                <p class="mt-3 text-slate-400">
                                    Organize suas contas, acompanhe seus resultados
                                    e tome decisões com mais segurança.
                                </p>
                            </div>

                            <Link
                                v-if="canLogin"
                                :href="
                                    $page.props.auth.user
                                        ? route('dashboard')
                                        : route('login')
                                "
                                class="shrink-0 rounded-xl bg-emerald-500 px-6 py-3.5 font-semibold text-slate-950 transition hover:bg-emerald-400"
                            >
                                Acessar FinanceStudio
                            </Link>
                        </div>
                    </div>
                </section>
            </main>

            <!-- Footer -->
            <footer class="border-t border-white/10">
                <div
                    class="mx-auto flex max-w-7xl flex-col gap-4 px-6 py-8 text-sm text-slate-500 sm:flex-row sm:items-center sm:justify-between lg:px-8"
                >
                    <p>
                        © {{ new Date().getFullYear() }} FinanceStudio
                    </p>

                    <div class="flex items-center gap-4">
                        <span>Gestão financeira simplificada</span>

                        <span class="h-1 w-1 rounded-full bg-slate-700"></span>

                        <span>Laravel + Vue + Inertia</span>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>