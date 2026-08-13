<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import Banner from '@/Components/Banner.vue';
import AppSidebar from '@/Components/Layout/AppSidebar.vue';
import AppTopbar from '@/Components/Layout/AppTopbar.vue';

defineProps({
    title: String,
});

const showingSidebar = ref(false);

const closeSidebar = () => {
    showingSidebar.value = false;
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

            <AppSidebar :show="showingSidebar" @close="closeSidebar" />

            <div class="md:pl-72">
                <AppTopbar :title="title" @open-sidebar="showingSidebar = true">
                    <template v-if="$slots.header" #header>
                        <slot name="header" />
                    </template>
                </AppTopbar>

                <main class="mx-auto w-full max-w-[1600px] px-4 py-6 sm:px-6 lg:px-8">
                    <slot />
                </main>
            </div>
        </div>
    </div>
</template>
