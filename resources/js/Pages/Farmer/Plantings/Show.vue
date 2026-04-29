<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ArrowLeft, CheckCircle, Trash2, CalendarDays, Hash, Sprout, ClipboardList, Plus, Camera, Settings, X, Save, Package, Timer } from 'lucide-vue-next';
import DatePicker from '@/Components/DatePicker.vue';

const props = defineProps({ planting: Object });

const statusLabels = { growing: 'Tumbuh', 'pre-order': 'Pre-Order', ready: 'Siap Panen', harvested: 'Dipanen' };
const statusClasses = { growing: 'status-growing', 'pre-order': 'status-pre-order', ready: 'status-ready', harvested: 'status-harvested' };

const showAdjustModal = ref(false);
const adjustForm = useForm({ estimated_harvest_at: props.planting.estimated_harvest_at?.split('T')[0] || '' });

function formatDate(d) { if(!d) return '-'; return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}); }
function adjustHarvest() { adjustForm.patch(route('farmer.plantings.adjust-harvest', props.planting.id), { onSuccess: () => showAdjustModal.value = false }); }
function markHarvested() { if(confirm('Tandai sebagai dipanen?')) router.patch(route('farmer.plantings.update', props.planting.id), { status: 'harvested' }); }
function deletePlanting() { if(confirm('Hapus penanaman ini?')) router.delete(route('farmer.plantings.destroy', props.planting.id)); }
</script>

