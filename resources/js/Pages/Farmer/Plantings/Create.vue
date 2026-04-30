<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DatePicker from '@/Components/DatePicker.vue';
import Modal from '@/Components/Modal.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, Sprout, Hash, CalendarDays, Plus, X, Pencil } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps({
    crops: Array,
    planting: {
        type: Object,
        default: null
    }
});

const isEdit = computed(() => !!props.planting);
const showCropModal = ref(false);

const form = useForm({
    crop_id: props.planting?.crop_id || '',
    planted_at: props.planting?.planted_at?.split('T')[0] || new Date().toISOString().split('T')[0],
    area_hectares: props.planting?.area_hectares || '',
    plant_count: props.planting?.plant_count || '',
    notes: props.planting?.notes || '',
    status: props.planting?.status || 'growing',
});

const selectedCrop = computed(() => {
    return props.crops.find(c => c.id == form.crop_id);
});

const estimatedYieldKg = computed(() => {
    if (selectedCrop.value && selectedCrop.value.estimated_yield_per_plant_kg && form.plant_count) {
        return (selectedCrop.value.estimated_yield_per_plant_kg * form.plant_count).toFixed(2);
    }
    return null;
});

function estimatedHarvest() {
    if (!form.planted_at || !selectedCrop.value) return null;
    const date = new Date(form.planted_at);
    date.setDate(date.getDate() + selectedCrop.value.default_hst);
    return date.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

function selectCrop(id) {
    form.crop_id = id;
    showCropModal.value = false;
}

const submit = () => {
    if (isEdit.value) {
        form.patch(route('farmer.plantings.update', props.planting.id));
    } else {
        form.post(route('farmer.plantings.store'));
    }
};
</script>

<template>
    <Head :title="isEdit ? 'Edit Penanaman' : 'Tambah Penanaman'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="isEdit ? route('farmer.plantings.show', planting.id) : route('farmer.plantings.index')"
                    class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 dark:bg-white/10">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <component :is="isEdit ? Pencil : Sprout" class="w-6 h-6 text-emerald-600" /> 
                    {{ isEdit ? 'Edit Penanaman' : 'Tambah Penanaman Baru' }}
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="glass-card p-6 sm:p-8 animate-fade-in">

                    <!-- Status Selection (only if editing and not harvested) -->
                    <div class="mb-8" v-if="isEdit && planting.status !== 'harvested'">
                        <InputLabel value="Status Pertumbuhan" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                        <div class="flex flex-wrap gap-2">
                            <button v-for="status in ['growing', 'pre-order', 'ready']" :key="status" type="button" @click="form.status = status"
                                class="px-4 py-2 rounded-xl text-sm font-bold border-2 transition-all"
                                :class="form.status === status 
                                    ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300' 
                                    : 'border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400'">
                                {{ {growing: 'Tumbuh', 'pre-order': 'Pre-Order', ready: 'Siap Panen'}[status] }}
                            </button>
                        </div>
                    </div>

                    <!-- Selected Crop Button -->
                    <div class="mb-8">
                        <InputLabel value="Komoditas" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                        <button type="button" @click="showCropModal = true"
                            class="w-full text-left p-4 rounded-2xl border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 dark:focus:ring-offset-[#131b17]"
                            :class="selectedCrop
                                ? 'border-emerald-500 dark:border-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 text-gray-900 dark:text-white'
                                : 'border-dashed border-gray-300 dark:border-white/10 text-gray-500 dark:text-gray-400 hover:border-emerald-400 dark:hover:border-emerald-500 hover:bg-gray-50 dark:hover:bg-white/5'">
                            <div v-if="selectedCrop" class="flex items-center justify-between">
                                <div class="flex items-center gap-4">
                                    <span
                                        class="text-4xl w-16 h-16 bg-white dark:bg-white/5 rounded-full flex items-center justify-center shadow-sm border border-emerald-100 dark:border-white/5">
                                        {{ selectedCrop.icon || '🌱' }}
                                    </span>
                                    <div>
                                        <div class="font-bold text-lg text-emerald-800 dark:text-emerald-400">{{
                                            selectedCrop.name }}</div>
                                        <div class="text-sm opacity-75">{{ selectedCrop.variety }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="text-sm font-bold text-amber-700 dark:text-amber-400 bg-amber-100 dark:bg-amber-900/30 px-3 py-1.5 rounded-lg border border-amber-200 dark:border-amber-900/50">
                                        {{ selectedCrop.default_hst }} HST
                                    </span>
                                </div>
                            </div>
                            <div v-else class="flex flex-col items-center justify-center py-4">
                                <Plus class="w-8 h-8 text-gray-400 dark:text-gray-500 mb-2" />
                                <span class="font-medium text-gray-600 dark:text-gray-300">Pilih Tanaman</span>
                                <span class="text-sm text-gray-400 dark:text-gray-500 mt-1">Tap untuk memilih
                                    komoditas</span>
                            </div>
                        </button>
                        <InputError class="mt-2" :message="form.errors.crop_id" />
                    </div>

                    <div class="border-t border-gray-100 dark:border-white/5 pt-6 mb-6">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Detail Penanaman</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ isEdit ? 'Sesuaikan informasi tanggal dan lahan.' : 'Lengkapi informasi tanggal dan lahan.' }}</p>
                    </div>

                    <!-- Planting Date -->
                    <div class="mb-6">
                        <InputLabel for="planted_at" value="Tanggal Tanam"
                            class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                        <DatePicker id="planted_at" v-model="form.planted_at" :max-date="new Date()"
                            placeholder="Pilih tanggal tanam" required />
                        <InputError class="mt-2" :message="form.errors.planted_at" />
                    </div>

                    <!-- Estimated Harvest Preview -->
                    <div v-if="estimatedHarvest()"
                        class="mb-8 p-5 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-900/30 flex items-start gap-4 transition-all duration-300 animate-fade-in">
                        <div
                            class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/40 flex items-center justify-center flex-shrink-0 text-amber-600 dark:text-amber-400 shadow-inner">
                            <CalendarDays class="w-6 h-6" />
                        </div>
                        <div class="flex-1">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-amber-800 dark:text-amber-300">Estimasi Waktu Panen</div>
                                    <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ estimatedHarvest() }}</div>
                                </div>
                                <div v-if="estimatedYieldKg"
                                    class="bg-white dark:bg-white/5 border border-amber-200 dark:border-white/10 px-4 py-2 rounded-lg text-center shadow-sm">
                                    <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Estimasi Hasil (Kg)</div>
                                    <div class="text-lg font-bold text-emerald-600 dark:text-emerald-400">{{ estimatedYieldKg }} Kg</div>
                                </div>
                            </div>
                            <div v-if="selectedCrop"
                                class="text-sm text-amber-600 dark:text-amber-400 mt-3 font-medium bg-white/50 dark:bg-white/5 inline-block px-2 py-0.5 rounded-md">
                                Berdasarkan referensi {{ selectedCrop.default_hst }} Hari Setelah Tanam
                            </div>
                        </div>
                    </div>

                    <!-- Area & Plant Count -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                        <div>
                            <InputLabel for="area_hectares" value="Luas Lahan (Hektar)"
                                class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Hash class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                                </div>
                                <TextInput id="area_hectares" type="number" step="0.01" min="0.01"
                                    class="mt-1 block w-full pl-10 rounded-xl" v-model="form.area_hectares"
                                    placeholder="Contoh: 0.5" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.area_hectares" />
                        </div>

                        <div>
                            <InputLabel for="plant_count" value="Jumlah Tanaman (Opsional)"
                                class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <Sprout class="h-5 w-5 text-gray-400 dark:text-gray-500" />
                                </div>
                                <TextInput id="plant_count" type="number" min="1"
                                    class="mt-1 block w-full pl-10 rounded-xl" v-model="form.plant_count"
                                    placeholder="Contoh: 1000" />
                            </div>
                            <InputError class="mt-2" :message="form.errors.plant_count" />
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-8">
                        <InputLabel for="notes" value="Catatan (Opsional)"
                            class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                        <textarea id="notes"
                            class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-emerald-500 focus:ring-emerald-500 resize-none shadow-sm transition-colors"
                            v-model="form.notes" rows="4"
                            placeholder="Contoh: Menggunakan pupuk organik, lahan bekas padi..." />
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link :href="isEdit ? route('farmer.plantings.show', planting.id) : route('farmer.plantings.index')" class="btn-secondary text-sm">
                            Batal
                        </Link>
                        <PrimaryButton :class="{ 'opacity-50 cursor-not-allowed': form.processing || (!isEdit && !form.crop_id) }"
                            :disabled="form.processing || (!isEdit && !form.crop_id)"
                            class="!px-6 !py-2.5 rounded-xl !text-sm !font-bold flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200">
                            <Save class="w-4 h-4" /> {{ isEdit ? 'Simpan Perubahan' : 'Simpan Penanaman' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>

        <!-- Crop Selection Modal -->
        <Modal :show="showCropModal" @close="showCropModal = false" maxWidth="2xl">
            <div class="bg-white dark:bg-[#131b17] border-none rounded-lg">
                <div class="flex p-6 justify-between items-center border-b border-gray-100 dark:border-white/5 pb-4">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white">Pilih Tanaman</h2>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih komoditas yang akan Anda tanam.</p>
                    </div>
                    <button @click="showCropModal = false"
                        class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-white transition-colors">
                        <X class="w-6 h-6" />
                    </button>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[60vh] overflow-y-auto p-6 custom-scrollbar">
                    <button v-for="crop in crops" :key="crop.id" type="button" @click="selectCrop(crop.id)"
                        class="p-4 rounded-2xl border-2 text-center transition-all duration-200 flex flex-col items-center"
                        :class="form.crop_id == crop.id
                            ? 'border-emerald-500 dark:border-emerald-400 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 shadow-sm transform scale-[1.02]'
                            : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:border-emerald-300 hover:bg-gray-50 dark:hover:bg-white/5'">
                        <span
                            class="text-4xl block mb-3 bg-white dark:bg-white/5 rounded-full w-16 h-16 flex items-center justify-center shadow-sm border border-gray-100 dark:border-white/5">{{
                                crop.icon || '🌱' }}</span>
                        <span class="text-sm font-bold block text-gray-900 dark:text-white">{{ crop.name }}</span>
                        <span class="text-xs opacity-75 mt-0.5 block">{{ crop.variety }}</span>
                        <span
                            class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 px-2 py-1 rounded-md block mt-3 w-full text-center">
                            {{ crop.default_hst }} HST
                        </span>
                    </button>
                </div>
            </div>
        </Modal>

    </AuthenticatedLayout>
</template>
