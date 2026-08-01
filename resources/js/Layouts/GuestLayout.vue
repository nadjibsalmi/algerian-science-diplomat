<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Brand panel (desktop only) -->
        <aside
            class="hidden w-1/2 flex-col items-center justify-center bg-[#0B1F3A] px-12 text-white lg:flex"
            aria-hidden="true"
        >
            <div class="max-w-sm text-center">
                <p class="text-5xl font-bold tracking-tight text-[#C9A227]">ASD</p>
                <p class="mt-3 text-xl font-semibold">Algerian Science Diplomat</p>
                <p class="mt-4 text-sm leading-relaxed text-white/60">
                    Relier la science et la diplomatie<br />pour une Algérie meilleure.
                </p>
            </div>
        </aside>

        <!-- Form panel -->
        <main class="flex flex-1 flex-col items-center justify-center bg-[#F4F6F8] px-6 py-12">
            <!-- Mobile logo -->
            <Link
                :href="route('home')"
                class="mb-8 block text-center lg:hidden"
                aria-label="Retour à l'accueil"
            >
                <span class="text-3xl font-bold text-[#0B1F3A]">ASD</span>
            </Link>

            <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-8 shadow-sm">
                <slot />
            </div>

            <p class="mt-6 text-center text-xs text-gray-400">
                &copy; {{ new Date().getFullYear() }} Algerian Science Diplomat
            </p>
        </main>
    </div>
</template>
