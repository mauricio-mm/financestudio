<script setup>
import { Link } from '@inertiajs/vue3';
import {
    ChartNoAxesCombined,
    LayoutDashboard,
    ReceiptText,
    Users,
} from '@lucide/vue';

const props = defineProps({
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
        icon: LayoutDashboard,
    },
    {
        name: 'Pessoas/Empresas',
        href: '/pessoas-empresas',
        routeName: 'people.*',
        icon: Users,
    },
    {
        name: 'Contas',
        href: '/contas',
        routeName: 'financial-entries.*',
        icon: ReceiptText,
    },
    {
        name: 'Relatórios',
        href: '/relatorios',
        routeName: 'reports.*',
        icon: ChartNoAxesCombined,
    },
];

const closeSidebar = () => {
    emit('close');
};
</script>

<template>
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-72 flex-col border-r border-white/10 bg-slate-950 text-white shadow-2xl transition-transform duration-200 md:translate-x-0"
        :class="show ? 'translate-x-0' : '-translate-x-full'"
    >
        <!-- Header -->
        <div class="flex h-16 items-center border-b border-white/10 px-5">
            <Link
                :href="route('dashboard')"
                class="flex items-center gap-3 rounded-lg transition hover:opacity-90"
                @click="closeSidebar"
            >
                <span
                    class="flex size-9 items-center justify-center rounded-xl bg-emerald-500 text-xs font-bold tracking-tight text-slate-950 shadow-lg shadow-emerald-500/20"
                >
                    FS
                </span>

                <div>
                    <span class="block text-sm font-bold tracking-tight">
                        FinanceStudio
                    </span>

                    <span class="block text-[10px] uppercase tracking-[0.18em] text-slate-500">
                        Gestão financeira
                    </span>
                </div>
            </Link>
        </div>

        <!-- User -->
        <div class="border-b border-white/10 px-5 py-5">
            <div
                class="flex items-center gap-3 rounded-xl border border-white/5 bg-white/[0.03] p-3"
            >
                <img
                    v-if="$page.props.jetstream.managesProfilePhotos"
                    class="size-10 rounded-full object-cover ring-2 ring-emerald-400/20"
                    :src="$page.props.auth.user.profile_photo_url"
                    :alt="$page.props.auth.user.name"
                />

                <div
                    v-else
                    class="flex size-10 shrink-0 items-center justify-center rounded-full bg-emerald-500/10 text-sm font-semibold uppercase text-emerald-400 ring-1 ring-emerald-400/20"
                >
                    {{ ($page.props.auth.user.name || 'U').slice(0, 1) }}
                </div>

                <div class="min-w-0">
                    <p class="truncate text-sm font-semibold text-white">
                        {{ $page.props.auth.user.name }}
                    </p>

                    <p class="mt-0.5 truncate text-xs text-slate-500">
                        Área financeira
                    </p>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1.5 px-3 py-5">
            <p
                class="mb-3 px-3 text-[10px] font-semibold uppercase tracking-[0.18em] text-slate-600"
            >
                Menu principal
            </p>

            <Link
                v-for="item in navigationItems"
                :key="item.name"
                :href="item.href"
                class="group flex items-center gap-3 rounded-xl px-3.5 py-3 text-sm font-medium transition-all duration-200"
                :class="
                    route().current(item.routeName)
                        ? 'bg-emerald-500/10 text-emerald-400 ring-1 ring-inset ring-emerald-400/10'
                        : 'text-slate-400 hover:bg-white/[0.04] hover:text-white'
                "
                @click="closeSidebar"
            >
                <component
                    :is="item.icon"
                    class="size-5 shrink-0 transition-colors"
                    :class="
                        route().current(item.routeName)
                            ? 'text-emerald-400'
                            : 'text-slate-500 group-hover:text-slate-300'
                    "
                    stroke-width="1.8"
                />

                <span>{{ item.name }}</span>

                <span
                    v-if="route().current(item.routeName)"
                    class="ml-auto size-1.5 rounded-full bg-emerald-400 shadow-sm shadow-emerald-400/50"
                ></span>
            </Link>
        </nav>

        <!-- Footer card -->
        <div class="border-t border-white/10 p-4">
            <div
                class="rounded-xl border border-emerald-400/10 bg-emerald-500/[0.04] p-4"
            >
                <div class="flex items-center gap-2">
                    <div
                        class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400"
                    >
                        <svg
                            class="size-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343 3-3-1.343-3-3-3z"
                            />
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06-1.4 1.4-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V19h-2v-.09a1.65 1.65 0 00-1-1.51 1.65 1.65 0 00-1.82.33l-.06.06-1.4-1.4.06-.06A1.65 1.65 0 008.63 15a1.65 1.65 0 00-1.51-1H7v-2h.09a1.65 1.65 0 001.51-1 1.65 1.65 0 00-.33-1.82l-.06-.06 1.4-1.4.06.06a1.65 1.65 0 001.82.33h.01a1.65 1.65 0 001-1.51V6h2v.09a1.65 1.65 0 001 1.51h.01a1.65 1.65 0 001.82-.33l.06-.06 1.4 1.4-.06.06a1.65 1.65 0 00-.33 1.82v.01a1.65 1.65 0 001.51 1H19v2h-.09a1.65 1.65 0 00-1.51 1z"
                            />
                        </svg>
                    </div>

                    <p class="text-[10px] font-semibold uppercase tracking-[0.16em] text-emerald-400/70">
                        FinanceStudio
                    </p>
                </div>

                <p class="mt-3 text-sm font-semibold text-white">
                    Controle financeiro
                </p>

                <p class="mt-1 text-xs leading-5 text-slate-500">
                    Contas a pagar, receber e relatórios gerenciais.
                </p>
            </div>
        </div>
    </aside>
</template>