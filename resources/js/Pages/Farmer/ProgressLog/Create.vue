<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Camera, Save, X, ChevronLeft, ImagePlus } from 'lucide-vue-next';

const props = defineProps({ planting: Object });

const form = useForm({
    note: '',
    photo: null,
});

const photoPreview = ref(null);

function handlePhotoChange(e) {
    const file = e.target.files[0];
    if (file) {
        form.photo = file;
        const reader = new FileReader();
        reader.onload = (ev) => { photoPreview.value = ev.target.result; };
        reader.readAsDataURL(file);
    }
}

const submit = () => {
    form.post(route('farmer.plantings.progress.store', props.planting.id), {
        forceFormData: true,
    });
};
</script>

<template>
    <Head title="Tambah Catatan Perkembangan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('farmer.plantings.show', planting.id)" class="text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">
                    <ChevronLeft class="w-6 h-6" />
                </Link>
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <Camera class="w-6 h-6 text-emerald-600 dark:text-emerald-500" /> Tambah Catatan
                </h2>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <!-- Crop Info -->
                <div class="glass-card p-4 sm:p-5 mb-6 flex items-center gap-4">
                    <span class="text-3xl bg-gray-50 dark:bg-[#1a241f] w-14 h-14 rounded-2xl flex items-center justify-center border border-gray-100 dark:border-white/5 shadow-sm">{{ planting.crop?.icon || '🌱' }}</span>
                    <div>
                        <h4 class="font-bold text-gray-900 dark:text-white text-lg">{{ planting.crop?.name }}</h4>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Catat perkembangan terbaru untuk tanaman ini</p>
                    </div>
                </div>

                <!-- Form -->
                <form @submit.prevent="submit" class="glass-card p-6 sm:p-8 animate-fade-in">
                    <!-- Photo Upload -->
                    <div class="mb-8">
                        <InputLabel value="Foto (Opsional)" class="text-gray-700 dark:text-gray-300 mb-2 font-bold" />
                        <label class="block cursor-pointer group">
                            <div v-if="!photoPreview" class="border-2 border-dashed border-gray-200 dark:border-gray-700 rounded-2xl p-10 text-center hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/10 transition-all duration-200">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-[#1a241f] border border-gray-100 dark:border-white/5 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform shadow-sm">
                                    <ImagePlus class="w-8 h-8 text-gray-400 group-hover:text-emerald-500 transition-colors" />
                                </div>
                                <span class="text-sm font-bold tracking-wide text-gray-600 dark:text-gray-400 group-hover:text-emerald-600 dark:group-hover:text-emerald-500">Klik untuk mengunggah foto perkembangan</span>
                                <p class="text-xs text-gray-400 dark:text-gray-500 mt-2 font-medium">Maks. 5MB (JPG, PNG, WEBP)</p>
                            </div>
                            <div v-else class="relative group/preview rounded-2xl overflow-hidden border border-gray-200 dark:border-gray-700 shadow-sm">
                                <img :src="photoPreview" class="w-full h-64 object-cover" />
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover/preview:opacity-100 transition-opacity flex items-center justify-center backdrop-blur-sm">
                                    <button type="button" @click.prevent="photoPreview=null; form.photo=null" class="bg-red-500 text-white rounded-full px-4 py-2 hover:bg-red-600 hover:scale-105 transition-all flex items-center gap-2 font-bold text-sm shadow-lg">
                                        <X class="w-4 h-4" /> Hapus Foto
                                    </button>
                                </div>
                            </div>
                            <input type="file" accept="image/*" capture="environment" class="hidden" @change="handlePhotoChange" />
                        </label>
                        <InputError class="mt-2" :message="form.errors.photo" />
                    </div>

                    <!-- Note -->
                    <div class="mb-8">
                        <InputLabel for="note" value="Catatan" class="text-gray-700 dark:text-gray-300 font-bold" />
                        <textarea id="note" class="mt-2 block w-full bg-white dark:bg-[#1a241f] border-gray-200 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-emerald-500 focus:ring-emerald-500 resize-none shadow-sm transition-colors" v-model="form.note" rows="5" placeholder="Tulis perkembangan tanaman, misalnya: 'Hari ini mulai muncul tunas baru' atau 'Penyiraman pagi selesai'..." />
                        <InputError class="mt-2" :message="form.errors.note" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link :href="route('farmer.plantings.show', planting.id)" class="px-5 py-2.5 rounded-xl text-sm font-bold tracking-wide text-gray-600 dark:text-gray-400 bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 transition-all duration-200">Batal</Link>
                        <PrimaryButton :disabled="form.processing" class="!px-5 !py-2.5 rounded-xl !text-sm !font-bold flex items-center gap-2 shadow-sm transition-all duration-200">
                            <Save class="w-4 h-4" />
                            Simpan Catatan
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
