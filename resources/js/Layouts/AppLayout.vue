<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
    title: String,
});

const showingSidebar = ref(false);
const dropdownContentClasses = ['py-2', 'bg-white'];

const navigationItems = [
    {
        name: 'Dashboard',
        href: '/dashboard',
        routeName: 'dashboard',
        icon: [
            'M3 13h8V3H3v10Z',
            'M13 21h8v-8h-8v8Z',
            'M13 3v8h8V3h-8Z',
            'M3 21h8v-6H3v6Z',
        ],
    },
    {
        name: 'Pessoas',
        disabled: true,
        icon: [
            'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2',
            'M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Z',
            'M22 21v-2a4 4 0 0 0-3-3.87',
            'M16 3.13a4 4 0 0 1 0 7.75',
        ],
    },
    {
        name: 'A Receber',
        disabled: true,
        icon: [
            'M12 2v20',
            'M17 5H9.5a3.5 3.5 0 0 0 0 7H14a3.5 3.5 0 0 1 0 7H6',
        ],
    },
    {
        name: 'A Pagar',
        disabled: true,
        icon: [
            'M3 6h18',
            'M7 6V4a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v2',
            'M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6',
            'M10 11v6',
            'M14 11v6',
        ],
    },
    {
        name: 'Relatorios',
        disabled: true,
        icon: [
            'M3 3v18h18',
            'M7 15l4-4 3 3 5-7',
        ],
    },
];

