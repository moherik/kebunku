<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Sprout, MapPin, Phone, Instagram, Facebook, Youtube, MessageCircle, CalendarDays, ArrowLeft, Package } from 'lucide-vue-next';

const props = defineProps({
    garden: Object,
    plantings: Array,
});

const openWhatsApp = (phone) => {
    if (!phone) return;
    const cleanPhone = phone.replace(/\D/g, '');
    const finalPhone = cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone;
    window.open(`https://wa.me/${finalPhone}`, '_blank');
};
</script>

<template>
    <Head :title="garden.name" />

    <div class="min-h-screen bg-gray-50 dark:bg-white/5">
        <!-- Top Navigation -->
        <nav class="bg-white/80 dark:bg-[#131b17]/80 backdrop-blur-md border-b border-gray-100 dark:border-white/5 sticky top-0 z-40">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center gap-4">
                        <Link :href="route('calendar')" class="p-2 -ml-2 text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors rounded-full hover:bg-emerald-50">
                            <ArrowLeft class="w-5 h-5" />
                        </Link>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500 flex items-center justify-center">
                                <Sprout class="w-5 h-5 text-white" />
                            </div>
                            <span class="font-bold text-xl text-gray-900 dark:text-white">KebunKu</span>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Garden Header Profile -->
            <div class="bg-white dark:bg-[#131b17] rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 mb-8 relative overflow-hidden">
                <!-- Cover Image -->
                <div class="h-48 sm:h-64 w-full bg-emerald-50 dark:bg-emerald-900/10 relative">
                    <img v-if="garden.cover_path" :src="'/storage/' + garden.cover_path" class="w-full h-full object-cover" alt="Garden Cover" />
                    <div v-else class="absolute inset-0 flex items-center justify-center opacity-10">
                        <Sprout class="w-32 h-32 text-emerald-500" />
                    </div>
                </div>

                <div class="px-6 sm:px-10 pb-10 relative">
                    <div class="flex flex-col sm:flex-row gap-6 sm:gap-8 items-start">
                        <!-- Profile Logo -->
                        <div class="-mt-12 sm:-mt-16 w-24 h-24 sm:w-32 sm:h-32 rounded-2xl bg-white dark:bg-[#131b17] p-2 flex-shrink-0 shadow-lg relative z-10">
                            <div class="w-full h-full rounded-xl bg-emerald-100 dark:bg-emerald-900/30 overflow-hidden flex items-center justify-center">
                                <img v-if="garden.photo_path" :src="'/storage/' + garden.photo_path" class="w-full h-full object-cover" alt="Garden Logo" />
                                <Sprout v-else class="w-10 h-10 sm:w-14 sm:h-14 text-emerald-500" />
                            </div>
                        </div>
                        
                        <div class="flex-1 sm:pt-4">
                        <h1 class="text-3xl sm:text-4xl font-black text-gray-900 dark:text-white mb-2">{{ garden.name }}</h1>
                        
                        <div class="flex flex-col gap-2 mt-4 text-gray-600 dark:text-gray-400">
                            <div class="flex items-center gap-2">
                                <MapPin class="w-4 h-4 flex-shrink-0" />
                                <span>{{ garden.address || garden.user.address || 'Alamat tidak tersedia' }}</span>
                            </div>
                        </div>

                        <p v-if="garden.description" class="mt-6 text-gray-600 dark:text-gray-400 leading-relaxed text-sm sm:text-base max-w-3xl">
                            {{ garden.description }}
                        </p>

                        <!-- Social & Contact Links -->
                        <div class="flex flex-wrap items-center gap-3 mt-8">
                            <button
                                v-if="garden.whatsapp_number"
                                @click="openWhatsApp(garden.whatsapp_number)"
                                class="px-5 py-2.5 bg-green-500 hover:bg-green-600 text-white text-sm font-bold rounded-xl transition-colors flex items-center gap-2 shadow-sm shadow-green-500/20"
                            >
                                <MessageCircle class="w-4 h-4" /> Hubungi via WhatsApp
                            </button>
                            <a
                                v-if="garden.instagram_link"
                                :href="garden.instagram_link"
                                target="_blank"
                                class="p-2.5 bg-gray-50 dark:bg-white/5 hover:bg-pink-50 dark:hover:bg-pink-900/20 text-gray-600 dark:text-gray-400 hover:text-pink-600 dark:hover:text-pink-400 rounded-xl transition-colors"
                            >
                                <Instagram class="w-5 h-5" />
                            </a>
                            <a
                                v-if="garden.facebook_link"
                                :href="garden.facebook_link"
                                target="_blank"
                                class="p-2.5 bg-gray-50 dark:bg-white/5 hover:bg-blue-50 dark:hover:bg-blue-900/20 text-gray-600 dark:text-gray-400 hover:text-blue-600 dark:hover:text-blue-400 rounded-xl transition-colors"
                            >
                                <Facebook class="w-5 h-5" />
                            </a>
                            <a
                                v-if="garden.youtube_link"
                                :href="garden.youtube_link"
                                target="_blank"
                                class="p-2.5 bg-gray-50 dark:bg-white/5 hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 rounded-xl transition-colors"
                            >
                                <Youtube class="w-5 h-5" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            </div>

            <!-- Plantings List -->
            <div>
                <div class="flex items-center gap-3 mb-6">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Daftar Tanaman</h2>
                    <div class="h-px flex-1 bg-gray-200 dark:bg-white/10"></div>
                </div>

                <div v-if="plantings.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-4 sm:gap-6">
                    <Link
                        v-for="planting in plantings"
                        :key="planting.id"
                        :href="route('public.plantings.show', planting.id)"
                        class="bg-white dark:bg-[#131b17] rounded-2xl p-5 border border-gray-100 dark:border-white/5 hover:border-emerald-500/30 hover:shadow-lg hover:shadow-emerald-500/5 transition-all group"
                    >
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gray-50 dark:bg-white/5 flex items-center justify-center text-3xl group-hover:scale-110 transition-transform">
                                {{ planting.crop.icon || '🌱' }}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-start justify-between">
                                    <div>
                                        <h3 class="font-bold text-lg text-gray-900 dark:text-white group-hover:text-emerald-500 transition-colors">
                                            {{ planting.crop.name }}
                                        </h3>
                                        <div v-if="planting.crop.variety" class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                                            Var. {{ planting.crop.variety }}
                                        </div>
                                    </div>
                                    <span 
                                        class="px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider rounded-lg"
                                        :class="{
                                            'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400': planting.status === 'growing',
                                            'bg-amber-50 dark:bg-amber-900/20 text-amber-600 dark:text-amber-400': planting.status === 'pre-order',
                                            'bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400': planting.status === 'ready',
                                            'bg-gray-50 dark:bg-white/10 text-gray-600 dark:text-gray-400': planting.status === 'harvested',
                                        }"
                                    >
                                        {{ planting.status === 'growing' ? 'Masa Tanam' : (planting.status === 'pre-order' ? 'Pre-Order' : (planting.status === 'ready' ? 'Siap Panen' : 'Selesai')) }}
                                    </span>
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-y-2 gap-x-4 text-sm">
                                    <div class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <CalendarDays class="w-4 h-4 text-emerald-500" />
                                        <span>Panen: {{ new Date(planting.estimated_harvest_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' }) }}</span>
                                    </div>
                                    <div v-if="planting.plant_count" class="flex items-center gap-2 text-gray-600 dark:text-gray-400">
                                        <Sprout class="w-4 h-4 text-emerald-500" />
                                        <span>{{ planting.plant_count }} pohon</span>
                                    </div>
                                    <div v-if="planting.estimated_yield_kg" class="flex items-center gap-2 text-gray-600 dark:text-gray-400 col-span-2">
                                        <Package class="w-4 h-4 text-emerald-500" />
                                        <span>Est. Hasil: {{ planting.estimated_yield_kg }} kg</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
                
                <div v-else class="text-center py-16 bg-white dark:bg-[#131b17] rounded-3xl border border-gray-100 dark:border-white/5 border-dashed">
                    <Sprout class="w-12 h-12 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">Belum ada tanaman</h3>
                    <p class="text-gray-500 dark:text-gray-400">Petani belum menambahkan data penanaman di kebun ini.</p>
                </div>
            </div>
        </main>
    </div>
</template>
