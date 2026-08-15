<script setup>
import { router } from '@inertiajs/vue3';
import { ChevronDown, Menu, Search } from '@lucide/vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

defineProps({
    title: String,
});

const emit = defineEmits(['open-sidebar']);
const dropdownContentClasses = ['py-2', 'bg-white'];

const logout = () => {
    router.post(route('logout'));
};
</script>

<template>
    <header class="sticky top-0 z-30 border-b border-black/5 bg-[#f4f3ef]/95 backdrop-blur">
        <div class="flex min-h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
            <button
                type="button"
                class="inline-flex size-10 items-center justify-center rounded-lg border border-slate-200 bg-white text-slate-600 shadow-sm md:hidden"
                @click="emit('open-sidebar')"
            >
                <span class="sr-only">Abrir menu</span>
                <Menu class="size-5" stroke-width="2" aria-hidden="true" />
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
                <Search class="me-2 size-4 shrink-0" stroke-width="2" aria-hidden="true" />
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
                        <ChevronDown class="size-4 text-slate-400" stroke-width="2" aria-hidden="true" />
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
</template>