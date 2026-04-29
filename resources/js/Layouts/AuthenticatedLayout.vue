<script setup>
import { ref, computed } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Sprout, LayoutDashboard, CalendarDays, ChevronDown, Menu, X, UserCircle, LogOut } from 'lucide-vue-next';

const showingNavigationDropdown = ref(false);

const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth.user);
const userRole = computed(() => page.props.auth.user?.role);
const isFarmer = computed(() => userRole.value === 'farmer');
const isBuyer = computed(() => userRole.value === 'buyer');
</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-50 dark:bg-[#0b110e] transition-colors duration-200">
            <nav class="bg-white dark:bg-[#131b17] border-b border-gray-200 dark:border-white/5 sticky top-0 z-40">
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="flex items-center gap-2 text-emerald-600 dark:text-emerald-500">
                                    <Sprout class="w-7 h-7" />
                                    <span class="text-xl font-bold tracking-tight">Kebunku</span>
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div class="hidden space-x-2 sm:-my-px sm:ms-8 sm:flex sm:items-center">
                                <template v-if="isAuthenticated">
                                    <NavLink
                                        :href="route('dashboard')"
                                        :active="route().current('dashboard')"
                                    >
                                        <div class="flex items-center gap-2">
                                            <LayoutDashboard class="w-4 h-4" />
                                            Beranda
                                        </div>
                                    </NavLink>
                                </template>

                                <!-- Farmer nav -->
                                <template v-if="isFarmer">
                                    <NavLink
                                        :href="route('farmer.plantings.index')"
                                        :active="route().current('farmer.plantings.*')"
                                    >
                                        <div class="flex items-center gap-2">
                                            <Sprout class="w-4 h-4" />
                                            Penanaman
                                        </div>
                                    </NavLink>
                                </template>

                                <!-- Buyer nav -->
                                <template v-if="isBuyer || !isAuthenticated">
                                    <NavLink
                                        :href="route('calendar')"
                                        :active="route().current('calendar')"
                                    >
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
                                <!-- Role badge -->
                                <span
                                    class="mr-4 px-3 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full border shadow-sm"
                                    :class="isFarmer ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800/60' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-400 dark:border-amber-800/60'"
                                >
                                    {{ isFarmer ? 'Petani' : 'Pembeli' }}
                                </span>

                                <!-- Settings Dropdown -->
                                <div class="relative ms-3">
                                    <Dropdown align="right" width="48">
                                        <template #trigger>
                                            <span class="inline-flex rounded-md">
                                                <button
                                                    type="button"
                                                    class="inline-flex items-center gap-2 rounded-xl bg-transparent px-4 py-2 text-sm font-bold tracking-wide text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white focus:outline-none transition-all duration-200"
                                                >
                                                    {{ $page.props.auth.user.name }}
                                                    <ChevronDown class="w-4 h-4 text-gray-500 dark:text-gray-400" />
                                                </button>
                                            </span>
                                        </template>

                                        <template #content>
                                            <DropdownLink :href="route('profile.edit')" class="flex items-center gap-2">
                                                <UserCircle class="w-4 h-4" /> Profil
                                            </DropdownLink>
                                            <DropdownLink :href="route('logout')" method="post" as="button" class="flex items-center gap-2">
                                                <LogOut class="w-4 h-4" /> Keluar
                                            </DropdownLink>
                                        </template>
                                    </Dropdown>
                                </div>
                            </template>
                            <template v-else>
                                <div class="flex items-center gap-3">
                                    <Link :href="route('login')" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-bold tracking-wide text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/5 hover:text-gray-900 dark:hover:text-white transition-all duration-200">
                                        Masuk
                                    </Link>
                                    <Link :href="route('register')" class="inline-flex items-center justify-center px-4 py-2 rounded-xl text-sm font-bold tracking-wide text-white bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 shadow-sm transition-all duration-200">
                                        Daftar
                                    </Link>
                                </div>
                            </template>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="showingNavigationDropdown = !showingNavigationDropdown"
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 focus:outline-none transition-colors"
                            >
                                <Menu v-if="!showingNavigationDropdown" class="w-6 h-6" />
                                <X v-else class="w-6 h-6" />
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    v-show="showingNavigationDropdown"
                    class="sm:hidden border-t border-gray-200 dark:border-white/5"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <template v-if="isAuthenticated">
                            <ResponsiveNavLink
                                :href="route('dashboard')"
                                :active="route().current('dashboard')"
                                class="flex items-center gap-2"
                            >
                                <LayoutDashboard class="w-4 h-4" /> Beranda
                            </ResponsiveNavLink>
                        </template>

                        <template v-if="isFarmer">
                            <ResponsiveNavLink
                                :href="route('farmer.plantings.index')"
                                :active="route().current('farmer.plantings.*')"
                                class="flex items-center gap-2"
                            >
                                <Sprout class="w-4 h-4" /> Penanaman
                            </ResponsiveNavLink>
                        </template>

                        <template v-if="isBuyer || !isAuthenticated">
                            <ResponsiveNavLink
                                :href="route('calendar')"
                                :active="route().current('calendar')"
                                class="flex items-center gap-2"
                            >
                                <CalendarDays class="w-4 h-4" /> Kalender Panen
                            </ResponsiveNavLink>
                        </template>
                    </div>

                    <!-- Responsive Settings Options -->
                    <template v-if="isAuthenticated">
                        <div class="border-t border-gray-200 dark:border-white/5 pb-1 pt-4">
                            <div class="px-4 flex justify-between items-center">
                                <div>
                                    <div class="text-base font-medium text-gray-800 dark:text-gray-200">
                                        {{ $page.props.auth.user.name }}
                                    </div>
                                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400">
                                        {{ $page.props.auth.user.email }}
                                    </div>
                                </div>
                                <span
                                    class="px-3 py-1.5 text-xs font-bold uppercase tracking-wider rounded-full border shadow-sm"
                                    :class="isFarmer ? 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-900/40 dark:text-emerald-400 dark:border-emerald-800/60' : 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-900/40 dark:text-amber-400 dark:border-amber-800/60'"
                                >
                                    {{ isFarmer ? 'Petani' : 'Pembeli' }}
                                </span>
                            </div>

                            <div class="mt-3 space-y-1">
                                <ResponsiveNavLink :href="route('profile.edit')" class="flex items-center gap-2">
                                    <UserCircle class="w-4 h-4" /> Profil
                                </ResponsiveNavLink>
                                <ResponsiveNavLink
                                    :href="route('logout')"
                                    method="post"
                                    as="button"
                                    class="flex items-center gap-2"
                                >
                                    <LogOut class="w-4 h-4" /> Keluar
                                </ResponsiveNavLink>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <div class="border-t border-gray-200 dark:border-white/5 pb-4 pt-4 px-4 space-y-3">
                            <Link :href="route('login')" class="block w-full text-center py-2.5 px-4 rounded-xl bg-gray-100 dark:bg-white/5 text-gray-700 dark:text-gray-300 font-bold tracking-wide transition-colors hover:bg-gray-200 dark:hover:bg-white/10">
                                Masuk
                            </Link>
                            <Link :href="route('register')" class="block w-full text-center py-2.5 px-4 rounded-xl text-white bg-emerald-600 hover:bg-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-400 font-bold tracking-wide shadow-sm transition-colors">
                                Daftar
                            </Link>
                        </div>
                    </template>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white dark:bg-[#131b17] border-b border-gray-200 dark:border-white/5"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>

