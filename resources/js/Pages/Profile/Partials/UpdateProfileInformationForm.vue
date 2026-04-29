<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Cropper } from 'vue-advanced-cropper';
import 'vue-advanced-cropper/dist/style.css';

const props = defineProps({
    mustVerifyEmail: {
        type: Boolean,
    },
    status: {
        type: String,
    },
    garden: {
        type: Object,
        default: () => null,
    },
});

const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
    garden: {
        name: props.garden?.name || '',
        address: props.garden?.address || '',
        description: props.garden?.description || '',
        instagram_link: props.garden?.instagram_link || '',
        facebook_link: props.garden?.facebook_link || '',
        youtube_link: props.garden?.youtube_link || '',
        whatsapp_number: props.garden?.whatsapp_number || '',
    },
    garden_photo: null,
    garden_cover: null,
    delete_garden_photo: false,
    delete_garden_cover: false,
    _method: 'patch',
});

const previewImages = ref({
    photo: props.garden?.photo_path ? '/storage/' + props.garden.photo_path : null,
    cover: props.garden?.cover_path ? '/storage/' + props.garden.cover_path : null,
});

const cropperState = ref({
    show: false,
    image: null,
    type: null // 'photo' or 'cover'
});
const cropperRef = ref(null);

const onFileChange = (e, type) => {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = (e) => {
            cropperState.value = {
                show: true,
                image: e.target.result,
                type: type
            };
        };
        reader.readAsDataURL(file);
    }
    e.target.value = null; // reset input
};

const cancelCrop = () => {
    cropperState.value.show = false;
    cropperState.value.image = null;
    cropperState.value.type = null;
};

const saveCrop = () => {
    const { canvas } = cropperRef.value.getResult();
    if (canvas) {
        canvas.toBlob((blob) => {
            if (cropperState.value.type === 'photo') {
                form.garden_photo = new File([blob], 'logo.png', { type: 'image/png' });
                form.delete_garden_photo = false;
                previewImages.value.photo = URL.createObjectURL(blob);
            } else {
                form.garden_cover = new File([blob], 'cover.png', { type: 'image/png' });
                form.delete_garden_cover = false;
                previewImages.value.cover = URL.createObjectURL(blob);
            }
            cancelCrop();
        }, 'image/png');
    }
};

const removeImage = (type) => {
    if (type === 'photo') {
        form.garden_photo = null;
        form.delete_garden_photo = true;
        previewImages.value.photo = null;
    } else {
        form.garden_cover = null;
        form.delete_garden_cover = true;
        previewImages.value.cover = null;
    }
};

