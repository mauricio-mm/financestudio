<script setup>
import { Link } from '@inertiajs/vue3';
import { ChartNoAxesCombined, LayoutDashboard, ReceiptText, Users } from '@lucide/vue';

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
        name: 'Relatorios',
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
            <Link
                v-for="item in navigationItems"
                :key="item.name"
                :href="item.href"
                class="group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-medium transition"
                :class="route().current(item.routeName)
                    ? 'bg-white text-slate-900 shadow-sm'
                    : 'text-white/70 hover:bg-white/10 hover:text-white'"
                @click="closeSidebar"
            >
                <component :is="item.icon" class="size-5 shrink-0" stroke-width="1.8" />
                <span>{{ item.name }}</span>
            </Link>
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