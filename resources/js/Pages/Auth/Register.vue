<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { Sprout, ShoppingCart } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: '',
    phone: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Daftar" />

        <form @submit.prevent="submit">
            <!-- Role Selection -->
            <div class="mb-6">
                <InputLabel value="Daftar Sebagai" class="mb-3" />
                <div class="grid grid-cols-2 gap-3">
                    <button
                        type="button"
                        @click="form.role = 'farmer'"
                        class="p-4 rounded-xl border-2 text-center transition-all duration-200 flex flex-col items-center justify-center gap-2"
                        :class="form.role === 'farmer'
                            ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400'
                            : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 hover:border-emerald-300 dark:hover:border-emerald-700/50 hover:bg-gray-50 dark:hover:bg-[#1a241f]'"
                    >
                        <Sprout class="w-8 h-8" />
                        <span class="text-sm font-semibold">Petani</span>
                    </button>
                    <button
                        type="button"
                        @click="form.role = 'buyer'"
                        class="p-4 rounded-xl border-2 text-center transition-all duration-200 flex flex-col items-center justify-center gap-2"
                        :class="form.role === 'buyer'
                            ? 'border-amber-500 bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400'
                            : 'border-gray-200 dark:border-white/10 text-gray-500 dark:text-gray-400 hover:border-amber-300 dark:hover:border-amber-700/50 hover:bg-gray-50 dark:hover:bg-[#1a241f]'"
                    >
                        <ShoppingCart class="w-8 h-8" />
                        <span class="text-sm font-semibold">Pembeli</span>
                    </button>
                </div>
                <InputError class="mt-2" :message="form.errors.role" />
            </div>

            <div>
                <InputLabel for="name" value="Nama Lengkap" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="mt-4">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="phone" value="No. HP / WhatsApp" />

                <TextInput
                    id="phone"
                    type="tel"
                    class="mt-1 block w-full"
                    v-model="form.phone"
                    placeholder="08xxxxxxxxxx"
                    autocomplete="tel"
                />

                <InputError class="mt-2" :message="form.errors.phone" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    value="Konfirmasi Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6 flex items-center justify-between">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 transition-colors"
                >
                    Sudah punya akun?
                </Link>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing || !form.role"
                >
                    Daftar
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>

