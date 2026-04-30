<script setup>
import { Sprout, LayoutDashboard, CalendarDays, ChevronDown, Menu, X, UserCircle, LogOut, Wallet, CloudSun, Eye, Share2, Sun, Moon, Monitor } from 'lucide-vue-next';
import { ref, computed, onMounted } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth.user);
const userRole = computed(() => page.props.auth.user?.role);
const isFarmer = computed(() => userRole.value === 'farmer');
const isBuyer = computed(() => userRole.value === 'buyer');

const currentTheme = ref(localStorage.getItem('theme') || 'auto');

const applyTheme = (theme) => {
    currentTheme.value = theme;
    localStorage.setItem('theme', theme);
    
    const isDark = theme === 'dark' || (theme === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    
    if (isDark) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

onMounted(() => {
    applyTheme(currentTheme.value);
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
        if (currentTheme.value === 'auto') {
            if (e.matches) document.documentElement.classList.add('dark');
            else document.documentElement.classList.remove('dark');
        }
    });
});

function shareGarden() {
    const garden = page.props.auth.user.garden;
    if (!garden) return;
    
    const url = route('garden.show', garden.id);
    const fullUrl = window.location.origin + url;
    
    if (navigator.share) {
        navigator.share({
            title: garden.name,
            text: `Yuk lihat kebun saya di Kebunku: ${garden.name}`,
            url: fullUrl,
        });
    } else {
        navigator.clipboard.writeText(fullUrl);
        alert('Link kebun berhasil disalin ke clipboard!');
    }
}
</script>

