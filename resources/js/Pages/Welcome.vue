<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { Sprout, LogIn, UserPlus, CalendarDays, Camera, Handshake } from 'lucide-vue-next';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Kebunku — Kalender Panen Interaktif" />

    <div class="min-h-screen flex flex-col">
        <!-- Header -->
        <nav class="fixed w-full z-50 bg-white/80 dark:bg-[#0b110e]/80 backdrop-blur-md border-b border-gray-200 dark:border-white/5">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <div class="flex items-center gap-2 text-emerald-600 dark:text-emerald-500">
                        <Sprout class="w-8 h-8" />
                        <span class="text-xl font-bold tracking-tight">Kebunku</span>
                    </div>
                    <div class="flex items-center gap-4">
                        <Link
                            :href="route('login')"
                            class="text-sm font-medium text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100 transition-colors flex items-center gap-2"
                        >
                            <LogIn class="w-4 h-4" /> Masuk
                        </Link>
                        <Link
                            :href="route('register')"
                            class="btn-primary text-sm flex items-center gap-2"
                        >
                            <UserPlus class="w-4 h-4" /> Daftar
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="flex-1 flex items-center justify-center pt-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
                <div class="text-center animate-fade-in max-w-3xl mx-auto">
                    <!-- Heading -->
                    <h1 class="text-4xl sm:text-6xl font-extrabold text-gray-900 dark:text-white mb-6 leading-tight tracking-tight">
                        Kalender Panen <span class="text-emerald-600 dark:text-emerald-500">Interaktif</span>
                    </h1>

                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-12 leading-relaxed">
                        Catat penanaman, pantau perkembangan, dan hubungkan petani langsung dengan pembeli. Terinspirasi dari Story of Seasons.
                    </p>

                    <!-- CTA Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center mb-24">
                        <Link
                            :href="route('register')"
                            class="btn-primary text-base px-8 py-3.5 flex items-center justify-center gap-2"
                        >
                            <Sprout class="w-5 h-5" /> Mulai Sebagai Petani
                        </Link>
                        <Link
                            :href="route('calendar')"
                            class="btn-secondary text-base px-8 py-3.5 flex items-center justify-center gap-2"
                        >
                            <CalendarDays class="w-5 h-5" /> Lihat Kalender Panen
                        </Link>
                    </div>

                    <!-- Feature Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-left animate-slide-up">
                        <div class="glass-card p-6 group">
                            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <CalendarDays class="w-6 h-6" />
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Kalender Otomatis</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Catat tanggal tanam dan sistem akan menghitung estimasi panen berdasarkan data HST.
                            </p>
                        </div>
                        <div class="glass-card p-6 group">
                            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <Camera class="w-6 h-6" />
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Catatan Perkembangan</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Upload foto dan catatan perkembangan tanaman. Bisa digunakan secara offline.
                            </p>
                        </div>
                        <div class="glass-card p-6 group">
                            <div class="w-12 h-12 bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                                <Handshake class="w-6 h-6" />
                            </div>
                            <h3 class="text-lg font-semibold mb-2">Hubungkan Langsung</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Pembeli melihat panen mendatang di kalender dan menghubungi petani via WhatsApp.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-gray-200 dark:border-white/5 py-8 mt-auto">
            <div class="max-w-7xl mx-auto px-4 text-center text-sm text-gray-500 dark:text-gray-500">
                © 2024 Kebunku. Membantu petani Indonesia terhubung dengan pembeli.
            </div>
        </footer>
    </div>
</template>

