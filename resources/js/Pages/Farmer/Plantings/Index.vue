<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, ChevronRight, Sprout, LayoutGrid } from 'lucide-vue-next';

const props = defineProps({
    plantings: Object,
    filters: Object,
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

function filterByStatus(status) {
    router.get(route('farmer.plantings.index'), {
        status: status || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}
</script>

<template>
    <Head title="Penanaman Saya" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <LayoutGrid class="w-6 h-6 text-emerald-600" />
                    Penanaman Saya
                </h2>
                <Link
                    :href="route('farmer.plantings.create')"
                    class="btn-primary text-sm flex items-center gap-2"
                >
                    <Plus class="w-4 h-4" /> Tambah Penanaman
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <!-- Filter Tabs -->
                <div class="flex gap-2 mb-6 overflow-x-auto pb-2 scrollbar-hide">
                    <button
                        @click="filterByStatus(null)"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 whitespace-nowrap border"
                        :class="!filters?.status ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-[#131b17] text-gray-600 border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 dark:bg-white/5'"
                    >
                        Semua
                    </button>
                    <button
                        v-for="(label, key) in statusLabels"
                        :key="key"
                        @click="filterByStatus(key)"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-all duration-200 whitespace-nowrap border"
                        :class="filters?.status === key ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-[#131b17] text-gray-600 border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 dark:bg-white/5'"
                    >
                        {{ label }}
                    </button>
                </div>

                <!-- Planting List -->
                <div v-if="plantings.data.length > 0" class="space-y-3 animate-fade-in">
                    <Link
                        v-for="planting in plantings.data"
                        :key="planting.id"
                        :href="route('farmer.plantings.show', planting.id)"
                        class="block glass-card p-5 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 dark:bg-white/5 transition-all duration-200 group"
                    >
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-5">
                                <div class="w-14 h-14 rounded-2xl bg-emerald-100 flex items-center justify-center text-2xl group-hover:scale-105 transition-transform">
                                    {{ planting.crop?.icon || '🌱' }}
                                </div>
                                <div>
                                    <div class="font-semibold text-lg text-gray-900 dark:text-white group-hover:text-emerald-600 transition-colors">
                                        {{ planting.crop?.name }}
                                        <span v-if="planting.crop?.variety" class="text-gray-500 dark:text-gray-400 font-normal text-sm ml-1">
                                            ({{ planting.crop.variety }})
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 flex flex-wrap items-center gap-x-4 gap-y-1">
                                        <span>Tanam: {{ formatDate(planting.planted_at) }}</span>
                                        <span class="hidden sm:inline text-gray-300">•</span>
                                        <span>Est. Panen: {{ formatDate(planting.estimated_harvest_at) }}</span>
                                    </div>
                                    <div v-if="planting.area_hectares" class="text-xs text-emerald-600 font-medium mt-2">
                                        Luas: {{ planting.area_hectares }} Ha
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span
                                    class="px-3 py-1.5 text-xs font-semibold rounded-md border"
                                    :class="statusClasses[planting.status]"
                                >
                                    {{ statusLabels[planting.status] }}
                                </span>
                                <ChevronRight class="w-5 h-5 text-gray-400 dark:text-gray-500 group-hover:text-emerald-500 transition-colors" />
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-else class="glass-card p-16 text-center animate-fade-in border-dashed border-2">
                    <Sprout class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Belum Ada Penanaman</h3>
                    <p class="text-gray-500 dark:text-gray-400 mb-8 max-w-md mx-auto">Mulai catat penanaman pertamamu untuk memantau pertumbuhan dan menghubungkannya dengan pembeli!</p>
                    <Link
                        :href="route('farmer.plantings.create')"
                        class="btn-primary"
                    >
                        <Plus class="w-4 h-4 mr-2" /> Tambah Penanaman Pertama
                    </Link>
                </div>

                <!-- Pagination -->
                <div v-if="plantings.links && plantings.last_page > 1" class="flex justify-center gap-2 mt-8">
                    <Link
                        v-for="link in plantings.links"
                        :key="link.label"
                        :href="link.url"
                        class="px-3 py-2 text-sm rounded-lg transition-colors border"
                        :class="link.active ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white dark:bg-[#131b17] text-gray-600 border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 dark:bg-white/5'"
                        v-html="link.label"
                    />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