<template>
    <div class="min-h-screen bg-gray-50 dark:bg-[#0b110e] transition-colors duration-200">
        <nav class="bg-white dark:bg-[#131b17] border-b border-gray-200 dark:border-white/5 sticky top-0 z-40">
            <!-- Primary Navigation Menu -->
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('homepage')"
                                    class="flex items-center gap-2 text-emerald-600">
                                    <Sprout class="w-7 h-7" />
                                    <span class="text-xl font-bold tracking-tight">Kebunku</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-2 sm:-my-px sm:ms-8 sm:flex sm:items-center">
                                <template v-if="isAuthenticated">
                                    <NavLink :href="route('dashboard')" :active="route().current('dashboard')">
                                        <div class="flex items-center gap-2">
                                            <LayoutDashboard class="w-4 h-4" />
                                            Beranda
                                        </div>
                                    </NavLink>
                                </template>

                                <!-- Farmer nav -->
                                <template v-if="isFarmer">
                                    <NavLink :href="route('farmer.plantings.index')"
                                        :active="route().current('farmer.plantings.*')">
                                        <div class="flex items-center gap-2">
                                            <Sprout class="w-4 h-4" />
                                            Penanaman
                                        </div>
                                    </NavLink>
                                    <NavLink :href="route('farmer.finances.index')"
                                        :active="route().current('farmer.finances.*')">
                                        <div class="flex items-center gap-2">
                                            <Wallet class="w-4 h-4" />
                                            Keuangan
                                        </div>
                                    </NavLink>
                                    <NavLink :href="route('farmer.weather.index')"
                                        :active="route().current('farmer.weather.*')">
                                        <div class="flex items-center gap-2">
                                            <CloudSun class="w-4 h-4" />
                                            Cuaca
                                        </div>
                                    </NavLink>
                                </template>

                                <!-- Buyer nav -->
                                <template v-if="isBuyer || !isAuthenticated">
                                    <NavLink :href="route('calendar')" :active="route().current('calendar')">
                                        <div class="flex items-center gap-2">
                                            <CalendarDays class="w-4 h-4" />
                                            Kalender Panen
                                        </div>
                                    </NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center">
                            <template v-if="isAuthenticated">
                                <!-- Refined Garden Badge -->
                                <div v-if="isFarmer && $page.props.auth.user.garden"
                                    class="mr-4 flex items-center rounded-full border border-emerald-200 dark:border-emerald-800/60 bg-emerald-50/50 dark:bg-emerald-900/20 shadow-sm overflow-hidden h-9">
                                    <!-- Garden Info -->
                                    <Link :href="route('garden.show', $page.props.auth.user.garden.id)"
                                        class="flex items-center gap-2 px-3 h-full hover:bg-emerald-100 dark:hover:bg-emerald-900/40 transition-colors border-r border-emerald-200 dark:border-emerald-800/60 max-w-[200px] group">
                                        <div
                                            class="w-6 h-6 rounded-full bg-emerald-100 flex items-center justify-center overflow-hidden flex-shrink-0">
                                            <img v-if="$page.props.auth.user.garden.photo_path"
                                                :src="`/storage/${$page.props.auth.user.garden.photo_path}`"
                                                class="w-full h-full object-cover" />
                                            <Sprout v-else class="w-4 h-4 text-emerald-500" />
                                        </div>
                                        <span
                                            class="text-[10px] font-black text-emerald-700 dark:text-emerald-400 uppercase tracking-tighter truncate">
                                            {{ $page.props.auth.user.garden.name }}
                                        </span>
                                    </Link>

                                    <!-- Quick Actions -->
                                    <div class="flex items-center h-full">
                                        <Link :href="route('garden.show', $page.props.auth.user.garden.id)"
                                            target="_blank" title="Pratinjau Publik"
                                            class="px-2.5 h-full flex items-center hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-600 dark:text-emerald-500 transition-colors border-r border-emerald-200 dark:border-emerald-800/60">
                                            <Eye class="w-3.5 h-3.5" />
                                        </Link>
                                        <button @click="shareGarden" title="Bagikan Kebun"
                                            class="px-2.5 h-full flex items-center hover:bg-emerald-100 dark:hover:bg-emerald-900/40 text-emerald-600 dark:text-emerald-500 transition-colors">
                                            <Share2 class="w-3.5 h-3.5" />
                                        </button>
                                    </div>
                                </div>
                                <span v-else
                                    class="mr-4 px-3 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full border shadow-sm"
                                    :class="isFarmer ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                                    {{ isFarmer ? 'Petani' : 'Pembeli' }}
                                </span>

                                <!-- Theme Toggle Dropdown -->
                                <div class="relative ms-3">
                                    <Dropdown align="right" width="36">
                                        <template #trigger>
                                            <button type="button" class="p-2 text-gray-500 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-gray-100 dark:hover:bg-white/5 rounded-xl transition-all duration-200">
                                                <Sun v-if="currentTheme === 'light'" class="w-5 h-5" />
                                                <Moon v-else-if="currentTheme === 'dark'" class="w-5 h-5" />
                                                <Monitor v-else class="w-5 h-5" />
                                            </button>
                                        </template>
                                        <template #content>
                                            <button @click="applyTheme('light')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5" :class="currentTheme === 'light' ? 'text-emerald-600 font-bold' : ''">
                                                <Sun class="w-4 h-4" /> Terang
                                            </button>
                                            <button @click="applyTheme('dark')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5" :class="currentTheme === 'dark' ? 'text-emerald-600 font-bold' : ''">
                                                <Moon class="w-4 h-4" /> Gelap
                                            </button>
                                            <button @click="applyTheme('auto')" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5" :class="currentTheme === 'auto' ? 'text-emerald-600 font-bold' : ''">
                                                <Monitor class="w-4 h-4" /> Auto
                                            </button>
                                        </template>
                                    </Dropdown>
                                </div>

                                <!-- Settings Dropdown -->
                                <div class="relative ms-3">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-transparent px-4 py-2 text-sm font-bold tracking-wide text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white focus:outline-none transition-all duration-200">
                                                    {{ $page.props.auth.user.name }}
                                                    <ChevronDown class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                                <UserCircle class="w-4 h-4" /> Profil
                                            </DropdownLink>
                                            <DropdownLink :href="route('logout')" method="post" as="button"
                                                class="flex items-center gap-2">
                                                <LogOut class="w-4 h-4" /> Keluar
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <Link :href="route('login')"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-bold tracking-wide text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
                                        Masuk
                                    </Link>
                                    <Link :href="route('register')"
                                        class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-bold tracking-wide text-white bg-emerald-600 hover:bg-emerald-500 shadow-sm transition-all duration-200">
                                        Daftar
                                    </Link>
                                </div>
                            </template>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none transition-colors">
                                <Menu v-if="!showingNavigationDropdown" class="w-6 h-6" />
                                <X v-else class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div v-show="showingNavigationDropdown" class="sm:hidden border-t border-gray-200">
                    <div class="space-y-1 pb-3 pt-2">
                        <template v-if="isAuthenticated">
                            <ResponsiveNavLink :href="route('dashboard')" :active="route().current('dashboard')"
                                class="flex items-center gap-2">
                                <LayoutDashboard class="w-4 h-4" /> Beranda
                            </ResponsiveNavLink>
                        </template>

                        <template v-if="isFarmer">
                            <ResponsiveNavLink :href="route('farmer.plantings.index')"
                                :active="route().current('farmer.plantings.*')" class="flex items-center gap-2">
                                <Sprout class="w-4 h-4" /> Penanaman
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('farmer.finances.index')"
                                :active="route().current('farmer.finances.*')" class="flex items-center gap-2">
                                <Wallet class="w-4 h-4" /> Keuangan
                            </ResponsiveNavLink>
                            <ResponsiveNavLink :href="route('farmer.weather.index')"
                                :active="route().current('farmer.weather.*')" class="flex items-center gap-2">
                                <CloudSun class="w-4 h-4" /> Cuaca
                            </ResponsiveNavLink>
                        </template>

                        <template v-if="isBuyer || !isAuthenticated">
                            <ResponsiveNavLink :href="route('calendar')" :active="route().current('calendar')"
                                class="flex items-center gap-2">
                                <CalendarDays class="w-4 h-4" /> Kalender Panen
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <template v-if="isAuthenticated">
                        <div class="border-t border-gray-200 pb-1 pt-4">
                            <div class="px-4 flex justify-between items-center">
                                <div>
                                    <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                                        {{ $page.props.auth.user.name }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ $page.props.auth.user.email }}
                                    </div>
                                </div>
                                <div v-if="isFarmer && $page.props.auth.user.garden"
                                    class="flex items-center gap-2 px-3 py-1 rounded-full border border-emerald-200 bg-emerald-50">
                                    <div
                                        class="w-5 h-5 rounded-full bg-emerald-100 flex items-center justify-center overflow-hidden">
                                        <img v-if="$page.props.auth.user.garden.photo_path"
                                            :src="`/storage/${$page.props.auth.user.garden.photo_path}`"
                                            class="w-full h-full object-cover" />
                                        <Sprout v-else class="w-3 h-3 text-emerald-500" />
                                    </div>
                                    <span
                                        class="text-[10px] font-black text-emerald-700 uppercase tracking-tighter">{{
                                        $page.props.auth.user.garden.name }}</span>
                                </div>
                                <span v-else
                                    class="px-3 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full border shadow-sm"
                                    :class="isFarmer ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                                    {{ isFarmer ? 'Petani' : 'Pembeli' }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile.edit')" class="flex items-center gap-2">
                                    <UserCircle class="w-4 h-4" /> Profil
                                </ResponsiveNavLink>
                                <ResponsiveNavLink :href="route('logout')" method="post" as="button"
                                    class="flex items-center gap-2">
                                    <LogOut class="w-4 h-4" /> Keluar
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="border-t border-gray-200 pb-4 pt-4 px-4 space-y-3">
                            <Link :href="route('login')"
                                class="block w-full text-center py-2.5 px-4 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-bold tracking-wide transition-colors hover:bg-gray-200 dark:hover:bg-white/10">
                                Masuk
                            </Link>
                            <Link :href="route('register')"
                                class="block w-full text-center py-2.5 px-4 rounded-xl text-white bg-emerald-600 hover:bg-emerald-500 font-bold tracking-wide shadow-sm transition-colors">
                                Daftar
                            </Link>
                        </div>
                    </template>
                </div>
            </nav>

            <!-- Page Heading -->
            <header class="bg-white dark:bg-[#131b17] border-b border-gray-200 dark:border-white/5"
                v-if="$slots.header">
                <div class="mx-auto max-w-screen-2xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
</template>
