<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({ planting: Object, crops: Array });

const form = useForm({
    area_hectares: props.planting.area_hectares || '',
    notes: props.planting.notes || '',
    status: props.planting.status,
});

const submit = () => {
    form.patch(route('farmer.plantings.update', props.planting.id));
};
</script>

<template>
    <Head title="Edit Penanaman" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="route('farmer.plantings.show', planting.id)" class="text-forest-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </Link>
                <h2 class="text-2xl font-bold text-white">✏️ Edit Penanaman</h2>
            </div>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
                <form @submit.prevent="submit" class="glass-card p-6 sm:p-8 animate-fade-in">
                    <div class="mb-6 p-4 bg-white/5 rounded-xl flex items-center gap-4">
                        <span class="text-4xl">{{ planting.crop?.icon || '🌱' }}</span>
                        <div>
                            <div class="font-semibold text-white">{{ planting.crop?.name }}</div>
                            <div class="text-sm text-forest-400">{{ planting.crop?.variety }}</div>
                        </div>
                    </div>
                    <div class="mb-6">
                        <InputLabel for="area_hectares" value="Luas Lahan (Ha)" class="text-forest-200" />
                        <input id="area_hectares" type="number" step="0.01" min="0.01" class="mt-1 block w-full bg-white/5 border-white/10 text-white rounded-xl focus:border-forest-400 focus:ring-forest-400" v-model="form.area_hectares" />
                        <InputError class="mt-2" :message="form.errors.area_hectares" />
                    </div>
                    <div class="mb-6">
                        <InputLabel for="notes" value="Catatan" class="text-forest-200" />
                        <textarea id="notes" class="mt-1 block w-full bg-white/5 border-white/10 text-white rounded-xl focus:border-forest-400 focus:ring-forest-400 resize-none" v-model="form.notes" rows="3" />
                        <InputError class="mt-2" :message="form.errors.notes" />
                    </div>
                    <div class="flex items-center justify-end gap-3">
                        <Link :href="route('farmer.plantings.show', planting.id)" class="btn-secondary text-sm !px-4 !py-2">Batal</Link>
                        <PrimaryButton :disabled="form.processing" class="btn-primary !bg-none">💾 Simpan</PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
