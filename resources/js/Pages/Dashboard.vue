<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, ChevronRight, Sprout, CalendarDays, LineChart } from 'lucide-vue-next';

const props = defineProps({
    userRole: String,
    stats: Object,
    recentPlantings: Array,
    upcomingHarvests: Array,
});

const statusLabels = {
    growing: 'Tumbuh',
    'pre-order': 'Pre-Order',
    ready: 'Siap Panen',
    harvested: 'Dipanen',
};

const statusClasses = {
    growing: 'status-growing',
    'pre-order': 'status-pre-order',
    ready: 'status-ready',
    harvested: 'status-harvested',
};

function formatDate(date) {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Beranda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                <LayoutDashboard class="w-6 h-6 text-emerald-600 dark:text-emerald-500" />
                {{ userRole === 'farmer' ? 'Dashboard Petani' : 'Dashboard Pembeli' }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Stats Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 animate-fade-in">
                    <!-- Farmer Stats -->
                    <template v-if="userRole === 'farmer'">
                        <div class="glass-card p-5">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_plantings }}</div>
                            <div class="text-sm text-gray-500 mt-1">Total Penanaman</div>
                        </div>
                        <div class="glass-card p-5">
                            <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.growing }}</div>
                            <div class="text-sm text-gray-500 mt-1">Sedang Tumbuh</div>
                        </div>
                        <div class="glass-card p-5">
                            <div class="text-3xl font-bold text-amber-500 dark:text-amber-400">{{ stats.pre_order }}</div>
                            <div class="text-sm text-gray-500 mt-1">Pre-Order</div>
                        </div>
                        <div class="glass-card p-5">
                            <div class="text-3xl font-bold text-orange-500 dark:text-orange-400">{{ stats.ready }}</div>
                            <div class="text-sm text-gray-500 mt-1">Siap Panen</div>
                        </div>
                    </template>

                    <!-- Buyer Stats -->
                    <template v-else>
                        <div class="glass-card p-5 col-span-1">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_available }}</div>
                            <div class="text-sm text-gray-500 mt-1">Total Tersedia</div>
                        </div>
                        <div class="glass-card p-5 col-span-1">
                            <div class="text-3xl font-bold text-orange-500 dark:text-orange-400">{{ stats.ready_now }}</div>
                            <div class="text-sm text-gray-500 mt-1">Siap Sekarang</div>
                        </div>
                        <div class="glass-card p-5 col-span-2">
                            <div class="text-3xl font-bold text-amber-500 dark:text-amber-400">{{ stats.coming_this_week }}</div>
                            <div class="text-sm text-gray-500 mt-1">Panen Minggu Ini</div>
                        </div>
                    </template>
                </div>

                <!-- Recent Plantings (Farmer) -->
                <div v-if="userRole === 'farmer'" class="glass-card p-6 animate-slide-up">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <LineChart class="w-5 h-5 text-emerald-500" /> Penanaman Terbaru
                        </h3>
                        <Link
                            :href="route('farmer.plantings.create')"
                            class="btn-primary text-sm flex items-center gap-1"
                        >
                            <Plus class="w-4 h-4" /> Tambah
                        </Link>
                    </div>

                    <div v-if="recentPlantings && recentPlantings.length > 0" class="space-y-3">
                        <Link
                            v-for="planting in recentPlantings"
                            :key="planting.id"
                            :href="route('farmer.plantings.show', planting.id)"
                            class="flex items-center justify-between p-4 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 border border-transparent hover:border-gray-100 dark:hover:border-white/5 transition-all duration-200 group"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-xl">
                                    {{ planting.crop?.icon || '🌱' }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                        {{ planting.crop?.name }}
                                        <span v-if="planting.crop?.variety" class="text-gray-500 text-sm ml-1 font-normal">
                                            ({{ planting.crop.variety }})
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500 mt-0.5">
                                        Ditanam: {{ formatDate(planting.planted_at) }}
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="px-2.5 py-1 text-xs font-medium rounded-full"
                                    :class="statusClasses[planting.status]"
                                >
                                    {{ statusLabels[planting.status] }}
                                </span>
                                <ChevronRight class="w-5 h-5 text-gray-400 group-hover:text-emerald-500 transition-colors" />
                            </div>
                        </Link>
                    </div>

                    <div v-else class="text-center py-12">
                        <Sprout class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                        <p class="text-gray-500 mb-6">Belum ada penanaman. Mulai catat penanaman pertamamu!</p>
                        <Link
                            :href="route('farmer.plantings.create')"
                            class="btn-primary"
                        >
                            <Plus class="w-4 h-4 mr-2" /> Tambah Penanaman Pertama
                        </Link>
                    </div>
                </div>

                <!-- Upcoming Harvests (Buyer) -->
                <div v-if="userRole === 'buyer'" class="glass-card p-6 animate-slide-up">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <CalendarDays class="w-5 h-5 text-amber-500" /> Panen Mendatang
                        </h3>
                        <Link
                            :href="route('calendar')"
                            class="btn-harvest text-sm flex items-center gap-2"
                        >
                            <CalendarDays class="w-4 h-4" /> Lihat Kalender
                        </Link>
                    </div>

                    <div v-if="upcomingHarvests && upcomingHarvests.length > 0" class="space-y-3">
                        <div
                            v-for="harvest in upcomingHarvests"
                            :key="harvest.id"
                            class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5"
                        >
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-xl">
                                    {{ harvest.crop?.icon || '🌾' }}
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 dark:text-white">
                                        {{ harvest.crop?.name }}
                                    </div>
                                    <div class="text-sm text-gray-500 mt-0.5">
                                        {{ harvest.user?.name }} · Est. panen: {{ formatDate(harvest.estimated_harvest_at) }}
                                    </div>
                                </div>
                            </div>
                            <span
                                class="px-2.5 py-1 text-xs font-medium rounded-full"
                                :class="statusClasses[harvest.status]"
                            >
                                {{ statusLabels[harvest.status] }}
                            </span>
                        </div>
                    </div>

                    <div v-else class="text-center py-12">
                        <CalendarDays class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                        <p class="text-gray-500">Belum ada panen mendatang di area ini.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

