<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import {
    ArrowLeft, Sprout, CalendarDays, ClipboardList, Wallet,
    CheckCircle, Trash2, Plus, Camera, Pencil, Settings, X, Save, Package, Timer, Hash
} from 'lucide-vue-next';
import DatePicker from '@/Components/DatePicker.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    planting: Object,
    recentActivities: Array
});

const statusLabels = { growing: 'Tumbuh', 'pre-order': 'Pre-Order', ready: 'Siap Panen', harvested: 'Dipanen' };
const statusClasses = { growing: 'status-growing', 'pre-order': 'status-pre-order', ready: 'status-ready', harvested: 'status-harvested' };

const showAdjustModal = ref(false);
const adjustForm = useForm({ estimated_harvest_at: props.planting.estimated_harvest_at?.split('T')[0] || '' });

function formatDate(d) { if (!d) return '-'; return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }); }
function adjustHarvest() { adjustForm.patch(route('farmer.plantings.adjust-harvest', props.planting.id), { onSuccess: () => showAdjustModal.value = false }); }
function markHarvested() { if (confirm('Tandai sebagai dipanen?')) router.patch(route('farmer.plantings.update', props.planting.id), { status: 'harvested' }); }

const deleteLog = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus catatan ini?')) {
        router.delete(route('farmer.plantings.progress.destroy', [props.planting.id, id]));
    }
};

const deleteActivity = (id) => {
    if (confirm('Apakah Anda yakin ingin menghapus aktivitas ini?')) {
        router.delete(route('farmer.plantings.activities.destroy', [props.planting.id, id]), {
            preserveScroll: true
        });
    }
};

const deletePlanting = () => { if (confirm('Hapus penanaman ini?')) router.delete(route('farmer.plantings.destroy', props.planting.id)); }
</script>

