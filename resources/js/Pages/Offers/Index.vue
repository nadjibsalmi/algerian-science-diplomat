<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import type { PaginatedOffers } from '@/types/models';

defineProps<{
    offers: PaginatedOffers;
}>();
</script>

<template>
    <Head title="Opportunités" />

    <div class="min-h-screen bg-[#F4F6F8]">
        <header class="bg-[#0B1F3A] text-white">
            <div class="mx-auto max-w-6xl px-6 py-8">
                <h1 class="text-3xl font-semibold">Opportunités internationales</h1>
                <p class="mt-2 text-white/70">
                    Bourses, stages, postes de recherche et programmes proposés par les
                    ambassades et organisations partenaires en Algérie.
                </p>
            </div>
        </header>

        <main class="mx-auto max-w-6xl px-6 py-10">
            <!-- Empty state -->
            <div
                v-if="offers.data.length === 0"
                class="rounded-lg border border-dashed border-gray-300 py-16 text-center text-gray-500"
                role="status"
                aria-label="Aucune opportunité disponible"
            >
                Aucune opportunité publiée pour le moment.
            </div>

            <!-- Offers grid -->
            <ul v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3" role="list" aria-label="Liste des opportunités">
                <li
                    v-for="offer in offers.data"
                    :key="offer.id"
                    class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm transition hover:shadow-md"
                >
                    <span class="inline-block rounded-full bg-[#2F6B4F]/10 px-2.5 py-0.5 text-xs font-medium text-[#2F6B4F]">
                        {{ offer.offer_type }}
                    </span>
                    <h2 class="mt-3 text-lg font-medium text-[#0B1F3A]">
                        {{ offer.title }}
                    </h2>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ offer.embassy_name }} · {{ offer.country }}<span v-if="offer.city">, {{ offer.city }}</span>
                    </p>
                    <p v-if="offer.level" class="mt-1 text-xs text-gray-400 capitalize">
                        {{ offer.level }}
                    </p>
                    <p v-if="offer.deadline" class="mt-3 text-xs text-gray-400">
                        Date limite :
                        <time :datetime="offer.deadline">
                            {{ new Date(offer.deadline).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                        </time>
                    </p>
                </li>
            </ul>

            <!-- Pagination — audit fix: pagination data was returned by the
                 controller and typed in models.ts but never rendered in the UI. -->
            <nav
                v-if="offers.last_page > 1"
                class="mt-10 flex items-center justify-center gap-2"
                aria-label="Pagination des opportunités"
            >
                <Link
                    v-if="offers.current_page > 1"
                    :href="`/offers?page=${offers.current_page - 1}`"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#0B1F3A]/30"
                    aria-label="Page précédente"
                >
                    ← Précédent
                </Link>

                <span class="px-4 py-2 text-sm text-gray-500">
                    Page {{ offers.current_page }} sur {{ offers.last_page }}
                    <span class="ml-1 text-gray-400">({{ offers.total }} résultats)</span>
                </span>

                <Link
                    v-if="offers.current_page < offers.last_page"
                    :href="`/offers?page=${offers.current_page + 1}`"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm text-gray-700 transition hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-[#0B1F3A]/30"
                    aria-label="Page suivante"
                >
                    Suivant →
                </Link>
            </nav>
        </main>
    </div>
</template>
