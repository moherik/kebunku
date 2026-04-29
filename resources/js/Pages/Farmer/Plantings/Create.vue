<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ArrowLeft, Save, Sprout, Hash } from 'lucide-vue-next';
import { computed } from 'vue';

const props = defineProps({
    crops: Array,
});

const form = useForm({
    crop_id: '',
    planted_at: new Date().toISOString().split('T')[0],
    area_hectares: '',
    plant_count: '',
    notes: '',
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

const submit = () => {
    form.post(route('farmer.plantings.store'));
};
</script>

<template>
    <Head title="Tambah Penanaman" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('farmer.plantings.index')" class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <Sprout class="w-6 h-6 text-emerald-600 dark:text-emerald-500" /> Tambah Penanaman Baru
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="animate-fade-in">
                    <div class="lg:grid lg:grid-cols-12 lg:gap-8 items-start">
                        
                        <!-- Left Column: Crop Selection -->
                        <div class="lg:col-span-5 xl:col-span-5 mb-8 lg:mb-0">
                            <div class="glass-card p-6 sm:p-8">
                                <div class="mb-6">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        Pilih Tanaman
                                    </h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pilih komoditas yang akan Anda tanam.</p>
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3 lg:max-h-[500px] overflow-y-auto pr-2 custom-scrollbar">
                                    <button
                                        v-for="crop in crops"
                                        :key="crop.id"
                                        type="button"
                                        @click="form.crop_id = crop.id"
                                        class="p-4 rounded-2xl border-2 text-center transition-all duration-200 flex flex-col items-center"
                                        :class="form.crop_id == crop.id
                                            ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 shadow-sm transform scale-[1.02]'
                                            : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:border-emerald-300 dark:hover:border-emerald-700/50 hover:bg-gray-50 dark:hover:bg-[#1a241f]'"
                                    >
                                        <span class="text-4xl block mb-3 bg-white dark:bg-gray-800 rounded-full w-16 h-16 flex items-center justify-center shadow-sm border border-gray-100 dark:border-gray-700">{{ crop.icon || '🌱' }}</span>
                                        <span class="text-sm font-bold block">{{ crop.name }}</span>
                                        <span class="text-xs opacity-75 mt-0.5 block">{{ crop.variety }}</span>
                                        <span class="text-xs font-bold text-amber-600 dark:text-amber-500 bg-amber-50 dark:bg-amber-900/30 px-2 py-1 rounded-md block mt-3 w-full text-center">
                                            {{ crop.default_hst }} HST
                                        </span>
                                    </button>
                                </div>
                                <InputError class="mt-4" :message="form.errors.crop_id" />
                            </div>
                        </div>

                        <!-- Right Column: Form Details -->
                        <div class="lg:col-span-7 xl:col-span-7">
                            <div class="glass-card p-6 sm:p-8">
                                <div class="mb-8 pb-4 border-b border-gray-100 dark:border-white/5">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Detail Penanaman</h3>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Lengkapi informasi tanggal dan lahan.</p>
                                </div>

                                <!-- Planting Date -->
                                <div class="mb-6">
                                    <InputLabel for="planted_at" value="Tanggal Tanam" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                                    <DatePicker
                                        id="planted_at"
                                        v-model="form.planted_at"
                                        :max-date="new Date()"
                                        placeholder="Pilih tanggal tanam"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.planted_at" />
                                </div>

                                <!-- Estimated Harvest Preview -->
                                <div v-if="estimatedHarvest()" class="mb-8 p-5 rounded-xl bg-amber-50 dark:bg-amber-900/10 border border-amber-200 dark:border-amber-800/30 flex items-start gap-4 transition-all duration-300 animate-fade-in">
                                    <div class="w-12 h-12 rounded-full bg-amber-100 dark:bg-amber-900/50 flex items-center justify-center flex-shrink-0 text-amber-600 dark:text-amber-400 shadow-inner">
                                        <CalendarDays class="w-6 h-6" />
                                    </div>
                                    <div class="flex-1">
                                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                            <div>
                                                <div class="text-sm font-semibold text-amber-800 dark:text-amber-500">Estimasi Waktu Panen</div>
                                                <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ estimatedHarvest() }}</div>
                                            </div>
                                            <div v-if="estimatedYieldKg" class="bg-white dark:bg-[#1a241f] border border-amber-200 dark:border-amber-800/50 px-4 py-2 rounded-lg text-center shadow-sm">
                                                <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">Estimasi Hasil (Kg)</div>
                                                <div class="text-lg font-bold text-emerald-600 dark:text-emerald-500">{{ estimatedYieldKg }} Kg</div>
                                            </div>
                                        </div>
                                        <div v-if="selectedCrop" class="text-sm text-amber-600 dark:text-amber-400 mt-3 font-medium bg-white/50 dark:bg-black/20 inline-block px-2 py-0.5 rounded-md">
                                            Berdasarkan referensi {{ selectedCrop.default_hst }} Hari Setelah Tanam
                                        </div>
                                    </div>
                                </div>

                                <!-- Area & Plant Count -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8">
                                    <div>
                                        <InputLabel for="area_hectares" value="Luas Lahan (Hektar)" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Hash class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <TextInput
                                                id="area_hectares"
                                                type="number"
                                                step="0.01"
                                                min="0.01"
                                                class="mt-1 block w-full pl-10 rounded-xl"
                                                v-model="form.area_hectares"
                                                placeholder="Contoh: 0.5"
                                            />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.area_hectares" />
                                    </div>

                                    <div>
                                        <InputLabel for="plant_count" value="Jumlah Tanaman (Opsional)" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <Sprout class="h-5 w-5 text-gray-400" />
                                            </div>
                                            <TextInput
                                                id="plant_count"
                                                type="number"
                                                min="1"
                                                class="mt-1 block w-full pl-10 rounded-xl"
                                                v-model="form.plant_count"
                                                placeholder="Contoh: 1000"
                                            />
                                        </div>
                                        <InputError class="mt-2" :message="form.errors.plant_count" />
                                    </div>
                                </div>

                                <!-- Notes -->
                                <div class="mb-8">
                                    <InputLabel for="notes" value="Catatan (Opsional)" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                                    <textarea
                                        id="notes"
                                        class="mt-1 block w-full bg-white dark:bg-[#1a241f] border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-emerald-500 focus:ring-emerald-500 resize-none shadow-sm transition-colors"
                                        v-model="form.notes"
                                        rows="4"
                                        placeholder="Contoh: Menggunakan pupuk organik, lahan bekas padi..."
                                    />
                                    <InputError class="mt-2" :message="form.errors.notes" />
                                </div>

                                <!-- Submit -->
                                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                                    <Link
                                        :href="route('farmer.plantings.index')"
                                        class="px-5 py-2.5 rounded-xl text-sm font-bold tracking-wide text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 transition-all duration-200"
                                    >
                                        Batal
                                    </Link>
                                    <PrimaryButton
                                        :class="{ 'opacity-50 cursor-not-allowed': form.processing || !form.crop_id }"
                                        :disabled="form.processing || !form.crop_id"
                                        class="!px-6 !py-2.5 rounded-xl !text-sm !font-bold flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200"
                                    >
                                        <Save class="w-4 h-4" /> Simpan Penanaman
                                    </PrimaryButton>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
