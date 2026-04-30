<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Plus, ChevronRight, Sprout, CalendarDays, LineChart, Wallet, ClipboardList, CloudSun, MapPin, Droplets, Wind, TrendingUp, TrendingDown, LayoutDashboard } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import idLocale from '@fullcalendar/core/locales/id';

const props = defineProps({
    userRole: String,
    stats: Object,
    recentPlantings: Array,
    upcomingHarvests: Array,
    recentActivities: Array,
    financialSummary: Object,
    calendarEvents: Array,
    activeGarden: Object,
});

function formatRupiah(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}

const weatherData = ref(null);
const isLoadingWeather = ref(false);

onMounted(async () => {
    if (props.userRole === 'farmer' && props.activeGarden?.id) {
        isLoadingWeather.value = true;
        try {
            const res = await fetch(route('farmer.weather.fetch', props.activeGarden.id));
            if (res.ok) {
                weatherData.value = await res.json();
            }
        } catch (e) {
            console.error('Failed fetching weather for dashboard', e);
        } finally {
            isLoadingWeather.value = false;
        }
    }
});

const statusLabels = {
    growing: 'Tumbuh',
    'pre-order': 'Pre-Order',
    ready: 'Siap Panen',
    harvested: 'Dipanen',
};

const statusClasses = {
    growing: 'status-growing',
    'pre-order': 'status-pre-order',
    ready: 'status-ready',
    harvested: 'status-harvested',
};

function formatDate(date) {
    return new Date(date).toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
}

const farmTips = [
    "Siram tanaman di pagi hari sebelum jam 9 agar air tidak cepat menguap.",
    "Gunakan pupuk organik secara rutin untuk menjaga kegemburan tanah.",
    "Periksa bagian bawah daun untuk mendeteksi hama sejak dini.",
    "Tanaman pendamping (companion planting) dapat membantu mengusir hama alami.",
    "Rotasi tanaman penting untuk mencegah penularan penyakit tanah.",
    "Pastikan sirkulasi udara baik di sekitar tanaman untuk mencegah jamur.",
];
const currentTip = farmTips[new Date().getDate() % farmTips.length];