<template>

    <Head :title="`${planting.crop?.name} - Detail`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('farmer.plantings.index')"
                    class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 dark:bg-white/10 dark:hover:bg-white/10 dark:bg-white/10">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">{{ planting.crop?.icon }}</span>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ planting.crop?.name }}</h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Info Card -->
                <div class="glass-card p-6 sm:p-8 animate-fade-in border-t-4 border-t-emerald-500">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 mb-8">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-100 flex items-center justify-center text-3xl">
                                {{ planting.crop?.icon || '🌱' }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ planting.crop?.name }}
                                    <span v-if="planting.crop?.variety"
                                        class="text-gray-500 dark:text-gray-400 text-base font-normal">({{
                                            planting.crop.variety
                                        }})</span>
                                </h3>
                                <div class="mt-2">
                                    <span class="inline-block px-3 py-1 text-sm font-semibold rounded-md border"
                                        :class="statusClasses[planting.status]">{{ statusLabels[planting.status]
                                        }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <Link :href="route('farmer.plantings.activities.index', planting.id)"
                                class="px-4 py-2 text-sm rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-900/40 border border-blue-200 dark:border-blue-900/30 transition-colors flex items-center gap-2 font-medium">
                                <ClipboardList class="w-4 h-4" /> Aktivitas
                            </Link>
                            <Link :href="route('farmer.finances.index', { planting_id: planting.id })"
                                class="px-4 py-2 text-sm rounded-xl bg-purple-50 dark:bg-purple-900/20 text-purple-600 dark:text-purple-400 hover:bg-purple-100 dark:hover:bg-purple-900/40 border border-purple-200 dark:border-purple-900/30 transition-colors flex items-center gap-2 font-medium">
                                <Wallet class="w-4 h-4" /> Keuangan
                            </Link>
                            <Link :href="route('farmer.plantings.edit', planting.id)"
                                class="px-4 py-2 text-sm rounded-xl bg-emerald-50 dark:bg-emerald-900/20 text-emerald-600 dark:text-emerald-400 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-900/30 transition-colors flex items-center gap-2 font-medium">
                                <Pencil class="w-4 h-4" /> Edit
                            </Link>
                            <button v-if="planting.status !== 'harvested'" @click="markHarvested"
                                class="btn-harvest text-sm flex items-center gap-2 !px-4 !py-2 bg-amber-500 hover:bg-amber-600 text-white border-none">
                                <CheckCircle class="w-4 h-4" /> Panen
                            </button>
                            <button @click="deletePlanting"
                                class="px-4 py-2 text-sm rounded-xl bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 border border-red-200 dark:border-red-900/30 transition-colors flex items-center gap-2 font-medium">
                                <Trash2 class="w-4 h-4" /> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                        <div
                            class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div
                                class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <CalendarDays class="w-4 h-4" /> Tanggal Tanam
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                formatDate(planting.planted_at)
                            }}</div>
                        </div>
                        <div
                            class="bg-amber-50 dark:bg-amber-900/20 rounded-xl p-4 border border-amber-100 dark:border-amber-900/30">
                            <div
                                class="flex items-center gap-2 text-xs font-medium text-amber-600 dark:text-amber-400 mb-1">
                                <CalendarDays class="w-4 h-4" /> Est. Panen
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                formatDate(planting.estimated_harvest_at) }}</div>
                            <button v-if="planting.status !== 'harvested'" @click="showAdjustModal = true"
                                class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 underline mt-1.5 flex items-center gap-1">
                                <Settings class="w-3 h-3" /> Sesuaikan
                            </button>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div
                                class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Hash class="w-4 h-4" /> Luas Lahan
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.area_hectares ?
                                `${planting.area_hectares} Ha` : '-' }}</div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div
                                class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Sprout class="w-4 h-4" /> Jml Tanaman
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.plant_count ?
                                planting.plant_count : '-' }}</div>
                        </div>
                        <div
                            class="bg-emerald-50 dark:bg-emerald-900/20 rounded-xl p-4 border border-emerald-100 dark:border-emerald-900/30">
                            <div
                                class="flex items-center gap-2 text-xs font-medium text-emerald-600 dark:text-emerald-400 mb-1">
                                <Package class="w-4 h-4" /> Est. Hasil
                            </div>
                            <div class="text-sm font-bold text-emerald-700 dark:text-emerald-300">{{
                                planting.estimated_yield_kg
                                    ? `${planting.estimated_yield_kg} Kg` : '-' }}</div>
                        </div>
                        <div
                            class="bg-gray-50 dark:bg-white/5 rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div
                                class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Timer class="w-4 h-4" /> Tumbuh
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{
                                planting.crop?.default_hst }}
                                hari (HST)</div>
                        </div>
                    </div>

                    <div v-if="planting.notes"
                        class="mt-4 p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            <ClipboardList class="w-4 h-4" /> Catatan
                        </div>
                        <div class="text-sm text-gray-700">{{ planting.notes }}</div>
                    </div>
                </div>

                <!-- Recent Activities Section -->
                <div class="glass-card p-6 sm:p-8 animate-slide-up mb-8">
                    <div
                        class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-white/5">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <ClipboardList class="w-5 h-5 text-blue-500" /> Aktivitas Terbaru
                        </h3>
                        <div class="flex items-center gap-3">
                            <Link :href="route('farmer.plantings.activities.index', planting.id)"
                                class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:underline transition-all">
                                Lihat Semua
                            </Link>
                            <Link v-if="planting.status !== 'harvested'"
                                :href="route('farmer.plantings.activities.create', planting.id)"
                                class="btn-primary !bg-blue-600 hover:!bg-blue-700 text-sm flex items-center gap-1 !px-3 !py-1.5 border-none shadow-sm">
                                <Plus class="w-4 h-4" /> Tambah
                            </Link>
                        </div>
                    </div>

                    <div v-if="recentActivities.length" class="space-y-4">
                        <div v-for="activity in recentActivities" :key="activity.id"
                            class="flex items-center justify-between gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5 group transition-colors hover:border-gray-200 dark:hover:border-white/10">
                            <div class="flex items-center gap-3 flex-1 overflow-hidden">
                                <div class="w-10 h-10 rounded-full bg-white dark:bg-[#131b17] flex items-center justify-center flex-shrink-0 border border-gray-100 dark:border-white/5 shadow-sm">
                                    <span class="text-lg">{{ activity.type_icon }}</span>
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-gray-900 dark:text-white text-sm truncate">
                                            {{ activity.type_label }}
                                        </h4>
                                        <span class="text-[10px] font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1 shrink-0">
                                            <CalendarDays class="w-3 h-3" /> {{ formatDate(activity.activity_date) }}
                                        </span>
                                    </div>
                                    <p class="text-xs text-gray-600 dark:text-gray-400 truncate mt-0.5" :class="{'italic opacity-75': !activity.note}">
                                        {{ activity.note || 'Tanpa catatan' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all shrink-0">
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
                    </div>
                    <div v-else
                        class="text-center py-12 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-xl">
                        <ClipboardList class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada aktivitas yang dicatat.</p>
                    </div>
                </div>

                <!-- Progress Logs -->
                <div class="glass-card p-6 sm:p-8 animate-slide-up">
                    <div
                        class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-white/5">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <Camera class="w-5 h-5 text-emerald-500" /> Catatan Perkembangan
                        </h3>
                        <Link v-if="planting.status !== 'harvested'"
                            :href="route('farmer.plantings.progress.create', planting.id)"
                            class="btn-primary text-sm flex items-center gap-1 !px-3 !py-1.5">
                            <Plus class="w-4 h-4" /> Tambah
                        </Link>
                    </div>

                    <div v-if="planting.progress_logs?.length" class="space-y-4">
                        <div v-for="log in planting.progress_logs" :key="log.id"
                            class="flex items-center justify-between gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5 group transition-colors hover:border-gray-200 dark:hover:border-white/10">
                            <div class="flex items-center gap-4 flex-1 overflow-hidden">
                                <img v-if="log.photo_path" :src="`/storage/${log.photo_path}`"
                                    class="w-14 h-14 sm:w-16 sm:h-16 object-cover rounded-lg flex-shrink-0 border border-gray-200 dark:border-white/10 shadow-sm" />
                                <div v-else class="w-14 h-14 sm:w-16 sm:h-16 rounded-lg bg-gray-100 dark:bg-[#131b17] flex items-center justify-center flex-shrink-0 border border-gray-100 dark:border-white/5 shadow-sm">
                                    <Camera class="w-6 h-6 text-gray-300 dark:text-gray-600" />
                                </div>
                                <div class="flex-1 min-w-0 flex flex-col justify-center">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-[10px] font-bold text-gray-500 dark:text-gray-400 flex items-center gap-1 shrink-0 uppercase tracking-wider">
                                            <CalendarDays class="w-3 h-3" /> {{ formatDate(log.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-gray-700 dark:text-gray-300 truncate" :class="{'italic opacity-75': !log.note}">
                                        {{ log.note || 'Tanpa catatan' }}
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-all shrink-0">
                                <Link :href="route('farmer.plantings.progress.edit', [planting.id, log.id])"
                                    class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all">
                                    <Pencil class="w-4 h-4" />
                                </Link>
                                <button @click="deleteLog(log.id)"
                                    class="p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                    <Trash2 class="w-4 h-4" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else
                        class="text-center py-12 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-xl">
                        <Camera class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada foto atau catatan perkembangan.
                        </p>
                    </div>
                </div>

                <!-- Adjust Modal -->
                <Modal :show="showAdjustModal" @close="showAdjustModal = false" maxWidth="md">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-5">
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                <CalendarDays class="w-5 h-5 text-amber-500" /> Sesuaikan Tanggal Panen
                            </h3>
                            <button @click="showAdjustModal = false"
                                class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-white p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                                <X class="w-5 h-5" />
                            </button>
                        </div>
                        <form @submit.prevent="adjustHarvest">
                            <div class="mb-4">
                                <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tanggal
                                    Panen
                                    Baru</label>
                                <DatePicker v-model="adjustForm.estimated_harvest_at" :min-date="planting.planted_at"
                                    placeholder="Pilih tanggal panen" required />
                            </div>

                            <!-- Estimated Yield Info -->
                            <div v-if="planting.estimated_yield_kg"
                                class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/30 flex items-center gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/40 flex items-center justify-center flex-shrink-0">
                                    <Package class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                </div>
                                <div>
                                    <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-300">Estimasi
                                        Hasil
                                        Panen</div>
                                    <div class="text-lg font-bold text-emerald-800 dark:text-white">{{
                                        planting.estimated_yield_kg }} Kg</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ planting.plant_count
                                    }}
                                        tanaman × {{ planting.crop?.estimated_yield_per_plant_kg }} kg/tanaman</div>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 mt-6">
                                <button type="button" @click="showAdjustModal = false"
                                    class="btn-secondary text-sm">Batal</button>
                                <button type="submit" :disabled="adjustForm.processing"
                                    class="btn-primary text-sm flex items-center gap-2">
                                    <Save class="w-4 h-4" /> Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </Modal>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
