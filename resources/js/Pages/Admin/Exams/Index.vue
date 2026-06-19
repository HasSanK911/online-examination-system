<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    exams:   Object,
    courses: Array,
    filters: Object,
});

const search       = ref(props.filters?.search    ?? '');
const statusFilter = ref(props.filters?.status    ?? '');
const courseFilter = ref(props.filters?.course_id ?? '');

function applyFilters() {
    router.get(route('admin.exams.index'), {
        search:    search.value       || undefined,
        status:    statusFilter.value || undefined,
        course_id: courseFilter.value || undefined,
    }, { preserveState: true, replace: true });
}

const statusConfig = {
    draft:     { label: 'Draft',     cls: 'bg-slate-100  text-slate-600  ring-slate-200',   dot: 'bg-slate-400'   },
    scheduled: { label: 'Scheduled', cls: 'bg-blue-100   text-blue-700   ring-blue-200',    dot: 'bg-blue-500'    },
    active:    { label: 'Live',      cls: 'bg-emerald-100 text-emerald-700 ring-emerald-200', dot: 'bg-emerald-500' },
    completed: { label: 'Completed', cls: 'bg-violet-100 text-violet-700 ring-violet-200',  dot: 'bg-violet-500'  },
    cancelled: { label: 'Cancelled', cls: 'bg-red-100    text-red-700    ring-red-200',     dot: 'bg-red-400'     },
};

function statusBadge(s) { return statusConfig[s] ?? statusConfig.draft; }

const statuses = ['', 'draft', 'scheduled', 'active', 'completed', 'cancelled'];

const counts = computed(() => {
    const c = { '': props.exams.total };
    for (const s of Object.keys(statusConfig)) c[s] = 0;
    return c;
});

const activeFiltersCount = computed(() =>
    [search.value, statusFilter.value, courseFilter.value].filter(Boolean).length
);

function clearFilters() {
    search.value = statusFilter.value = courseFilter.value = '';
    applyFilters();
}
</script>

<template>
    <Head title="Exams — Admin" />
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900">Exams</h1>
                    <p class="text-sm text-slate-500 mt-0.5">Manage all examinations across courses</p>
                </div>
            </div>
        </template>

        <!-- Status tab filter -->
        <div class="flex items-center gap-1 bg-white border border-slate-200 rounded-xl p-1 mb-4 w-fit overflow-x-auto">
            <button
                v-for="s in statuses"
                :key="s"
                @click="statusFilter = s; applyFilters()"
                :class="[
                    'px-4 py-1.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap',
                    statusFilter === s
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'text-slate-600 hover:bg-slate-100'
                ]"
            >
                {{ s === '' ? 'All' : statusConfig[s]?.label ?? s }}
            </button>
        </div>

        <!-- Search + course filter -->
        <div class="bg-white rounded-2xl border border-slate-200 mb-4">
            <div class="p-4 flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px]">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input v-model="search" @keyup.enter="applyFilters" type="text"
                        placeholder="Search by exam title…"
                        class="w-full pl-9 pr-4 h-10 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" />
                </div>
                <select v-model="courseFilter" @change="applyFilters"
                    class="h-10 pl-3 pr-8 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">All Courses</option>
                    <option v-for="c in courses" :key="c.id" :value="c.id">{{ c.code }} — {{ c.title }}</option>
                </select>
                <button v-if="activeFiltersCount > 0" @click="clearFilters"
                    class="inline-flex items-center gap-1.5 h-10 px-3 text-sm text-slate-600 hover:text-red-600 border border-slate-200 rounded-xl hover:border-red-300 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear ({{ activeFiltersCount }})
                </button>
            </div>
        </div>

        <!-- Exam Cards grid -->
        <div v-if="exams.data.length === 0" class="bg-white rounded-2xl border border-slate-200 p-16 text-center">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="font-semibold text-slate-700">No exams found</p>
            <p class="text-sm text-slate-400 mt-1">Try adjusting your filters.</p>
        </div>

        <div v-else class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 bg-slate-50">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Exam</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Course</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Marks</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Duration</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600">Schedule</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Attempts</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600">Status</th>
                            <th class="px-4 py-3 text-right font-semibold text-slate-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="exam in exams.data" :key="exam.id" class="hover:bg-slate-50 transition-colors">
                            <!-- Exam title -->
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div :class="['w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0', statusBadge(exam.status).cls]">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-900">{{ exam.title }}</p>
                                        <p class="text-xs text-slate-400">by {{ exam.creator?.name }}</p>
                                    </div>
                                </div>
                            </td>
                            <!-- Course -->
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ exam.course?.title }}</p>
                                <p class="text-xs text-slate-400">{{ exam.course?.code }} · {{ exam.course?.department?.name }}</p>
                            </td>
                            <!-- Marks -->
                            <td class="px-4 py-3 text-center">
                                <span class="font-semibold text-slate-900">{{ exam.total_marks }}</span>
                                <span class="text-slate-400 text-xs"> / {{ exam.passing_marks }} pass</span>
                            </td>
                            <!-- Duration -->
                            <td class="px-4 py-3 text-center text-slate-600 font-medium">
                                {{ exam.duration_minutes }} min
                            </td>
                            <!-- Schedule -->
                            <td class="px-4 py-3">
                                <div v-if="exam.start_time">
                                    <p class="text-slate-800 text-xs font-medium">{{ new Date(exam.start_time).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' }) }}</p>
                                    <p class="text-slate-400 text-xs">{{ new Date(exam.start_time).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' }) }}</p>
                                </div>
                                <span v-else class="text-slate-400 text-xs">Not set</span>
                            </td>
                            <!-- Attempts count -->
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-[2rem] h-7 px-2 bg-slate-100 text-slate-700 rounded-lg font-semibold text-sm">
                                    {{ exam.attempts_count ?? 0 }}
                                </span>
                            </td>
                            <!-- Status badge -->
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1"
                                    :class="statusBadge(exam.status).cls">
                                    <span class="w-1.5 h-1.5 rounded-full" :class="statusBadge(exam.status).dot"></span>
                                    {{ statusBadge(exam.status).label }}
                                </span>
                            </td>
                            <!-- Actions -->
                            <td class="px-4 py-3 text-right">
                                <Link :href="route('admin.exams.show', exam.id)"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 rounded-lg transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    View
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="exams.last_page > 1" class="px-4 py-3 border-t border-slate-200 flex items-center justify-between">
                <p class="text-sm text-slate-500">Showing {{ exams.from }}–{{ exams.to }} of {{ exams.total }} exams</p>
                <div class="flex gap-1">
                    <Link
                        v-for="link in exams.links"
                        :key="link.label"
                        :href="link.url ?? '#'"
                        v-html="link.label"
                        :class="[
                            'px-3 py-1.5 text-sm rounded-lg transition-colors',
                            link.active ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100',
                            !link.url   ? 'opacity-40 pointer-events-none' : '',
                        ]"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
