<script setup>
import { Link } from '@inertiajs/vue3';
import { Sprout } from 'lucide-vue-next';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center bg-gray-50 pt-6 sm:justify-center sm:pt-0"
    >
        <div>
            <Link href="/" class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 bg-emerald-100 text-emerald-600 rounded-2xl flex items-center justify-center group-hover:scale-105 transition-transform">
                    <Sprout class="w-10 h-10" />
                </div>
                <span class="text-2xl font-bold text-gray-900 tracking-tight">Kebunku</span>
            </Link>
        </div>

        <div
            class="mt-8 w-full overflow-hidden glass-card px-8 py-8 sm:max-w-md sm:rounded-2xl"
        >
            <slot />
        </div>
    </div>
</template>

