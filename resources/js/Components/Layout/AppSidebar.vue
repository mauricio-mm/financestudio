<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

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
        name: 'Pessoas/Empresas',
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
    emit('close');
};
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col bg-[#202020] text-white shadow-2xl transition-transform duration-200 md:translate-x-0"
        :class="show ? 'translate-x-0' : '-translate-x-full'"
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
</template>
