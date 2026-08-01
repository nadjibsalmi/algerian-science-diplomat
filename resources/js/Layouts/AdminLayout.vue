<script setup lang="ts">
import { ref } from 'vue';
import { Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

const mobileOpen = ref(false);

const logoutForm = useForm({});

function logout() {
    logoutForm.post(route('logout'));
}

const nav = [
    { label: 'Vue d\'ensemble', href: route('admin.overview') },
    { label: 'Utilisateurs', href: route('admin.users') },
    { label: 'CMS', href: route('cms.pages') },
    { label: 'Analytique', href: route('admin.analytics') },
    { label: 'Paramètres', href: route('admin.settings') },
    { label: 'Journaux', href: route('admin.audit-logs') },
];
</script>

<template>
    <div class="flex min-h-screen bg-[#F4F6F8]">
        <!-- Sidebar -->
        <aside
            :class="[
                'fixed inset-y-0 start-0 z-30 w-60 border-e border-gray-200 bg-[#0B1F3A] text-white transition-transform lg:static lg:translate-x-0',
                mobileOpen ? 'translate-x-0' : '-translate-x-full',
            ]"
        >
            <div class="flex h-16 items-center gap-2 border-b border-white/10 px-5">
                <Link :href="route('home')" class="text-xl font-bold text-[#C9A227]">ASD</Link>
                <span class="rounded-full bg-white/10 px-2 py-0.5 text-xs text-white/70">Admin</span>
            </div>

            <nav class="flex flex-col gap-0.5 p-3" aria-label="Navigation admin">
                <Link
                    v-for="item in nav"
                    :key="item.href"
                    :href="item.href"
                    class="rounded-lg px-3 py-2 text-sm text-white/70 transition hover:bg-white/10 hover:text-white"
                >
                    {{ item.label }}
                </Link>
            </nav>

            <div class="absolute bottom-4 start-0 w-full px-3">
                <button
                    type="button"
                    class="w-full rounded-lg px-3 py-2 text-start text-sm text-red-400 transition hover:bg-white/10"
                    @click="logout"
                >
                    Se déconnecter
                </button>
            </div>
        </aside>

        <!-- Overlay -->
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-20 bg-black/50 lg:hidden"
            aria-hidden="true"
            @click="mobileOpen = false"
        />

        <div class="flex flex-1 flex-col overflow-hidden">
            <header class="flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 lg:px-6">
                <button
                    type="button"
                    class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 lg:hidden"
                    :aria-expanded="mobileOpen"
                    aria-label="Ouvrir le menu"
                    @click="mobileOpen = !mobileOpen"
                >
                    <span class="block h-px w-5 bg-current" />
                    <span class="mt-1.5 block h-px w-5 bg-current" />
                    <span class="mt-1.5 block h-px w-5 bg-current" />
                </button>
                <span class="ms-auto text-xs text-gray-400">Administration</span>
            </header>

            <slot name="flash" />

            <main class="flex-1 overflow-y-auto p-4 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
