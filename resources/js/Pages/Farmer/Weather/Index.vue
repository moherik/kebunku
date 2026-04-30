<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { CloudSun, MapPin, Droplets, Wind, Eye } from 'lucide-vue-next';

const props = defineProps({
    gardens: Array,
    activeGardenId: Number,
    weatherData: Object,
});

const selectedGardenId = ref(props.activeGardenId || (props.gardens.length ? props.gardens[0].id : null));
const currentWeatherData = ref(props.weatherData);
const isLoading = ref(false);

watch(selectedGardenId, async (newId) => {
    if (!newId) return;

    const garden = props.gardens.find(g => g.id === newId);
    if (!garden || !garden.bmkg_adm4_code) {
        currentWeatherData.value = null;
        return;
    }

    isLoading.value = true;
    try {
        const response = await fetch(route('farmer.weather.fetch', newId));
        if (response.ok) {
            currentWeatherData.value = await response.json();
        } else {
            currentWeatherData.value = null;
        }
    } catch (e) {
        console.error('Failed to fetch weather', e);
        currentWeatherData.value = null;
    } finally {
        isLoading.value = false;
    }
});

function formatDate(dateStr) {
    return new Date(dateStr).toLocaleDateString('id-ID', {
        weekday: 'long',
        day: 'numeric',
        month: 'long'
    });
}

function formatTime(dateTimeStr) {
    const d = new Date(dateTimeStr.replace(' ', 'T'));
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}
</script>

<template>
    <Head title="Prakiraan Cuaca" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <CloudSun class="w-6 h-6 text-sky-500" />
                Prakiraan Cuaca
            </h2>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Select Garden -->
                <div v-if="gardens.length > 0" class="glass-card p-6 animate-fade-in">
                    <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                        <MapPin class="w-4 h-4 text-emerald-500" /> Pilih Kebun
                    </label>
                    <select v-model="selectedGardenId" class="w-full sm:max-w-md rounded-xl border-gray-300 bg-white dark:bg-[#131b17] text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <option v-for="g in gardens" :key="g.id" :value="g.id">
                            {{ g.name }} {{ !g.bmkg_adm4_code ? '(Belum ada kode lokasi BMKG)' : '' }}
                        </option>
                    </select>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                        Prakiraan cuaca menggunakan data dari Badan Meteorologi, Klimatologi, dan Geofisika (BMKG).
                    </p>
                </div>

                <div v-else class="glass-card p-6 text-center text-gray-500 dark:text-gray-400 animate-fade-in">
                    Anda belum menambahkan kebun.
                </div>

                <!-- Weather Loading state -->
                <div v-if="isLoading" class="flex justify-center py-12 animate-pulse">
                    <div class="flex flex-col items-center">
                        <CloudSun class="w-12 h-12 text-sky-400 mb-4 animate-bounce" />
                        <p class="text-gray-500 dark:text-gray-400">Mengambil data prakiraan cuaca BMKG...</p>
                    </div>
                </div>

                <!-- Weather Data Display -->
                <template v-else-if="currentWeatherData">
                    <!-- Location info -->
                    <div class="glass-card p-6 bg-gradient-to-br from-sky-50 dark:from-sky-900/20 to-white dark:to-[#131b17] border-t-4 border-t-sky-400 animate-slide-up">
                        <div class="flex items-center gap-3 mb-2">
                            <MapPin class="w-5 h-5 text-sky-600 dark:text-sky-400" />
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                {{ currentWeatherData.location?.kelurahan || 'Lokasi tidak diketahui' }},
                                {{ currentWeatherData.location?.kecamatan || '' }}
                            </h3>
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 pl-8">
                            {{ currentWeatherData.location?.kota || '' }}, {{ currentWeatherData.location?.provinsi || '' }}
                        </p>
                    </div>

                    <!-- Forecasts per day -->
                    <div class="space-y-6">
                        <div v-for="(dayForecasts, date) in currentWeatherData.forecasts" :key="date" class="glass-card p-6 animate-slide-up">
                            <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2 border-b border-gray-100 dark:border-white/5 pb-2">
                                <CalendarDays class="w-5 h-5 text-emerald-500" />
                                {{ formatDate(date) }}
                            </h4>

                            <!-- Scrollable timeline for the day -->
                            <div class="overflow-x-auto pb-4 -mx-2 px-2">
                                <div class="flex gap-4 min-w-max">
                                    <div v-for="forecast in dayForecasts" :key="forecast.datetime" class="flex flex-col items-center bg-white dark:bg-[#131b17] border border-gray-100 dark:border-white/5 rounded-2xl p-4 w-32 shrink-0 relative overflow-hidden group hover:border-sky-200 transition-colors">
                                        <!-- Time -->
                                        <div class="text-sm font-bold text-gray-700 mb-2">
                                            {{ formatTime(forecast.datetime) }}
                                        </div>

                                        <!-- Icon -->
                                        <div class="text-4xl mb-2 drop-shadow-sm group-hover:scale-110 transition-transform">
                                            {{ forecast.weather_icon }}
                                        </div>

                                        <!-- Weather Desc -->
                                        <div class="text-xs font-medium text-gray-600 text-center h-8 flex items-center justify-center">
                                            {{ forecast.weather }}
                                        </div>

                                        <!-- Temp -->
                                        <div class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                                            {{ forecast.temp }}&deg;C
                                        </div>

                                        <!-- Detail Grid -->
                                        <div class="grid grid-cols-2 gap-x-2 gap-y-1 mt-3 text-[10px] w-full pt-3 border-t border-gray-100 dark:border-white/5">
                                            <div class="flex items-center gap-1 text-sky-600" title="Kelembapan">
                                                <Droplets class="w-3 h-3" /> {{ forecast.humidity }}%
                                            </div>
                                            <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400" title="Kecepatan Angin">
                                                <Wind class="w-3 h-3" /> {{ forecast.wind_speed }}kt
                                            </div>
                                            <div class="flex items-center gap-1 text-gray-500 dark:text-gray-400" title="Arah Angin" style="grid-column: span 2">
                                                <span>🧭 {{ forecast.wind_direction }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- No Data State (e.g. Garden selected but no BMKG code) -->
                <div v-else-if="selectedGardenId && !isLoading" class="glass-card p-12 text-center animate-fade-in">
                    <CloudSun class="w-16 h-16 text-gray-300 mx-auto mb-4" />
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Data Cuaca Belum Tersedia</h3>
                    <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">
                        Kebun ini belum dikonfigurasi dengan kode wilayah administrasi BMKG, atau data sedang tidak bisa diakses dari server BMKG.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
