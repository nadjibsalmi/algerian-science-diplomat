<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import GuestLayout from '@/Layouts/GuestLayout.vue';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post(route('login'), { onFinish: () => form.reset('password') });
}
</script>

<template>
    <GuestLayout>
        <Head title="Connexion" />

        <h1 class="mb-6 text-2xl font-bold text-[#0B1F3A]">Connexion</h1>

        <!-- Status -->
        <div
            v-if="$page.props.flash && ($page.props.flash as { success?: string }).success"
            class="mb-4 rounded-lg bg-green-50 p-3 text-sm text-green-700"
            role="alert"
        >
            {{ ($page.props.flash as { success?: string }).success }}
        </div>

        <form @submit.prevent="submit" novalidate>
            <div class="space-y-4">
                <!-- Email -->
                <div>
                    <label for="email" class="mb-1 block text-sm font-medium text-gray-700">
                        Adresse e-mail
                    </label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        autocomplete="email"
                        required
                        :class="[
                            'block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#006233]/40',
                            form.errors.email
                                ? 'border-red-400 bg-red-50'
                                : 'border-gray-300 bg-white',
                        ]"
                    />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600" role="alert">
                        {{ form.errors.email }}
                    </p>
                </div>

                <!-- Password -->
                <div>
                    <div class="mb-1 flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            Mot de passe
                        </label>
                        <Link
                            :href="route('password.request')"
                            class="text-xs text-[#006233] hover:underline"
                        >
                            Oublié ?
                        </Link>
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        autocomplete="current-password"
                        required
                        :class="[
                            'block w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#006233]/40',
                            form.errors.password
                                ? 'border-red-400 bg-red-50'
                                : 'border-gray-300 bg-white',
                        ]"
                    />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600" role="alert">
                        {{ form.errors.password }}
                    </p>
                </div>

                <!-- Remember me -->
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input
                        v-model="form.remember"
                        type="checkbox"
                        class="rounded border-gray-300 text-[#006233]"
                    />
                    Se souvenir de moi
                </label>
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="mt-6 w-full rounded-lg bg-[#0B1F3A] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#0B1F3A]/90 disabled:opacity-60"
            >
                <span v-if="form.processing">Connexion...</span>
                <span v-else>Se connecter</span>
            </button>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
            Pas encore de compte ?
            <Link :href="route('register')" class="font-medium text-[#006233] hover:underline">
                S'inscrire
            </Link>
        </p>
    </GuestLayout>
</template>
