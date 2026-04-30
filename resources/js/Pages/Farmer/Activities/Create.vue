<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import DatePicker from '@/Components/DatePicker.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { ArrowLeft, Save, ClipboardList, Camera, X, Pencil } from 'lucide-vue-next';

const props = defineProps({
    planting: Object,
    activity: {
        type: Object,
        default: null
    },
    activityTypes: Object,
    activityIcons: Object,
});

const isEdit = computed(() => !!props.activity);

const form = useForm({
    type: props.activity?.type || Object.keys(props.activityTypes)[0],
    activity_date: props.activity?.activity_date || new Date().toISOString().split('T')[0],
    note: props.activity?.note || '',
    quantity: props.activity?.quantity || '',
    quantity_unit: props.activity?.quantity_unit || '',
    photo: null,
    _method: isEdit.value ? 'PATCH' : 'POST',
});

const photoPreview = ref(props.activity?.photo_path ? `/storage/${props.activity.photo_path}` : null);

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
    if (isEdit.value) {
        form.post(route('farmer.plantings.activities.update', [props.planting.id, props.activity.id]), {
            forceFormData: true,
        });
    } else {
        form.post(route('farmer.plantings.activities.store', props.planting.id), {
            forceFormData: true,
        });
    }
};
</script>

<template>

    <Head :title="isEdit ? 'Edit Aktivitas' : 'Tambah Aktivitas'" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('farmer.plantings.activities.index', planting.id)"
                    class="text-gray-500 dark:text-gray-400 hover:text-emerald-600 transition-colors p-2 -ml-2 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10">
                    <ArrowLeft class="w-5 h-5" />
                </Link>
                <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                    <component :is="isEdit ? Pencil : ClipboardList" class="w-6 h-6 text-emerald-600" />
                    {{ isEdit ? 'Edit Aktivitas' : 'Tambah Aktivitas Baru' }}
                </h2>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="glass-card p-6 sm:p-8 animate-fade-in">

                    <!-- Activity Type Selection Grid -->
                    <div class="mb-8">
                        <InputLabel value="Jenis Aktivitas" class="text-gray-700 dark:text-gray-300 font-bold mb-3" />
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-5 gap-3">
                            <button v-for="(label, value) in activityTypes" :key="value" type="button"
                                @click="form.type = value"
                                class="flex flex-col items-center p-3 rounded-2xl border-2 transition-all duration-200"
                                :class="form.type === value
                                    ? 'border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-300 shadow-sm scale-[1.02]'
                                    : 'border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 hover:border-emerald-300'">
                                <span class="text-2xl mb-1">{{ activityIcons[value] }}</span>
                                <span class="text-[10px] font-bold uppercase tracking-tight">{{ label }}</span>
                            </button>
                        </div>
                        <InputError class="mt-2" :message="form.errors.type" />
                    </div>

                    <div class="mb-8">
                        <!-- Date -->
                        <div>
                            <InputLabel for="activity_date" value="Tanggal Aktivitas"
                                class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                            <DatePicker id="activity_date" v-model="form.activity_date" placeholder="Pilih tanggal"
                                required />
                            <InputError class="mt-2" :message="form.errors.activity_date" />
                        </div>
                    </div>

                    <!-- Quantity -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <InputLabel for="quantity" value="Jumlah (Opsional)"
                                class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                            <TextInput id="quantity" type="number" step="0.1" class="mt-1 block w-full rounded-xl"
                                v-model="form.quantity" placeholder="Contoh: 10" />
                            <InputError class="mt-2" :message="form.errors.quantity" />
                        </div>
                        <div>
                            <InputLabel for="quantity_unit" value="Satuan"
                                class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                            <TextInput id="quantity_unit" type="text" class="mt-1 block w-full rounded-xl"
                                v-model="form.quantity_unit" placeholder="Contoh: Kg, Liter, Karung" />
                            <InputError class="mt-2" :message="form.errors.quantity_unit" />
                        </div>
                    </div>

                    <!-- Photo -->
                    <div class="mb-8">
                        <InputLabel value="Foto (Opsional)" class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                        <div class="flex items-center gap-4">
                            <div v-if="photoPreview" class="relative group">
                                <img :src="photoPreview"
                                    class="w-32 h-32 object-cover rounded-xl border border-gray-200 dark:border-white/10" />
                                <button type="button" @click="photoPreview = null; form.photo = null"
                                    class="absolute -top-2 -right-2 bg-red-500 text-white p-1 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity">
                                    <X class="w-4 h-4" />
                                </button>
                            </div>
                            <label
                                class="w-32 h-32 rounded-xl border-2 border-dashed border-gray-200 dark:border-white/10 flex flex-col items-center justify-center cursor-pointer hover:border-emerald-500 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-all">
                                <Camera class="w-8 h-8 text-gray-400" />
                                <span class="text-[10px] font-bold text-gray-500 mt-1 uppercase tracking-wider">{{
                                    photoPreview
                                    ? 'Ganti Foto' : 'Pilih Foto' }}</span>
                                <input type="file" class="hidden" accept="image/*" @change="handlePhotoChange" />
                            </label>
                        </div>
                        <InputError class="mt-2" :message="form.errors.photo" />
                    </div>

                    <!-- Note -->
                    <div class="mb-8">
                        <InputLabel for="note" value="Catatan"
                            class="text-gray-700 dark:text-gray-300 font-bold mb-2" />
                        <textarea id="note"
                            class="mt-1 block w-full bg-white dark:bg-white/5 border-gray-300 dark:border-white/10 text-gray-900 dark:text-white rounded-xl focus:border-emerald-500 focus:ring-emerald-500 resize-none shadow-sm transition-colors"
                            v-model="form.note" rows="4" placeholder="Detail aktivitas yang dilakukan..." />
                        <InputError class="mt-2" :message="form.errors.note" />
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-100 dark:border-white/5">
                        <Link :href="route('farmer.plantings.activities.index', planting.id)"
                            class="btn-secondary text-sm">
                            Batal</Link>
                        <PrimaryButton :disabled="form.processing"
                            class="!px-6 !py-2.5 rounded-xl !text-sm !font-bold flex items-center gap-2 shadow-md">
                            <Save class="w-4 h-4" /> {{ isEdit ? 'Simpan Perubahan' : 'Simpan Aktivitas' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
