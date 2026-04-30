<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ArrowLeft, CalendarDays, Hash, MapPin, User, MessageCircle, Camera } from 'lucide-vue-next';

const props = defineProps({ planting: Object });

const statusLabels = { growing: 'Tumbuh', 'pre-order': 'Pre-Order', ready: 'Siap Panen', harvested: 'Dipanen' };
const statusClasses = { growing: 'status-growing', 'pre-order': 'status-pre-order', ready: 'status-ready', harvested: 'status-harvested' };

function formatDate(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}); }
function openWhatsApp(phone) { if(!phone) return; window.open(`https://wa.me/${phone.replace(/^0/,'62')}`, '_blank'); }
</script>

<template>
    <Head :title="`${planting.crop?.name} - Detail Panen`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('calendar')" class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 dark:bg-white/10 dark:hover:bg-white/10 dark:bg-white/10">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">{{ planting.crop?.icon }}</span>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ planting.crop?.name }}</h2>
                </div>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 space-y-6">
                <div class="glass-card p-6 sm:p-8 animate-fade-in border-t-4 border-t-amber-500">
                    <div class="flex items-center gap-5 mb-8">
                        <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center text-3xl">
                            {{ planting.crop?.icon || '🌱' }}
                        </div>
                        <div>
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                {{ planting.crop?.name }} 
                                <span v-if="planting.crop?.variety" class="text-gray-500 dark:text-gray-400 text-base font-normal">({{ planting.crop.variety }})</span>
                            </h3>
                            <div class="mt-2">
                                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-md border" :class="statusClasses[planting.status]">{{ statusLabels[planting.status] }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4 mb-8">
                        <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <CalendarDays class="w-4 h-4" /> Tanggal Tanam
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(planting.planted_at) }}</div>
                        </div>
                        <div class="bg-amber-50 rounded-xl p-4 border border-amber-100">
                            <div class="flex items-center gap-2 text-xs font-medium text-amber-600 mb-1">
                                <CalendarDays class="w-4 h-4" /> Est. Panen
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(planting.estimated_harvest_at) }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Hash class="w-4 h-4" /> Luas Lahan
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.area_hectares ? `${planting.area_hectares} Ha` : '-' }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <User class="w-4 h-4" /> Petani
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.user?.name }}</div>
                        </div>
                    </div>
                    
                    <div v-if="planting.user?.address" class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5 mb-8">
                        <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            <MapPin class="w-4 h-4" /> Lokasi
                        </div>
                        <div class="text-sm text-gray-700">{{ planting.user.address }}</div>
                    </div>
                    
                    <button v-if="planting.user?.phone" @click="openWhatsApp(planting.user.phone)" class="w-full px-4 py-3 bg-green-500 hover:bg-green-600 text-white font-semibold rounded-xl transition-colors flex items-center justify-center gap-2">
                        <MessageCircle class="w-5 h-5" /> Hubungi Petani via WhatsApp
                    </button>
                </div>

                <!-- Progress photos (public view) -->
                <div v-if="planting.progress_logs?.length" class="glass-card p-6 sm:p-8 animate-slide-up">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-6 pb-4 border-b border-gray-100 dark:border-white/5">
                        <Camera class="w-5 h-5 text-emerald-500" /> Foto Perkembangan
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <div v-for="log in planting.progress_logs" :key="log.id" class="relative group aspect-square">
                            <img v-if="log.photo_path" :src="`/storage/${log.photo_path}`" class="w-full h-full object-cover rounded-xl border border-gray-200 dark:border-white/10" />
                            <div v-if="log.note" class="absolute inset-0 bg-gray-900/70 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity flex items-end p-3 backdrop-blur-sm">
                                <p class="text-xs text-white line-clamp-3">{{ log.note }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