<template>
    <Head :title="`${planting.crop?.name} - Detail`" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('farmer.plantings.index')" class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <div class="flex items-center gap-2">
                    <span class="text-2xl">{{ planting.crop?.icon }}</span>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ planting.crop?.name }}</h2>
                </div>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-4xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Info Card -->
                <div class="glass-card p-6 sm:p-8 animate-fade-in border-t-4 border-t-emerald-500">
                    <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-6 mb-8">
                        <div class="flex items-center gap-5">
                            <div class="w-16 h-16 rounded-2xl bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-3xl">
                                {{ planting.crop?.icon || '🌱' }}
                            </div>
                            <div>
                                <h3 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    {{ planting.crop?.name }} 
                                    <span v-if="planting.crop?.variety" class="text-gray-500 text-base font-normal">({{ planting.crop.variety }})</span>
                                </h3>
                                <div class="mt-2">
                                    <span class="inline-block px-3 py-1 text-sm font-semibold rounded-md border" :class="statusClasses[planting.status]">{{ statusLabels[planting.status] }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button v-if="planting.status !== 'harvested'" @click="markHarvested" class="btn-harvest text-sm flex items-center gap-2 !px-4 !py-2 bg-amber-500 hover:bg-amber-600 text-white border-none">
                                <CheckCircle class="w-4 h-4" /> Panen
                            </button>
                            <button @click="deletePlanting" class="px-4 py-2 text-sm rounded-xl bg-red-50 text-red-600 hover:bg-red-100 dark:bg-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30 border border-red-200 dark:border-red-800/30 transition-colors flex items-center gap-2 font-medium">
                                <Trash2 class="w-4 h-4" /> Hapus
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4">
                        <div class="bg-gray-50 dark:bg-[#1a241f] rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <CalendarDays class="w-4 h-4" /> Tanggal Tanam
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(planting.planted_at) }}</div>
                        </div>
                        <div class="bg-amber-50 dark:bg-amber-900/10 rounded-xl p-4 border border-amber-100 dark:border-amber-900/30">
                            <div class="flex items-center gap-2 text-xs font-medium text-amber-600 dark:text-amber-500 mb-1">
                                <CalendarDays class="w-4 h-4" /> Est. Panen
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ formatDate(planting.estimated_harvest_at) }}</div>
                            <button v-if="planting.status!=='harvested'" @click="showAdjustModal=true" class="text-xs text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 underline mt-1.5 flex items-center gap-1">
                                <Settings class="w-3 h-3" /> Sesuaikan
                            </button>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#1a241f] rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Hash class="w-4 h-4" /> Luas Lahan
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.area_hectares ? `${planting.area_hectares} Ha` : '-' }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#1a241f] rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Sprout class="w-4 h-4" /> Jml Tanaman
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.plant_count ? planting.plant_count : '-' }}</div>
                        </div>
                        <div class="bg-emerald-50 dark:bg-emerald-900/10 rounded-xl p-4 border border-emerald-100 dark:border-emerald-900/30">
                            <div class="flex items-center gap-2 text-xs font-medium text-emerald-600 dark:text-emerald-500 mb-1">
                                <Package class="w-4 h-4" /> Est. Hasil
                            </div>
                            <div class="text-sm font-bold text-emerald-700 dark:text-emerald-400">{{ planting.estimated_yield_kg ? `${planting.estimated_yield_kg} Kg` : '-' }}</div>
                        </div>
                        <div class="bg-gray-50 dark:bg-[#1a241f] rounded-xl p-4 border border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">
                                <Timer class="w-4 h-4" /> Tumbuh
                            </div>
                            <div class="text-sm font-semibold text-gray-900 dark:text-white">{{ planting.crop?.default_hst }} hari (HST)</div>
                        </div>
                    </div>
                    
                    <div v-if="planting.notes" class="mt-4 p-4 bg-gray-50 dark:bg-[#1a241f] rounded-xl border border-gray-100 dark:border-white/5">
                        <div class="flex items-center gap-2 text-xs font-medium text-gray-500 dark:text-gray-400 mb-2">
                            <ClipboardList class="w-4 h-4" /> Catatan
                        </div>
                        <div class="text-sm text-gray-700 dark:text-gray-300">{{ planting.notes }}</div>
                    </div>
                </div>

                <!-- Progress Logs -->
                <div class="glass-card p-6 sm:p-8 animate-slide-up">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-white/5">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                            <Camera class="w-5 h-5 text-emerald-500" /> Catatan Perkembangan
                        </h3>
                        <Link v-if="planting.status!=='harvested'" :href="route('farmer.plantings.progress.create', planting.id)" class="btn-primary text-sm flex items-center gap-1 !px-3 !py-1.5">
                            <Plus class="w-4 h-4" /> Tambah
                        </Link>
                    </div>
                    
                    <div v-if="planting.progress_logs?.length" class="space-y-4">
                        <div v-for="log in planting.progress_logs" :key="log.id" class="flex flex-col sm:flex-row gap-4 p-4 bg-gray-50 dark:bg-[#1a241f] rounded-xl border border-gray-100 dark:border-white/5">
                            <img v-if="log.photo_path" :src="`/storage/${log.photo_path}`" class="w-full sm:w-32 h-32 object-cover rounded-lg flex-shrink-0 border border-gray-200 dark:border-white/10" />
                            <div class="flex-1 flex flex-col justify-center">
                                <p v-if="log.note" class="text-sm text-gray-700 dark:text-gray-300">{{ log.note }}</p>
                                <p v-else class="text-sm text-gray-400 italic">Tanpa catatan</p>
                                <p class="text-xs font-medium text-gray-500 mt-2 flex items-center gap-1">
                                    <CalendarDays class="w-3 h-3" /> {{ formatDate(log.created_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="text-center py-12 border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-xl">
                        <Camera class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                        <p class="text-gray-500 text-sm">Belum ada foto atau catatan perkembangan.</p>
                    </div>
                </div>

                <!-- Adjust Modal -->
                <Teleport to="body">
                    <Transition
                        enter-active-class="transition duration-200 ease-out"
                        enter-from-class="opacity-0"
                        enter-to-class="opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="opacity-100"
                        leave-to-class="opacity-0"
                    >
                        <div v-if="showAdjustModal" class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/50 dark:bg-black/60 backdrop-blur-sm p-4">
                            <div class="glass-card p-6 w-full max-w-md shadow-2xl animate-fade-in">
                                <div class="flex justify-between items-center mb-5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <CalendarDays class="w-5 h-5 text-amber-500" /> Sesuaikan Tanggal Panen
                                    </h3>
                                    <button @click="showAdjustModal=false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors">
                                        <X class="w-5 h-5" />
                                    </button>
                                </div>
                                <form @submit.prevent="adjustHarvest">
                                    <div class="mb-4">
                                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tanggal Panen Baru</label>
                                        <DatePicker
                                            v-model="adjustForm.estimated_harvest_at"
                                            :min-date="planting.planted_at"
                                            placeholder="Pilih tanggal panen"
                                            required
                                        />
                                    </div>

                                    <!-- Estimated Yield Info -->
                                    <div v-if="planting.estimated_yield_kg" class="mb-4 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/10 border border-emerald-200 dark:border-emerald-800/30 flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/50 flex items-center justify-center flex-shrink-0">
                                            <Package class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
                                        </div>
                                        <div>
                                            <div class="text-xs font-semibold text-emerald-700 dark:text-emerald-500">Estimasi Hasil Panen</div>
                                            <div class="text-lg font-bold text-emerald-800 dark:text-emerald-400">{{ planting.estimated_yield_kg }} Kg</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ planting.plant_count }} tanaman × {{ planting.crop?.estimated_yield_per_plant_kg }} kg/tanaman</div>
                                        </div>
                                    </div>

                                    <div class="flex justify-end gap-3 mt-6">
                                        <button type="button" @click="showAdjustModal=false" class="px-5 py-2.5 rounded-xl text-sm font-bold tracking-wide text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 transition-all duration-200">Batal</button>
                                        <button type="submit" :disabled="adjustForm.processing" class="btn-primary text-sm !px-5 !py-2.5 flex items-center gap-2 rounded-xl font-bold shadow-sm">
                                            <Save class="w-4 h-4" /> Simpan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </Transition>
                </Teleport>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
