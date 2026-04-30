<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import listPlugin from '@fullcalendar/list';
import interactionPlugin from '@fullcalendar/interaction';
import idLocale from '@fullcalendar/core/locales/id';
import { CalendarDays, Sprout, MessageCircle } from 'lucide-vue-next';

const props = defineProps({
    upcomingPlantings: Array
});

const tooltip = ref({ show: false, x: 0, y: 0, data: null });
const calendarRef = ref(null);
let tooltipHideTimer = null;

function showTooltip(info) {
    clearTimeout(tooltipHideTimer);
    const rect = info.el.getBoundingClientRect();
    tooltip.value = {
        show: true,
        x: rect.left + rect.width / 2,
        y: rect.top - 10,
        data: info.event.extendedProps,
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

const calendarOptions = ref({
    plugins: [dayGridPlugin, listPlugin, interactionPlugin],
    initialView: 'dayGridMonth',
    locale: idLocale,
    headerToolbar: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,listWeek',
    },
    height: 'auto',
    events: function(fetchInfo, successCallback, failureCallback) {
        fetch(`/api/calendar/events?start=${fetchInfo.startStr}&end=${fetchInfo.endStr}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(r => r.json())
        .then(data => successCallback(data))
        .catch(err => failureCallback(err));
    },
    eventMouseEnter: function(info) {
        showTooltip(info);
    },
    eventMouseLeave: function() {
        scheduleHideTooltip();
    },
    eventClick: function(info) {
        hideTooltipNow();
    },
    eventDisplay: 'block',
    dayMaxEvents: 3,
});

const statusLabels = { growing: 'Tumbuh', 'pre-order': 'Pre-Order', ready: 'Siap Panen' };
const statusClasses = { 
    growing: 'bg-emerald-50 text-emerald-700 border-emerald-200', 
    'pre-order': 'bg-amber-50 text-amber-700 border-amber-200', 
    ready: 'bg-orange-50 text-orange-700 border-orange-200' 
};

function formatDate(d) { 
    if(!d) return '-'; 
    return new Date(d).toLocaleDateString('id-ID',{day:'numeric',month:'long',year:'numeric'}); 
}

function openWhatsApp(phone) {
    if (!phone) return;
    const cleaned = phone.replace(/^0/, '62');
    window.open(`https://wa.me/${cleaned}`, '_blank');
}

function goToDate(dateStr) {
    if (calendarRef.value) {
        const calendarApi = calendarRef.value.getApi();
        calendarApi.gotoDate(dateStr);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}
</script>

<template>
    <Head title="Kalender Panen" />
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <CalendarDays class="w-6 h-6 text-emerald-600" /> Kalender Panen
            </h2>
        </template>
        <div class="py-8">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <!-- Legend -->
                <div class="flex flex-wrap gap-6 mb-6 px-2">
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-emerald-500"></span><span class="text-sm font-medium text-gray-600">Tumbuh</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-amber-500"></span><span class="text-sm font-medium text-gray-600">Pre-Order</span></div>
                    <div class="flex items-center gap-2"><span class="w-3 h-3 rounded-full bg-orange-500"></span><span class="text-sm font-medium text-gray-600">Siap Panen</span></div>
                </div>

                <!-- Calendar -->
                <div class="glass-card p-4 sm:p-6 animate-fade-in custom-calendar">
                    <FullCalendar ref="calendarRef" :options="calendarOptions" />
                </div>

                <!-- Upcoming Plantings List -->
                <div class="mt-8 mb-6 animate-slide-up">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Daftar Penanaman</h3>
                    <div v-if="upcomingPlantings?.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div 
                            v-for="planting in upcomingPlantings" 
                            :key="planting.id" 
                            @click="goToDate(planting.estimated_harvest_at)" 
                            class="glass-card p-5 cursor-pointer hover:-translate-y-1 transition-transform border border-transparent hover:border-emerald-500/30"
                        >
                            <div class="flex items-center gap-4">
                                <span class="text-3xl bg-gray-50 dark:bg-white/5 w-14 h-14 rounded-2xl flex items-center justify-center border border-gray-100 dark:border-white/5">{{ planting.crop?.icon || '🌱' }}</span>
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white text-lg">{{ planting.crop?.name }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 flex items-center gap-1 mt-0.5">
                                        <Sprout class="w-3.5 h-3.5" /> {{ planting.user?.name }}
                                    </p>
                                </div>
                            </div>
                            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/5 flex items-center justify-between text-sm">
                                <span class="text-amber-600 font-medium flex items-center gap-1.5">
                                    <CalendarDays class="w-4 h-4" /> {{ formatDate(planting.estimated_harvest_at) }}
                                </span>
                                <span class="px-2.5 py-1 text-xs font-semibold rounded-md border" :class="statusClasses[planting.status]">{{ statusLabels[planting.status] }}</span>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-12 border-2 border-dashed border-gray-200 dark:border-white/10 rounded-2xl">
                        <Sprout class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada data penanaman yang aktif.</p>
                    </div>
                </div>

                <!-- Tooltip -->
                <Teleport to="body">
                    <div
                        v-if="tooltip.show && tooltip.data"
                        class="fixed z-[9999]"
                        :style="{ left: tooltip.x + 'px', top: tooltip.y + 'px', transform: 'translate(-50%, -100%)' }"
                        @mouseenter="cancelHideTooltip"
                        @mouseleave="hideTooltipNow"
                    >
                        <div class="bg-white dark:bg-[#131b17] border border-gray-200 dark:border-white/10 rounded-xl p-4 shadow-xl min-w-[260px]">
                            <div class="flex items-center gap-3 mb-3 pb-3 border-b border-gray-100 dark:border-white/5">
                                <span class="text-2xl bg-gray-50 dark:bg-white/5 w-10 h-10 rounded-full flex items-center justify-center">{{ tooltip.data.crop_icon || '🌱' }}</span>
                                <span class="font-bold text-gray-900 dark:text-white">{{ tooltip.data.crop_name }}</span>
                            </div>
                            <div class="space-y-2 text-sm">
                                <div class="text-gray-600 flex items-center gap-2">
                                    <Sprout class="w-4 h-4" /> 
                                    <span v-if="!tooltip.data.garden">{{ tooltip.data.farmer_name }}</span>
                                    <Link v-else :href="route('garden.show', tooltip.data.garden.id)" class="text-emerald-600 hover:underline font-medium">
                                        {{ tooltip.data.farmer_name }}
                                    </Link>
                                </div>
                                <div class="text-gray-500 dark:text-gray-400">📅 Tanam: {{ tooltip.data.planted_at }}</div>
                                <div class="text-amber-600 font-medium">🌾 Panen: {{ tooltip.data.estimated_harvest_at }}</div>
                                <div v-if="tooltip.data.area_hectares" class="text-gray-500 dark:text-gray-400">📐 {{ tooltip.data.area_hectares }} Ha</div>
                                <div class="text-gray-500 dark:text-gray-400">🌿 HST: {{ tooltip.data.hst }} hari</div>
                            </div>
                            <div v-if="tooltip.data.farmer_phone" class="mt-4">
                                <button
                                    @click="openWhatsApp(tooltip.data.farmer_phone)"
                                    class="w-full px-3 py-2 bg-green-500 hover:bg-green-600 text-white text-xs font-medium rounded-lg transition-colors flex items-center justify-center gap-1.5"
                                >
                                    <MessageCircle class="w-4 h-4" /> Hubungi via WhatsApp
                                </button>
                            </div>
                        </div>
                    </div>
                </Teleport>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
/* FullCalendar custom styling */
.custom-calendar .fc {
    --fc-border-color: rgba(156, 163, 175, 0.2);
    --fc-today-bg-color: rgba(16, 185, 129, 0.05);
    --fc-page-bg-color: transparent;
    --fc-neutral-bg-color: rgba(156, 163, 175, 0.05);
    --fc-list-event-hover-bg-color: rgba(156, 163, 175, 0.05);
}

.dark .custom-calendar .fc {
    --fc-border-color: rgba(255, 255, 255, 0.08);
    --fc-today-bg-color: rgba(16, 185, 129, 0.1);
    --fc-neutral-bg-color: rgba(255, 255, 255, 0.02);
    --fc-list-event-hover-bg-color: rgba(255, 255, 255, 0.05);
}

.custom-calendar .fc .fc-toolbar-title { color: #111827; font-size: 1.25rem; font-weight: 700; }
.dark .custom-calendar .fc .fc-toolbar-title { color: #ffffff; }

.custom-calendar .fc .fc-col-header-cell-cushion { color: #6b7280; font-size: 0.75rem; text-transform: uppercase; font-weight: 600; padding: 12px 0; }
.dark .custom-calendar .fc .fc-col-header-cell-cushion { color: #9ca3af; }

.custom-calendar .fc .fc-daygrid-day-number { color: #4b5563; font-size: 0.85rem; padding: 8px; }
.dark .custom-calendar .fc .fc-daygrid-day-number { color: #9ca3af; }

.custom-calendar .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { color: #059669; font-weight: bold; background: #ecfdf5; border-radius: 50%; width: 28px; height: 28px; display: flex; align-items: center; justify-content: center; margin: 4px; padding: 0; }
.dark .custom-calendar .fc .fc-daygrid-day.fc-day-today .fc-daygrid-day-number { color: #34d399; background: rgba(16, 185, 129, 0.2); }

.custom-calendar .fc .fc-event { border-radius: 6px; padding: 3px 6px; font-size: 0.75rem; cursor: pointer; font-weight: 500; border: none !important; margin: 1px 4px; }

/* Modern Button Groups (Segmented Controls) */
.custom-calendar .fc .fc-button-group {
    background: #f3f4f6;
    border-radius: 10px;
    padding: 4px;
    gap: 4px;
    border: 1px solid #e5e7eb;
}
.dark .custom-calendar .fc .fc-button-group {
    background: #1a241f;
    border-color: rgba(255,255,255,0.05);
}

.custom-calendar .fc .fc-button-group > .fc-button {
    border-radius: 8px !important;
    background: transparent !important;
    border: none !important;
    color: #4b5563 !important;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    text-transform: capitalize;
    box-shadow: none !important;
    transition: all 0.2s;
}
.dark .custom-calendar .fc .fc-button-group > .fc-button {
    color: #9ca3af !important;
}

.custom-calendar .fc .fc-button-group > .fc-button.fc-button-active, 
.custom-calendar .fc .fc-button-group > .fc-button:active {
    background: #10b981 !important;
    color: white !important;
    box-shadow: 0 2px 4px rgba(16, 185, 129, 0.2) !important;
}

.custom-calendar .fc .fc-button-group > .fc-button:hover:not(.fc-button-active) {
    background: #e5e7eb !important;
    color: #111827 !important;
}
.dark .custom-calendar .fc .fc-button-group > .fc-button:hover:not(.fc-button-active) {
    background: rgba(255,255,255,0.05) !important;
    color: white !important;
}

/* Individual Buttons (e.g., Today) */
.custom-calendar .fc .fc-button:not(.fc-button-group .fc-button) {
    background: white !important;
    border: 1px solid #e5e7eb !important;
    color: #374151 !important;
    border-radius: 10px !important;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 0.5rem 1rem;
    text-transform: capitalize;
    box-shadow: 0 1px 2px rgba(0,0,0,0.05) !important;
    transition: all 0.2s;
}
.dark .custom-calendar .fc .fc-button:not(.fc-button-group .fc-button) {
    background: #1a241f !important;
    border-color: rgba(255,255,255,0.05) !important;
    color: #f3f4f6 !important;
}
.custom-calendar .fc .fc-button:not(.fc-button-group .fc-button):hover {
    background: #f9fafb !important;
    color: #111827 !important;
}
.dark .custom-calendar .fc .fc-button:not(.fc-button-group .fc-button):hover {
    background: rgba(255,255,255,0.08) !important;
    color: white !important;
}

/* Icons via CSS Mask */
.custom-calendar .fc-dayGridMonth-button::before {
    content: ''; display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: -3px; background-color: currentColor;
    mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
    -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='18' rx='2' ry='2'/%3E%3Cline x1='16' y1='2' x2='16' y2='6'/%3E%3Cline x1='8' y1='2' x2='8' y2='6'/%3E%3Cline x1='3' y1='10' x2='21' y2='10'/%3E%3C/svg%3E");
    mask-size: contain; -webkit-mask-size: contain;
}

.custom-calendar .fc-listWeek-button::before {
    content: ''; display: inline-block; width: 16px; height: 16px; margin-right: 6px; vertical-align: -3px; background-color: currentColor;
    mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cline x1='8' y1='6' x2='21' y2='6'/%3E%3Cline x1='8' y1='12' x2='21' y2='12'/%3E%3Cline x1='8' y1='18' x2='21' y2='18'/%3E%3Cline x1='3' y1='6' x2='3.01' y2='6'/%3E%3Cline x1='3' y1='12' x2='3.01' y2='12'/%3E%3Cline x1='3' y1='18' x2='3.01' y2='18'/%3E%3C/svg%3E");
    -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cline x1='8' y1='6' x2='21' y2='6'/%3E%3Cline x1='8' y1='12' x2='21' y2='12'/%3E%3Cline x1='8' y1='18' x2='21' y2='18'/%3E%3Cline x1='3' y1='6' x2='3.01' y2='6'/%3E%3Cline x1='3' y1='12' x2='3.01' y2='12'/%3E%3Cline x1='3' y1='18' x2='3.01' y2='18'/%3E%3C/svg%3E");
    mask-size: contain; -webkit-mask-size: contain;
}

.custom-calendar .fc .fc-list-event-title a { color: #111827 !important; font-weight: 500; }
.dark .custom-calendar .fc .fc-list-event-title a { color: #f3f4f6 !important; }

.custom-calendar .fc .fc-list-day-cushion { background: #f9fafb !important; padding: 12px 16px; }
.dark .custom-calendar .fc .fc-list-day-cushion { background: rgba(255,255,255,0.02) !important; }

.custom-calendar .fc .fc-list-day-text, .custom-calendar .fc .fc-list-day-side-text { color: #4b5563 !important; font-weight: 600; }
.dark .custom-calendar .fc .fc-list-day-text, .dark .custom-calendar .fc .fc-list-day-side-text { color: #9ca3af !important; }

.custom-calendar .fc-theme-standard td, .custom-calendar .fc-theme-standard th { border-color: var(--fc-border-color); }
</style>

