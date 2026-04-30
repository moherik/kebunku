<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import {
    Wallet, TrendingUp, TrendingDown, Plus, Trash2, X, Save, Filter,
    ArrowUpCircle, ArrowDownCircle, BarChart3, Sprout
} from 'lucide-vue-next';
import DatePicker from '@/Components/DatePicker.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    transactions: Object,
    totalExpenses: Number,
    totalIncome: Number,
    profitLoss: Number,
    monthlySummary: Array,
    plantingSummary: Array,
    plantings: Array,
    expenseCategories: Object,
    incomeCategories: Object,
    categoryIcons: Object,
    filters: Object,
});

const showModal = ref(false);
const form = useForm({
    planting_id: '',
    type: 'expense',
    category: 'bibit',
    amount: '',
    transaction_date: new Date().toISOString().split('T')[0],
    note: '',
});

const currentCategories = computed(() => {
    return form.type === 'expense' ? props.expenseCategories : props.incomeCategories;
});

function onTypeChange() {
    const cats = form.type === 'expense' ? props.expenseCategories : props.incomeCategories;
    form.category = Object.keys(cats)[0];
}

function submit() {
    form.post(route('farmer.finances.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
            form.transaction_date = new Date().toISOString().split('T')[0];
        },
    });
}

function deleteTransaction(id) {
    if (confirm('Hapus transaksi ini?')) {
        router.delete(route('farmer.finances.destroy', id));
    }
}

function formatRupiah(amount) {
    return 'Rp ' + Number(amount).toLocaleString('id-ID');
}

function applyFilter(key, value) {
    router.get(route('farmer.finances.index'), {
        ...props.filters,
        [key]: value,
    }, { preserveState: true, preserveScroll: true });
}

const maxMonthlyValue = computed(() => {
    let max = 0;
    (props.monthlySummary || []).forEach(m => {
        max = Math.max(max, m.expenses, m.income);
    });
    return max || 1;
});

function barHeight(val) {
    return Math.max(4, (val / maxMonthlyValue.value) * 120);
}
</script>