const submit = () => {
    form.post(route('profile.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.garden_photo = null;
            form.garden_cover = null;
        },
    });
};
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                Profile Information
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Update your account's profile information and email address.
            </p>
        </header>

        <form
            @submit.prevent="submit"
            class="mt-6 space-y-6"
        >
            <div>
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Garden Info (Only for farmers) -->
            <div v-if="user.role === 'farmer'" class="pt-6 border-t border-gray-200 dark:border-gray-700 space-y-6">
                <header>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Informasi Kebun
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Informasi ini akan ditampilkan ke publik (misal: di Kalender Panen).
                    </p>
                </header>

                <div>
                    <InputLabel for="garden_name" value="Nama Kebun" />
                    <TextInput
                        id="garden_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.garden.name"
                        placeholder="Misal: Kebun Sayur Pak Eko"
                    />
                    <InputError class="mt-2" :message="form.errors['garden.name']" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="garden_photo" value="Logo Kebun (Opsional)" />
                        <div class="mt-1 flex items-center gap-4">
                            <div v-if="previewImages.photo" class="relative w-12 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0 group">
                                <img :src="previewImages.photo" class="w-full h-full object-cover" />
                                <button type="button" @click="removeImage('photo')" class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                            <input
                                id="garden_photo"
                                type="file"
                                @change="onFileChange($event, 'photo')"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-400"
                                accept="image/*"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.garden_photo" />
                    </div>

                    <div>
                        <InputLabel for="garden_cover" value="Cover Kebun (Opsional)" />
                        <div class="mt-1 flex items-center gap-4">
                            <div v-if="previewImages.cover" class="relative w-16 h-12 rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800 flex-shrink-0 group">
                                <img :src="previewImages.cover" class="w-full h-full object-cover" />
                                <button type="button" @click="removeImage('cover')" class="absolute inset-0 bg-black/50 text-white flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                                </button>
                            </div>
                            <input
                                id="garden_cover"
                                type="file"
                                @change="onFileChange($event, 'cover')"
                                class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 dark:file:bg-emerald-900/30 dark:file:text-emerald-400"
                                accept="image/*"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.garden_cover" />
                    </div>
                </div>

                <div>
                    <InputLabel for="garden_address" value="Alamat Kebun" />
                    <textarea
                        id="garden_address"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        v-model="form.garden.address"
                        rows="2"
                        placeholder="Misal: Jl. Pertanian No. 123, Desa Makmur, Kec. Subur"
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors['garden.address']" />
                </div>

                <div>
                    <InputLabel for="garden_description" value="Deskripsi Singkat" />
                    <textarea
                        id="garden_description"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
                        v-model="form.garden.description"
                        rows="3"
                        placeholder="Ceritakan sedikit tentang kebun Anda..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors['garden.description']" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="garden_whatsapp" value="No. WhatsApp" />
                        <TextInput
                            id="garden_whatsapp"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.garden.whatsapp_number"
                            placeholder="Misal: 08123456789"
                        />
                        <InputError class="mt-2" :message="form.errors['garden.whatsapp_number']" />
                    </div>
                    <div>
                        <InputLabel for="garden_instagram" value="Link Instagram" />
                        <TextInput
                            id="garden_instagram"
                            type="url"
                            class="mt-1 block w-full"
                            v-model="form.garden.instagram_link"
                            placeholder="https://instagram.com/..."
                        />
                        <InputError class="mt-2" :message="form.errors['garden.instagram_link']" />
                    </div>
                    <div>
                        <InputLabel for="garden_facebook" value="Link Facebook" />
                        <TextInput
                            id="garden_facebook"
                            type="url"
                            class="mt-1 block w-full"
                            v-model="form.garden.facebook_link"
                            placeholder="https://facebook.com/..."
                        />
                        <InputError class="mt-2" :message="form.errors['garden.facebook_link']" />
                    </div>
                    <div>
                        <InputLabel for="garden_youtube" value="Link YouTube" />
                        <TextInput
                            id="garden_youtube"
                            type="url"
                            class="mt-1 block w-full"
                            v-model="form.garden.youtube_link"
                            placeholder="https://youtube.com/..."
                        />
                        <InputError class="mt-2" :message="form.errors['garden.youtube_link']" />
                    </div>
                </div>
            </div>

            <div v-if="mustVerifyEmail && user.email_verified_at === null">
                <p class="mt-2 text-sm text-gray-800 dark:text-gray-200">
                    Your email address is unverified.
                    <Link
                        :href="route('verification.send')"
                        method="post"
                        as="button"
                        class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:text-gray-400 dark:hover:text-gray-100 dark:focus:ring-offset-gray-800"
                    >
                        Click here to re-send the verification email.
                    </Link>
                </p>

                <div
                    v-show="status === 'verification-link-sent'"
                    class="mt-2 text-sm font-medium text-green-600 dark:text-green-400"
                >
                    A new verification link has been sent to your email address.
                </div>
            </div>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Save</PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p
                        v-if="form.recentlySuccessful"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </section>

    <Modal :show="cropperState.show" @close="cancelCrop">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">
                Sesuaikan {{ cropperState.type === 'photo' ? 'Logo' : 'Cover' }}
            </h2>
            <div class="w-full h-64 sm:h-96 bg-gray-100 dark:bg-gray-900 rounded-lg overflow-hidden">
                <cropper
                    v-if="cropperState.show"
                    ref="cropperRef"
                    class="h-full w-full"
                    :src="cropperState.image"
                    :stencil-props="{
                        aspectRatio: cropperState.type === 'photo' ? 1 : 21/9
                    }"
                />
            </div>
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="cancelCrop">Batal</SecondaryButton>
                <PrimaryButton @click="saveCrop">Simpan Potongan</PrimaryButton>
            </div>
        </div>
    </Modal>
</template>
