<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Plus, Trash2, CalendarDays, Image as ImageIcon, ClipboardList, Pencil } from 'lucide-vue-next';

const props = defineProps({
    planting: Object,
    activities: Array,
    activityTypes: Object,
    activityIcons: Object,
});

function formatDate(d) {
    if (!d) return '-';
    return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function deleteActivity(activityId) {
    if (confirm('Hapus aktivitas ini?')) {
        router.delete(route('farmer.plantings.activities.destroy', [props.planting.id, activityId]));
    }
}

// Group activities by date
function groupedActivities() {
    const groups = {};
    (props.activities || []).forEach(a => {
        const date = a.activity_date?.split('T')[0] || a.activity_date;
        if (!groups[date]) groups[date] = [];
        groups[date].push(a);
    });
    return groups;
}
</script>

<template>
    <Head :title="`Aktivitas - ${planting.crop?.name}`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('farmer.plantings.show', planting.id)"
                    class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <ClipboardList class="w-6 h-6 text-emerald-600 dark:text-emerald-500" />
                        Catatan Aktivitas
                    </h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ planting.crop?.icon }} {{ planting.crop?.name }} {{ planting.crop?.variety ? `(${planting.crop.variety})` : '' }}</p>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <!-- Add Button -->
                <div class="flex justify-end mb-6">
                    <Link :href="route('farmer.plantings.activities.create', planting.id)"
                        class="btn-primary text-sm flex items-center gap-2">
                        <Plus class="w-4 h-4" /> Catat Aktivitas
                    </Link>
                </div>

                <!-- Activities Timeline -->
                <div v-if="activities?.length" class="space-y-4 animate-fade-in">
                    <template v-for="(items, date) in groupedActivities()" :key="date">
                        <!-- Date header -->
                        <div class="flex items-center gap-3 mt-2">
                            <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400 rounded-full text-xs font-bold border border-emerald-100 dark:border-emerald-800/30">
                                <CalendarDays class="w-3.5 h-3.5" />
                                {{ formatDate(date) }}
                            </div>
                            <div class="flex-1 h-px bg-gray-200 dark:bg-white/10"></div>
                        </div>

                        <!-- Activities for this date -->
                        <div class="space-y-3 pl-2">
                            <div v-for="activity in items" :key="activity.id"
                                class="glass-card p-4 sm:p-5 flex flex-col sm:flex-row gap-4 animate-slide-up group">
                                <!-- Photo -->
                                <div v-if="activity.photo_url" class="flex-shrink-0">
                                    <img :src="activity.photo_url"
                                        class="w-full sm:w-28 h-28 object-cover rounded-xl border border-gray-200 dark:border-white/10" />
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between gap-3">
                                        <div class="flex items-center gap-2.5">
                                            <span class="text-xl">{{ activity.type_icon }}</span>
                                            <span class="font-bold text-gray-900 dark:text-white text-sm">
                                                {{ activity.type_label }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all">
                                            <Link :href="route('farmer.plantings.activities.edit', [planting.id, activity.id])"
                                                class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all">
                                                <Pencil class="w-4 h-4" />
                                            </Link>
                                            <button @click="deleteActivity(activity.id)"
                                                class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Quantity -->
                                    <div v-if="activity.formatted_quantity"
                                        class="mt-2 inline-flex items-center gap-1 px-2.5 py-1 bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-gray-400 rounded-lg text-xs font-medium">
                                        {{ activity.formatted_quantity }}
                                    </div>

                                    <!-- Note -->
                                    <p v-if="activity.note" class="mt-2 text-sm text-gray-600 dark:text-gray-400 leading-relaxed">
                                        {{ activity.note }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Empty State -->
                <div v-else class="text-center py-16 glass-card">
                    <ClipboardList class="w-14 h-14 text-gray-300 mx-auto mb-4" />
                    <p class="text-gray-500 dark:text-gray-400 mb-1 font-medium">Belum ada catatan aktivitas</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500 mb-6">Mulai catat aktivitas pertanian kamu untuk tracking yang lebih baik</p>
                    <Link :href="route('farmer.plantings.activities.create', planting.id)"
                        class="btn-primary text-sm inline-flex items-center gap-2">
                        <Plus class="w-4 h-4" /> Catat Aktivitas Pertama
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