<template>
    <Head title="Keuangan" />
    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <Wallet class="w-6 h-6 text-emerald-600" />
                    Keuangan
                </h2>
                <button @click="showModal = true" class="btn-primary text-sm flex items-center gap-2">
                    <Plus class="w-4 h-4" /> Catat Transaksi
                </button>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-screen-2xl px-4 sm:px-6 lg:px-8 space-y-6">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 animate-fade-in">
                    <div class="glass-card p-5 border-l-4 border-l-red-400">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-red-50 dark:bg-red-900/20 flex items-center justify-center">
                                <ArrowDownCircle class="w-5 h-5 text-red-500 dark:text-red-400" />
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pengeluaran</div>
                                <div class="text-xl font-bold text-red-600 dark:text-red-400">{{ formatRupiah(totalExpenses) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="glass-card p-5 border-l-4 border-l-emerald-400">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-50 dark:bg-emerald-900/20 flex items-center justify-center">
                                <ArrowUpCircle class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Pendapatan</div>
                                <div class="text-xl font-bold text-emerald-600 dark:text-emerald-400">{{ formatRupiah(totalIncome) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="glass-card p-5 border-l-4" :class="profitLoss >= 0 ? 'border-l-emerald-400' : 'border-l-red-400'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center" :class="profitLoss >= 0 ? 'bg-emerald-50 dark:bg-emerald-900/20' : 'bg-red-50 dark:bg-red-900/20'">
                                <TrendingUp v-if="profitLoss >= 0" class="w-5 h-5 text-emerald-500 dark:text-emerald-400" />
                                <TrendingDown v-else class="w-5 h-5 text-red-500 dark:text-red-400" />
                            </div>
                            <div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Profit / Loss</div>
                                <div class="text-xl font-bold" :class="profitLoss >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400'">
                                    {{ formatRupiah(Math.abs(profitLoss)) }}
                                    <span class="text-xs font-medium ml-1">{{ profitLoss >= 0 ? 'Untung' : 'Rugi' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Monthly Chart -->
                    <div class="lg:col-span-2 glass-card p-6 animate-slide-up">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-6">
                            <BarChart3 class="w-5 h-5 text-emerald-500" /> Ringkasan Bulanan
                        </h3>
                        <div class="flex items-end justify-between gap-2 h-40">
                            <div v-for="month in monthlySummary" :key="month.label" class="flex-1 flex flex-col items-center gap-1">
                                <div class="flex gap-1 items-end w-full justify-center">
                                    <div class="w-3 sm:w-4 bg-red-400 rounded-t-sm opacity-80"
                                        :style="{ height: barHeight(month.expenses) + 'px' }" :title="`Pengeluaran: ${formatRupiah(month.expenses)}`"></div>
                                    <div class="w-3 sm:w-4 bg-emerald-400 rounded-t-sm opacity-80"
                                        :style="{ height: barHeight(month.income) + 'px' }" :title="`Pendapatan: ${formatRupiah(month.income)}`"></div>
                                </div>
                                <span class="text-[10px] sm:text-xs text-gray-500 dark:text-gray-400 text-center leading-tight mt-1">
                                    {{ month.label.split(' ')[0] }}
                                </span>
                            </div>
                        </div>
                        <div class="flex items-center justify-center gap-6 mt-4 pt-4 border-t border-gray-100 dark:border-white/5">
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <div class="w-3 h-3 bg-red-400 rounded-sm"></div> Pengeluaran
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                <div class="w-3 h-3 bg-emerald-400 rounded-sm"></div> Pendapatan
                            </div>
                        </div>
                    </div>

                    <!-- Per-Planting Summary -->
                    <div class="glass-card p-6 animate-slide-up">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white flex items-center gap-2 mb-4">
                            <Sprout class="w-5 h-5 text-emerald-500" /> Per Penanaman
                        </h3>
                        <div v-if="plantingSummary?.length" class="space-y-3">
                            <div v-for="ps in plantingSummary" :key="ps.id" class="p-3 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-100 dark:border-white/5">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-lg">{{ ps.crop_icon }}</span>
                                    <span class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ ps.crop_name }}</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 text-xs">
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Keluar:</span>
                                        <span class="font-bold text-red-500 ml-1">{{ formatRupiah(ps.total_expenses) }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 dark:text-gray-400">Masuk:</span>
                                        <span class="font-bold text-emerald-500 ml-1">{{ formatRupiah(ps.total_income) }}</span>
                                    </div>
                                </div>
                                <div class="mt-1 text-xs font-bold" :class="ps.profit_loss >= 0 ? 'text-emerald-600' : 'text-red-600'">
                                    {{ ps.profit_loss >= 0 ? '↑' : '↓' }} {{ formatRupiah(Math.abs(ps.profit_loss)) }}
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-sm text-gray-400 dark:text-gray-500 text-center py-6">Belum ada penanaman aktif</p>
                    </div>
                </div>

                <!-- Filters -->
                <div class="glass-card p-4 flex flex-wrap items-center gap-3 animate-slide-up">
                    <Filter class="w-4 h-4 text-gray-400 dark:text-gray-500" />
                    <select @change="applyFilter('period', $event.target.value)" :value="filters.period" class="text-sm rounded-xl border-gray-300 bg-white dark:bg-[#131b17] text-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        <option value="all">Semua Waktu</option>
                        <option value="month">Bulan Ini</option>
                        <option value="year">Tahun Ini</option>
                    </select>
                    <select @change="applyFilter('type', $event.target.value)" :value="filters.type || ''" class="text-sm rounded-xl border-gray-300 bg-white dark:bg-[#131b17] text-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 py-2">
                        <option value="">Semua Tipe</option>
                        <option value="expense">Pengeluaran</option>
                        <option value="income">Pendapatan</option>
                    </select>
                    <select @change="applyFilter('planting_id', $event.target.value)" :value="filters.planting_id || ''" class="text-sm rounded-xl border-gray-300 bg-white dark:bg-[#131b17] text-gray-900 dark:text-white focus:border-emerald-500 focus:ring-emerald-500 py-2 max-w-xs truncate">
                        <option value="">Semua Penanaman</option>
                        <option v-for="p in plantings" :key="p.id" :value="p.id">{{ p.label }}</option>
                    </select>
                </div>

                <!-- Transaction List -->
                <div class="glass-card divide-y divide-gray-100 dark:divide-white/5 animate-slide-up">
                    <div v-if="transactions.data?.length" v-for="t in transactions.data" :key="t.id" class="flex items-center justify-between p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg flex-shrink-0" :class="t.type === 'expense' ? 'bg-red-50 dark:bg-red-900/20' : 'bg-emerald-50 dark:bg-emerald-900/20'">
                                {{ t.category_icon }}
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ t.category_label }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 truncate">
                                    {{ new Date(t.transaction_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }) }}
                                    <span v-if="t.planting?.crop?.name"> · {{ t.planting.crop.name }}</span>
                                    <span v-if="t.note"> · {{ t.note }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 flex-shrink-0">
                            <span class="text-sm font-bold" :class="t.type === 'expense' ? 'text-red-600 dark:text-red-400' : 'text-emerald-600 dark:text-emerald-400'">
                                {{ t.type === 'expense' ? '-' : '+' }}{{ t.formatted_amount }}
                            </span>
                            <button @click="deleteTransaction(t.id)" class="opacity-0 group-hover:opacity-100 p-1.5 rounded-lg text-gray-400 dark:text-gray-500 hover:text-red-500 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all">
                                <Trash2 class="w-4 h-4" />
                            </button>
                        </div>
                    </div>
                    <div v-if="!transactions.data?.length" class="text-center py-12">
                        <Wallet class="w-12 h-12 text-gray-300 mx-auto mb-3" />
                        <p class="text-gray-500 dark:text-gray-400 text-sm">Belum ada transaksi</p>
                    </div>
                </div>

                <!-- Pagination -->
                <div v-if="transactions.links?.length > 3" class="flex justify-center gap-1">
                    <template v-for="link in transactions.links" :key="link.label">
                        <button v-if="link.url" @click="router.get(link.url, {}, { preserveScroll: true })" class="px-3 py-1.5 text-sm rounded-lg transition-colors" :class="link.active ? 'bg-emerald-600 text-white font-bold' : 'text-gray-600 hover:bg-gray-100 dark:hover:bg-white/10 dark:bg-white/10 dark:hover:bg-white/10 dark:bg-white/10'" v-html="link.label"></button>
                    </template>
                </div>
            </div>
        </div>

        <Modal :show="showModal" @close="showModal = false" maxWidth="md">
            <div class="p-6">
                <div class="flex justify-between items-center mb-5">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                        <Wallet class="w-5 h-5 text-emerald-500" /> Catat Transaksi
                    </h3>
                    <button @click="showModal = false" class="text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-white p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <div class="flex rounded-xl overflow-hidden border border-gray-200 dark:border-white/10">
                        <button type="button" @click="form.type = 'expense'; onTypeChange()" class="flex-1 py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2" :class="form.type === 'expense' ? 'bg-red-500 text-white' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400'">
                            <ArrowDownCircle class="w-4 h-4" /> Pengeluaran
                        </button>
                        <button type="button" @click="form.type = 'income'; onTypeChange()" class="flex-1 py-2.5 text-sm font-bold transition-colors flex items-center justify-center gap-2" :class="form.type === 'income' ? 'bg-emerald-500 text-white' : 'bg-gray-50 dark:bg-white/5 text-gray-600 dark:text-gray-400'">
                            <ArrowUpCircle class="w-4 h-4" /> Pendapatan
                        </button>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Kategori</label>
                        <div class="grid grid-cols-2 gap-2">
                            <button v-for="(label, key) in currentCategories" :key="key" type="button" @click="form.category = key" class="flex items-center gap-2 px-3 py-2 rounded-xl border text-sm font-medium transition-all text-left" :class="form.category === key ? (form.type === 'expense' ? 'border-red-400 dark:border-red-500 bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300' : 'border-emerald-400 dark:border-emerald-500 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300') : 'border-gray-200 dark:border-white/10 text-gray-600 dark:text-gray-400 hover:border-gray-300 dark:hover:border-white/20'">
                                <span>{{ categoryIcons[key] || '📝' }}</span>
                                <span class="truncate">{{ label }}</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Jumlah (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-gray-500 dark:text-gray-400">Rp</span>
                            <input v-model="form.amount" type="number" min="0" step="1000" required placeholder="0" class="w-full pl-10 rounded-xl border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                        </div>
                        <p v-if="form.errors.amount" class="mt-1 text-sm text-red-500">{{ form.errors.amount }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Penanaman (opsional)</label>
                        <select v-model="form.planting_id" class="w-full rounded-xl border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                            <option value="" class="dark:bg-[#131b17] dark:text-white">Tidak terkait penanaman</option>
                            <option v-for="p in plantings" :key="p.id" :value="p.id" class="dark:bg-[#131b17] dark:text-white">{{ p.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Tanggal</label>
                        <DatePicker v-model="form.transaction_date" placeholder="Pilih tanggal" required />
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2">Catatan (opsional)</label>
                        <input v-model="form.note" type="text" placeholder="Keterangan singkat..." class="w-full rounded-xl border-gray-300 dark:border-white/10 bg-white dark:bg-white/5 text-gray-900 dark:text-white shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-100 dark:border-white/5">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-gray-600 dark:text-gray-300 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 transition-all">
                            Batal
                        </button>
                        <button type="submit" :disabled="form.processing" class="btn-primary text-sm flex items-center gap-2 !px-5 !py-2.5">
                            <Save class="w-4 h-4" /> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