const calendarOptions = ref({
    plugins: [dayGridPlugin, listPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: idLocale,
    headerToolbar: {
        left: 'prev,next',
        center: 'title',
        right: 'today',
    },
    height: '400px',
    events: props.calendarEvents || [],
    eventMouseEnter: function (info) {
        showTooltip(info);
    },
    eventMouseLeave: function () {
        scheduleHideTooltip();
    },
    eventClick: function (info) {
        if (info.event.url) {
            info.jsEvent.preventDefault();
            window.location.href = info.event.url;
        }
        hideTooltipNow();
    },
    eventDisplay: 'block',
    dayMaxEvents: 1,
    aspectRatio: 1,
});

const tooltip = ref({ show: false, x: 0, y: 0, date: null, events: [] });
let tooltipHideTimer = null;

function showTooltip(info) {
    clearTimeout(tooltipHideTimer);
    const rect = info.el.getBoundingClientRect();
    const dateStr = info.event.startStr;

    // Get all events for this specific date
    const dateEvents = props.calendarEvents.filter(e => e.start === dateStr);

    tooltip.value = {
        show: true,
        x: rect.left + rect.width / 2,
        y: rect.top - 10,
        date: dateStr,
        events: dateEvents,
    };
}

function scheduleHideTooltip() {
    tooltipHideTimer = setTimeout(() => {
        tooltip.value.show = false;
    }, 200);
}

function cancelHideTooltip() {
    clearTimeout(tooltipHideTimer);
}

function hideTooltipNow() {
    clearTimeout(tooltipHideTimer);
    tooltip.value.show = false;
}
</script>

<template>

    <Head title="Beranda" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold leading-tight text-gray-900 dark:text-white flex items-center gap-2">
                <LayoutDashboard class="w-6 h-6 text-emerald-600 dark:text-emerald-500" />
                {{ userRole === 'farmer' ? 'Dashboard Petani' : 'Dashboard Pembeli' }}
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8">
                <div v-if="userRole === 'farmer'" class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up">

                    <!-- Left Column: Stats, Recent Plantings & Activities -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Stats Grid inside Left Column -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-fade-in">
                            <div class="glass-card p-5">
                                <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_plantings
                                }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Penanaman</div>
                            </div>
                            <div class="glass-card p-5">
                                <div class="text-3xl font-bold text-emerald-600 dark:text-emerald-400">{{ stats.growing
                                }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Sedang Tumbuh</div>
                            </div>
                            <div class="glass-card p-5">
                                <div class="text-3xl font-bold text-amber-500">{{ stats.pre_order }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pre-Order</div>
                            </div>
                            <div class="glass-card p-5">
                                <div class="text-3xl font-bold text-orange-500">{{ stats.ready }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Siap Panen</div>
                            </div>
                        </div>

                        <!-- Financial Mini Widget -->
                        <div class="glass-card p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <Wallet class="w-5 h-5 text-emerald-500" /> Ringkasan Keuangan
                                </h3>
                                <Link :href="route('farmer.finances.index')"
                                    class="text-sm text-emerald-600 dark:text-emerald-400 hover:underline">
                                    Lihat Detail
                                </Link>
                            </div>
                            <div class="grid grid-cols-3 gap-4">
                                <div class="p-3 bg-red-50 dark:bg-red-900/10 rounded-xl">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Pengeluaran</div>
                                    <div class="text-sm font-bold text-red-600 dark:text-red-400 mt-0.5 truncate">{{
                                        formatRupiah(financialSummary.expenses) }}</div>
                                </div>
                                <div class="p-3 bg-emerald-50 dark:bg-emerald-900/10 rounded-xl">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Pendapatan</div>
                                    <div
                                        class="text-sm font-bold text-emerald-600 dark:text-emerald-400 mt-0.5 truncate">
                                        {{
                                            formatRupiah(financialSummary.income) }}</div>
                                </div>
                                <div class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl">
                                    <div class="text-xs text-gray-500 dark:text-gray-400">Profit</div>
                                    <div class="text-sm font-bold mt-0.5 flex items-center gap-1 truncate"
                                        :class="financialSummary.profit >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                        <TrendingUp v-if="financialSummary.profit >= 0" class="w-3 h-3 flex-shrink-0" />
                                        <TrendingDown v-else class="w-3 h-3 flex-shrink-0" />
                                        {{ formatRupiah(Math.abs(financialSummary.profit)) }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Recent Plantings -->
                        <div class="glass-card p-6">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                    <Sprout class="w-5 h-5 text-emerald-500" /> Penanaman Terbaru
                                </h3>
                                <Link :href="route('farmer.plantings.create')"
                                    class="btn-primary text-xs !px-3 !py-1.5 flex items-center gap-1">
                                    <Plus class="w-3 h-3" /> Tambah
                                </Link>
                            </div>

                            <div v-if="recentPlantings?.length > 0" class="space-y-3">
                                <Link v-for="planting in recentPlantings" :key="planting.id"
                                    :href="route('farmer.plantings.show', planting.id)"
                                    class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 border border-transparent hover:border-gray-100 dark:border-white/5 dark:hover:border-white/5 transition-all duration-200 group">
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-xl">
                                            {{ planting.crop?.icon || '🌱' }}
                                        </div>
                                        <div>
                                            <div
                                                class="font-medium text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors">
                                                {{ planting.crop?.name }}
                                                <span v-if="planting.crop?.variety"
                                                    class="text-gray-500 dark:text-gray-400 text-sm ml-1 font-normal">({{
                                                        planting.crop.variety }})</span>
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Ditanam: {{
                                                formatDate(planting.planted_at) }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
                                        <span class="px-2.5 py-1 text-xs font-medium rounded-full"
                                            :class="statusClasses[planting.status]">{{ statusLabels[planting.status]
                                            }}</span>
                                        <ChevronRight
                                            class="w-5 h-5 text-gray-400 dark:text-gray-600 group-hover:text-emerald-500 transition-colors" />
                                    </div>
                                </Link>
                            </div>
                            <div v-else class="text-center py-6">
                                <p class="text-gray-500 dark:text-gray-400 mb-4">Belum ada penanaman.</p>
                                <Link :href="route('farmer.plantings.create')" class="btn-primary text-sm">Mulai Menanam
                                </Link>
                            </div>
                        </div>

                        <!-- Recent Activities -->
                        <div class="glass-card p-6">
                            <h3
                                class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                                <ClipboardList class="w-5 h-5 text-emerald-500" /> Aktivitas Terakhir
                            </h3>
                            <div v-if="recentActivities?.length" class="space-y-3">
                                <Link v-for="act in recentActivities" :key="act.id"
                                    :href="route('farmer.plantings.activities.index', act.planting_id)"
                                    class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 dark:bg-white/5 dark:hover:bg-white/5 transition-colors border border-transparent hover:border-gray-100 dark:border-white/5 dark:hover:border-white/5">
                                    <div
                                        class="w-10 h-10 flex-shrink-0 bg-gray-100 dark:bg-white/10 rounded-xl flex items-center justify-center text-lg">
                                        {{ act.type_icon }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-sm font-bold text-gray-900 dark:text-white truncate">{{
                                            act.type_label }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 truncate">{{
                                            act.planting?.crop?.name
                                        }} · {{ formatDate(act.activity_date) }}</div>
                                    </div>
                                    <ChevronRight class="w-4 h-4 text-gray-400 dark:text-gray-600" />
                                </Link>
                            </div>
                            <div v-else class="text-center py-6">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada catatan aktivitas.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Weather & Extra Widgets -->
                    <div class="space-y-6">
                        <!-- Redesigned Weather Widget -->
                        <div class="glass-card overflow-hidden group animate-fade-in">
                            <div
                                class="p-6 bg-gradient-to-br from-sky-400/10 via-transparent to-emerald-400/5 dark:from-sky-500/10 dark:to-emerald-500/5 border-b border-gray-100 dark:border-white/5">
                                <div class="flex items-center justify-between mb-6">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                        <CloudSun class="w-5 h-5 text-sky-500" /> Cuaca Hari Ini
                                    </h3>
                                    <Link :href="route('farmer.weather.index')"
                                        class="px-3 py-1 text-[10px] font-bold uppercase tracking-wider rounded-full bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400 hover:bg-sky-100 dark:hover:bg-sky-900/50 transition-colors">
                                        Detail</Link>
                                </div>

                                <div v-if="!activeGarden" class="text-center py-6">
                                    <MapPin class="w-10 h-10 text-gray-300 dark:text-gray-600 mx-auto mb-3" />
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Lokasi belum diatur.</p>
                                    <Link :href="route('profile.edit')" class="btn-primary text-xs !px-4 !py-2">Atur
                                        Lokasi
                                    </Link>
                                </div>
                                <div v-else-if="isLoadingWeather"
                                    class="py-10 flex flex-col items-center justify-center">
                                    <div class="relative">
                                        <CloudSun class="w-12 h-12 text-sky-300 animate-pulse" />
                                        <div
                                            class="absolute -top-1 -right-1 w-3 h-3 bg-sky-500 rounded-full animate-ping">
                                        </div>
                                    </div>
                                    <p
                                        class="mt-4 text-xs font-medium text-gray-400 dark:text-gray-500 animate-pulse uppercase tracking-widest">
                                        Sinkronisasi BMKG...</p>
                                </div>
                                <div v-else-if="weatherData && weatherData.forecasts" class="space-y-6">
                                    <!-- Hero Weather -->
                                    <div class="flex items-center gap-6">
                                        <div class="text-6xl drop-shadow-lg animate-float">
                                            {{ Object.values(weatherData.forecasts)[0]?.[0]?.weather_icon }}
                                        </div>
                                        <div>
                                            <div
                                                class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                                                {{ Object.values(weatherData.forecasts)[0]?.[0]?.temp }}&deg;<span
                                                    class="text-2xl font-light text-gray-400 dark:text-gray-500">C</span>
                                            </div>
                                            <div
                                                class="text-sm font-bold text-sky-600 dark:text-sky-400 mt-1 uppercase tracking-wider">
                                                {{ Object.values(weatherData.forecasts)[0]?.[0]?.weather }}
                                            </div>
                                            <div
                                                class="flex items-center gap-1.5 text-[10px] text-gray-500 dark:text-gray-400 mt-2 font-medium">
                                                <MapPin class="w-3 h-3 text-red-400" />
                                                {{ weatherData.location?.kelurahan || activeGarden.name }}
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Mini Timeline -->
                                    <div
                                        class="grid grid-cols-4 gap-2 pt-6 border-t border-gray-100 dark:border-white/5">
                                        <div v-for="forecast in Object.values(weatherData.forecasts)[0]?.slice(1, 5)"
                                            :key="forecast.datetime"
                                            class="text-center group/item p-2 rounded-xl hover:bg-white dark:bg-[#131b17] dark:hover:bg-white/5 transition-colors">
                                            <div
                                                class="text-[9px] font-bold text-gray-400 dark:text-gray-500 uppercase mb-1">
                                                {{
                                                    forecast.datetime.split(' ')[1].substring(0, 5) }}</div>
                                            <div class="text-xl mb-1 group-hover/item:scale-110 transition-transform">{{
                                                forecast.weather_icon }}</div>
                                            <div class="text-xs font-bold text-gray-700 dark:text-gray-300">{{
                                                forecast.temp
                                            }}&deg;</div>
                                        </div>
                                    </div>
                                </div>
                                <div v-else
                                    class="text-center py-6 bg-red-50 dark:bg-red-900/10 rounded-2xl border border-red-100 dark:border-red-900/20">
                                    <CloudSun class="w-8 h-8 text-red-400 mx-auto mb-2 opacity-50" />
                                    <p class="text-xs text-red-500 font-medium">Gagal memuat data BMKG.</p>
                                    <button @click="window.location.reload()"
                                        class="mt-2 text-[10px] font-bold text-red-600 underline">Coba Lagi</button>
                                </div>
                            </div>
                        </div>

                        <!-- Mini Calendar Widget (Sidebar) -->
                        <div class="glass-card p-4 sm:p-5 animate-slide-up" style="animation-delay: 50ms">
                            <div class="flex items-center justify-between mb-5">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                                    <CalendarDays class="w-5 h-5 text-amber-500" /> Kalender
                                </h3>
                            </div>
                            <div class="custom-mini-calendar side-calendar">
                                <FullCalendar :options="calendarOptions" />
                            </div>

                            <div class="mt-6">
                                <h5 class="text-xs font-semibold text-gray-900 dark:text-white flex items-center">
                                    Panen Mendatang
                                </h5>

                                <div v-if="upcomingHarvests?.length" class="space-y-4 mt-4">
                                    <Link v-for="harvest in upcomingHarvests" :key="harvest.id"
                                        :href="route('farmer.plantings.show', harvest.id)" class="block group">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-900/20 flex items-center justify-center text-xl group-hover:scale-110 transition-transform">
                                                {{ harvest.crop?.icon || '🌾' }}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="text-sm font-bold text-gray-900 dark:text-white truncate group-hover:text-amber-600 transition-colors">
                                                    {{ harvest.crop?.name }}</div>
                                                <div class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">
                                                    {{
                                                        formatDate(harvest.estimated_harvest_at) }}</div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xs font-black text-amber-600">
                                                    {{ Math.ceil((new Date(harvest.estimated_harvest_at) - new
                                                        Date()) /
                                                        (1000 * 60 * 60
                                                            * 24)) }} <span class="font-normal text-[9px]">hari lagi</span>
                                                </div>
                                            </div>
                                        </div>
                                    </Link>
                                </div>
                                <div v-else class="text-center py-6 opacity-50">
                                    <CalendarDays class="w-8 h-8 text-gray-300 dark:text-gray-600 mx-auto mb-2" />
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Belum ada panen
                                        direncanakan.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Farm Tip Widget -->
                        <div class="glass-card p-6 bg-emerald-600 relative overflow-hidden group animate-slide-up"
                            style="animation-delay: 200ms">
                            <!-- Background decoration -->
                            <Sprout
                                class="absolute -right-4 -bottom-4 w-24 h-24 text-white/10 group-hover:rotate-12 transition-transform duration-500" />

                            <h3 class="text-xs font-black uppercase tracking-widest text-emerald-200/60 mb-3">Tip Kebun
                                Hari Ini
                            </h3>
                            <p class="text-sm font-bold text-white leading-relaxed relative z-10">
                                "{{ currentTip }}"
                            </p>
                            <div class="mt-4 flex items-center gap-2 relative z-10">
                                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                                    <Sprout class="w-3 h-3 text-white" />
                                </div>
                                <span class="text-[10px] font-bold text-emerald-100 uppercase tracking-tight">Kebunku
                                    Pintar</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Harvests (Buyer) -->
                <div v-if="userRole === 'buyer'" class="space-y-6 animate-slide-up">
                    <!-- Buyer Stats Grid -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 animate-fade-in">
                        <div class="glass-card p-5 col-span-1">
                            <div class="text-3xl font-bold text-gray-900 dark:text-white">{{ stats.total_available }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Tersedia</div>
                        </div>
                        <div class="glass-card p-5 col-span-1">
                            <div class="text-3xl font-bold text-orange-500 dark:text-orange-400">{{ stats.ready_now }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Siap Sekarang</div>
                        </div>
                        <div class="glass-card p-5 col-span-2">
                            <div class="text-3xl font-bold text-amber-500 dark:text-amber-400">{{ stats.coming_this_week
                            }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Panen Minggu Ini</div>
                        </div>
                    </div>

                    <div class="glass-card p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                                <CalendarDays class="w-5 h-5 text-amber-500" /> Panen Mendatang
                            </h3>
                            <Link :href="route('calendar')" class="btn-harvest text-sm flex items-center gap-2">
                                <CalendarDays class="w-4 h-4" /> Lihat Kalender
                            </Link>
                        </div>

                        <div v-if="upcomingHarvests && upcomingHarvests.length > 0" class="space-y-3">
                            <div v-for="harvest in upcomingHarvests" :key="harvest.id"
                                class="flex items-center justify-between p-4 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5">
                                <div class="flex items-center gap-4">
                                    <div
                                        class="w-10 h-10 rounded-full bg-emerald-100 dark:bg-emerald-900/30 flex items-center justify-center text-xl">
                                        {{ harvest.crop?.icon || '🌾' }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            {{ harvest.crop?.name }}
                                        </div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                                            {{ harvest.user?.name }} · Est. panen: {{
                                                formatDate(harvest.estimated_harvest_at)
                                            }}
                                        </div>
                                    </div>
                                </div>
                                <span class="px-2.5 py-1 text-xs font-medium rounded-full"
                                    :class="statusClasses[harvest.status]">
                                    {{ statusLabels[harvest.status] }}
                                </span>
                            </div>
                        </div>

                        <div v-else class="text-center py-12">
                            <CalendarDays class="w-12 h-12 text-gray-300 dark:text-gray-600 mx-auto mb-4" />
                            <p class="text-gray-500 dark:text-gray-400">Belum ada panen mendatang di area ini.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tooltip for Calendar -->
        <Teleport to="body">
            <div v-if="tooltip.show && tooltip.events.length" class="fixed z-[9999]"
                :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px', transform: 'translate(-50%, -100%)' }"
                @mouseenter="cancelHideTooltip" @mouseleave="hideTooltipNow">
                <div
                    class="bg-white dark:bg-[#131b17] border border-gray-200 dark:border-white/10 rounded-xl p-3 shadow-2xl min-w-[200px] backdrop-blur-md bg-white/95 dark:bg-[#131b17]/95">
                    <div
                        class="text-[10px] font-black text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-2 pb-2 border-b border-gray-100 dark:border-white/5">
                        {{ formatDate(tooltip.date) }}
                    </div>
                    <div class="space-y-1.5">
                        <Link v-for="event in tooltip.events" :key="event.id" :href="event.url"
                            class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/5 transition-colors group/item">
                            <span class="w-2 h-2 rounded-full shrink-0"
                                :style="{ backgroundColor: event.backgroundColor }"></span>
                            <span
                                class="text-xs font-bold text-gray-700 dark:text-gray-200 group-hover/item:text-emerald-500 transition-colors">{{
                                    event.title }}</span>
                        </Link>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>

<style>
.custom-mini-calendar .fc {
    --fc-border-color: rgba(156, 163, 175, 0.1);
    --fc-today-bg-color: rgba(16, 185, 129, 0.05);
    --fc-page-bg-color: transparent;
    font-family: inherit;
}

.dark .custom-mini-calendar .fc {
    --fc-border-color: rgba(255, 255, 255, 0.05);
    --fc-today-bg-color: rgba(16, 185, 129, 0.1);
}

.custom-mini-calendar .fc .fc-toolbar {
    margin-bottom: 0.5rem !important;
}

.custom-mini-calendar .fc .fc-toolbar-title {
    font-size: 0.75rem !important;
    font-weight: 800 !important;
    text-transform: capitalize;
    color: #111827;
}

.dark .custom-mini-calendar .fc .fc-toolbar-title {
    color: #ffffff;
}

.custom-mini-calendar .fc .fc-button {
    padding: 1px 4px !important;
    font-size: 0.6rem !important;
    font-weight: 700 !important;
    border-radius: 4px !important;
    background: #f3f4f6 !important;
    border: none !important;
    color: #4b5563 !important;
}

.dark .custom-mini-calendar .fc .fc-button {
    background: rgba(255, 255, 255, 0.05) !important;
    color: #9ca3af !important;
}

.custom-mini-calendar .fc .fc-button-primary:not(:disabled).fc-button-active,
.custom-mini-calendar .fc .fc-button-primary:not(:disabled):active {
    background: #10b981 !important;
    color: white !important;
}

.custom-mini-calendar .fc .fc-col-header-cell-cushion {
    font-size: 0.6rem !important;
    font-weight: 800 !important;
    text-transform: uppercase;
    color: #9ca3af;
    padding: 4px 0 !important;
}

.custom-mini-calendar .fc .fc-daygrid-day-number {
    font-size: 0.65rem !important;
    font-weight: 600 !important;
    padding: 2px 4px !important;
    color: #6b7280;
}

.dark .custom-mini-calendar .fc .fc-daygrid-day-number {
    color: #9ca3af;
}

.custom-mini-calendar .fc .fc-daygrid-day.fc-day-today {
    background: var(--fc-today-bg-color) !important;
}

.custom-mini-calendar .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
    color: #10b981;
    background: #ecfdf5;
    border-radius: 4px;
    width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 2px;
}

.dark .custom-mini-calendar .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number {
    background: rgba(16, 185, 129, 0.2);
}

.custom-mini-calendar .fc .fc-event {
    height: 3px !important;
    padding: 0 !important;
    overflow: hidden;
    text-indent: -9999px;
    border-radius: 2px !important;
    margin: 1px 2px !important;
    cursor: pointer;
    border: none !important;
}

.custom-mini-calendar .fc .fc-daygrid-event-harness {
    margin-bottom: 0px !important;
}

.custom-mini-calendar .fc .fc-daygrid-day-frame {
    min-height: 30px !important;
}

/* Tooltip animation */
.fixed.z-\[9999\] {
    animation: tooltip-fade-in 0.2s ease-out;
}

@keyframes tooltip-fade-in {
    from {
        opacity: 0;
        transform: translate(-50%, -90%);
    }

    to {
        opacity: 1;
        transform: translate(-50%, -100%);
    }
}
</style>