const closeSidebar = () => {
    showingSidebar.value = false;
};

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <div>
        <Head :title="title" />

        <Banner />

        <div class="min-h-screen bg-[#f4f3ef] text-slate-700">
            <div
                v-show="showingSidebar"
                class="fixed inset-0 z-40 bg-black/40 md:hidden"
                @click="closeSidebar"
            />

            <aside
                class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#202020] text-white shadow-2xl transition-transform duration-200 md:translate-x-0"
                :class="showingSidebar ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="flex h-16 items-center border-b border-white/10 px-6">
                    <Link :href="route('dashboard')" class="flex items-center gap-3" @click="closeSidebar">
                        <span class="flex size-9 items-center justify-center rounded-lg bg-emerald-500 text-sm font-bold text-white">
                            FS
                        </span>
                        <span class="text-sm font-semibold uppercase tracking-[0.18em]">
                            FinanceStudio
                        </span>
                    </Link>
                </div>

                <div class="border-b border-white/10 px-6 py-5">
                    <div class="flex items-center gap-3">
                        <img
                            v-if="$page.props.jetstream.managesProfilePhotos"
                            class="size-10 rounded-full object-cover ring-2 ring-white/20"
                            :src="$page.props.auth.user.profile_photo_url"
                            :alt="$page.props.auth.user.name"
                        >
                        <div
                            v-else
                            class="flex size-10 items-center justify-center rounded-full bg-white/10 text-sm font-semibold uppercase"
                        >
                            {{ ($page.props.auth.user.name || 'U').slice(0, 1) }}
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-semibold">
                                {{ $page.props.auth.user.name }}
                            </p>
                            <p class="truncate text-xs text-white/50">
                                Area financeira
                            </p>
                        </div>
                    </div>
                </div>

                <nav class="flex-1 space-y-1 px-4 py-5">
                    <template v-for="item in navigationItems" :key="item.name">
                        <Link
                            v-if="! item.disabled"
                            :href="item.href"
                            class="group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
                            :class="route().current(item.routeName)
                                ? 'bg-white text-slate-900 shadow-sm'
                                : 'text-white/70 hover:bg-white/10 hover:text-white'"
                            @click="closeSidebar"
                        >
                            <svg
                                class="size-5 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.8"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path v-for="path in item.icon" :key="path" :d="path" />
                            </svg>
                            <span>{{ item.name }}</span>
                        </Link>

                        <button
                            v-else
                            type="button"
                            class="flex w-full items-center justify-between gap-3 rounded-lg px-4 py-3 text-sm font-medium text-white/40 transition hover:bg-white/5 hover:text-white/70"
                            title="Sera ativado quando criarmos este modulo"
                        >
                            <span class="flex min-w-0 items-center gap-3">
                                <svg
                                    class="size-5 shrink-0"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    aria-hidden="true"
                                >
                                    <path v-for="path in item.icon" :key="path" :d="path" />
                                </svg>
                                <span class="truncate">{{ item.name }}</span>
                            </span>
                            <span class="rounded bg-white/10 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-white/40">
                                Breve
                            </span>
                        </button>
                    </template>
                </nav>

                <div class="border-t border-white/10 p-4">
                    <div class="rounded-lg bg-white/10 p-4">
                        <p class="text-xs uppercase tracking-[0.18em] text-white/40">
                            Projeto
                        </p>
                        <p class="mt-2 text-sm font-semibold text-white">
                            Controle financeiro
                        </p>
                        <p class="mt-1 text-xs leading-5 text-white/50">
                            Contas a pagar, receber e relatorios gerenciais.
                        </p>
                    </div>
                </div>
            </aside>

            <div class="md:pl-72">
                <header class="sticky top-0 z-30 border-b border-black/5 bg-[#f4f3ef]/95 backdrop-blur">
                    <div class="flex min-h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                        <button
                            type="button"
                            class="inline-flex size-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm md:hidden"
                            @click="showingSidebar = true"
                        >
                            <span class="sr-only">Abrir menu</span>
                            <svg
                                class="size-5"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <path d="M4 6h16" />
                                <path d="M4 12h16" />
                                <path d="M4 18h16" />
                            </svg>
                        </button>

                        <div class="min-w-0 flex-1">
                            <slot name="header">
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">
                                        Painel
                                    </p>
                                    <h1 class="text-xl font-semibold text-slate-900">
                                        {{ title }}
                                    </h1>
                                </div>
                            </slot>
                        </div>

                        <div class="hidden w-full max-w-xs items-center rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-400 shadow-sm lg:flex">
                            <svg
                                class="me-2 size-4 shrink-0"
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="2"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                aria-hidden="true"
                            >
                                <circle cx="11" cy="11" r="8" />
                                <path d="m21 21-4.3-4.3" />
                            </svg>
                            <span>Buscar...</span>
                        </div>

                        <Dropdown align="right" width="48" :content-classes="dropdownContentClasses">
                            <template #trigger>
                                <button
                                    type="button"
                                    class="flex items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm text-slate-700 shadow-sm transition hover:border-slate-300"
                                >
                                    <img
                                        v-if="$page.props.jetstream.managesProfilePhotos"
                                        class="size-8 rounded-full object-cover"
                                        :src="$page.props.auth.user.profile_photo_url"
                                        :alt="$page.props.auth.user.name"
                                    >
                                    <span
                                        v-else
                                        class="flex size-8 items-center justify-center rounded-full bg-emerald-50 text-xs font-semibold uppercase text-emerald-700"
                                    >
                                        {{ ($page.props.auth.user.name || 'U').slice(0, 1) }}
                                    </span>
                                    <span class="hidden max-w-32 truncate font-medium sm:block">
                                        {{ $page.props.auth.user.name }}
                                    </span>
                                    <svg
                                        class="size-4 text-slate-400"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        aria-hidden="true"
                                    >
                                        <path d="m6 9 6 6 6-6" />
                                    </svg>
                                </button>
                            </template>

                            <template #content>
                                <div class="px-4 py-2">
                                    <p class="truncate text-sm font-semibold text-slate-800">
                                        {{ $page.props.auth.user.name }}
                                    </p>
                                    <p class="truncate text-xs text-slate-500">
                                        {{ $page.props.auth.user.email }}
                                    </p>
                                </div>

                                <div class="my-2 border-t border-slate-100" />

                                <DropdownLink :href="route('profile.show')">
                                    Perfil
                                </DropdownLink>

                                <DropdownLink
                                    v-if="$page.props.jetstream.hasApiFeatures"
                                    :href="route('api-tokens.index')"
                                >
                                    API Tokens
                                </DropdownLink>

                                <div class="my-2 border-t border-slate-100" />

                                <form @submit.prevent="logout">
                                    <DropdownLink as="button">
                                        Sair
                                    </DropdownLink>
                                </form>
                            </template>
                        </Dropdown>
                    </div>
                </header>

                <main class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
