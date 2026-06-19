<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    exam:     Object,
    rankings: Array,
});

const statusConfig = {
    draft:     { label: 'Draft',     cls: 'bg-slate-100 text-slate-600',   dot: 'bg-slate-400'   },
    scheduled: { label: 'Scheduled', cls: 'bg-blue-100 text-blue-700',     dot: 'bg-blue-500'    },
    active:    { label: 'Live',      cls: 'bg-emerald-100 text-emerald-700',dot: 'bg-emerald-500' },
    completed: { label: 'Completed', cls: 'bg-violet-100 text-violet-700', dot: 'bg-violet-500'  },
    cancelled: { label: 'Cancelled', cls: 'bg-red-100 text-red-700',       dot: 'bg-red-400'     },
};

function badge(s) { return statusConfig[s] ?? statusConfig.draft; }

const gradeColors = {
    'A+': 'bg-emerald-100 text-emerald-700',
    'A':  'bg-emerald-100 text-emerald-700',
    'B+': 'bg-blue-100 text-blue-700',
    'B':  'bg-blue-100 text-blue-700',
    'C+': 'bg-amber-100 text-amber-700',
    'C':  'bg-amber-100 text-amber-700',
    'D':  'bg-orange-100 text-orange-700',
    'F':  'bg-red-100 text-red-700',
};

function gradeClass(g) { return gradeColors[g] ?? 'bg-slate-100 text-slate-600'; }

const publishLoading = ref(false);

function publishResults() {
    publishLoading.value = true;
    router.post(route('admin.exams.publish-results', props.exam.id), {}, {
        onFinish: () => { publishLoading.value = false; },
    });
}

const passCount = computed(() => (props.rankings ?? []).filter(r => r.percentage >= 60).length);
const failCount = computed(() => (props.rankings ?? []).filter(r => r.percentage < 60).length);
const avgPct    = computed(() => {
    const rs = props.rankings ?? [];
    if (!rs.length) return 0;
    return (rs.reduce((s, r) => s + Number(r.percentage), 0) / rs.length).toFixed(1);
});
</script>

<template>
    <Head :title="`${exam.title} — Exam Details`" />
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link href="/admin/exams" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">{{ exam.title }}</h1>
                    <p class="text-sm text-slate-500 mt-0.5">{{ exam.course?.code }} · {{ exam.course?.title }}</p>
                </div>
            </div>
        </template>

        <div class="space-y-6">
            <!-- Exam info cards row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-400 mb-1">Status</p>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold ring-1"
                        :class="badge(exam.status).cls + ' ring-current/20'">
                        <span class="w-1.5 h-1.5 rounded-full" :class="badge(exam.status).dot"></span>
                        {{ badge(exam.status).label }}
                    </span>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-400 mb-1">Total Marks</p>
                    <p class="text-2xl font-bold text-slate-900">{{ exam.total_marks }}</p>
                    <p class="text-[11px] text-slate-400">Pass: {{ exam.passing_marks }}</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-400 mb-1">Duration</p>
                    <p class="text-2xl font-bold text-slate-900">{{ exam.duration_minutes }}</p>
                    <p class="text-[11px] text-slate-400">minutes</p>
                </div>
                <div class="bg-white rounded-2xl border border-slate-200 p-4">
                    <p class="text-xs text-slate-400 mb-1">Questions</p>
                    <p class="text-2xl font-bold text-slate-900">{{ exam.questions?.length ?? 0 }}</p>
                    <p class="text-[11px] text-slate-400">in this exam</p>
                </div>
            </div>

            <!-- Schedule + description -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                    <h2 class="text-sm font-bold text-slate-900">Exam Details</h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="text-xs text-slate-400">Course</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ exam.course?.title }} ({{ exam.course?.code }})</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Department</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ exam.course?.department?.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Created by</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ exam.creator?.name }}</dd>
                        </div>
                        <div v-if="exam.start_time">
                            <dt class="text-xs text-slate-400">Start Time</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ new Date(exam.start_time).toLocaleString('en-GB') }}</dd>
                        </div>
                        <div v-if="exam.end_time">
                            <dt class="text-xs text-slate-400">End Time</dt>
                            <dd class="font-medium text-slate-800 mt-0.5">{{ new Date(exam.end_time).toLocaleString('en-GB') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs text-slate-400">Settings</dt>
                            <dd class="mt-1 flex flex-wrap gap-1.5">
                                <span v-if="exam.shuffle_questions" class="text-[11px] bg-primary-50 text-primary-600 px-2 py-0.5 rounded-full">Shuffle Q</span>
                                <span v-if="exam.shuffle_options" class="text-[11px] bg-primary-50 text-primary-600 px-2 py-0.5 rounded-full">Shuffle Opts</span>
                                <span v-if="exam.allow_backtrack" class="text-[11px] bg-emerald-50 text-emerald-600 px-2 py-0.5 rounded-full">Backtrack ✓</span>
                                <span v-if="exam.show_result_immediately" class="text-[11px] bg-amber-50 text-amber-600 px-2 py-0.5 rounded-full">Instant Results</span>
                            </dd>
                        </div>
                    </dl>

                    <!-- Actions -->
                    <div class="pt-2 border-t border-slate-100 space-y-2">
                        <button
                            v-if="exam.status === 'completed'"
                            @click="publishResults"
                            :disabled="publishLoading"
                            class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-emerald-400 text-white text-sm font-semibold rounded-xl transition-colors"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            {{ publishLoading ? 'Publishing…' : 'Publish Results' }}
                        </button>
                    </div>
                </div>

                <!-- Result summary + rankings -->
                <div class="lg:col-span-2 space-y-4">
                    <!-- Summary stats (only if there are rankings) -->
                    <div v-if="rankings?.length" class="grid grid-cols-3 gap-3">
                        <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
                            <p class="text-2xl font-bold text-slate-900">{{ rankings.length }}</p>
                            <p class="text-xs text-slate-400 mt-0.5">Attempts</p>
                        </div>
                        <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-4 text-center">
                            <p class="text-2xl font-bold text-emerald-700">{{ passCount }}</p>
                            <p class="text-xs text-emerald-500 mt-0.5">Passed</p>
                        </div>
                        <div class="bg-red-50 rounded-2xl border border-red-200 p-4 text-center">
                            <p class="text-2xl font-bold text-red-600">{{ failCount }}</p>
                            <p class="text-xs text-red-400 mt-0.5">Failed</p>
                        </div>
                    </div>

                    <!-- Rankings table -->
                    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                </div>
                                <h2 class="text-sm font-bold text-slate-900">Class Rankings</h2>
                            </div>
                            <span v-if="rankings?.length" class="text-xs text-slate-400">Avg: {{ avgPct }}%</span>
                        </div>

                        <div v-if="!rankings?.length" class="py-12 text-center">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-500">No published results yet</p>
                            <p class="text-xs text-slate-400 mt-1">Results will appear here once published.</p>
                        </div>

                        <div v-else class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 bg-slate-50">
                                        <th class="px-4 py-3 text-center font-semibold text-slate-500 w-12">Rank</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-500">Student</th>
                                        <th class="px-4 py-3 text-left font-semibold text-slate-500">Department</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-500">Marks</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-500">%</th>
                                        <th class="px-4 py-3 text-center font-semibold text-slate-500">Grade</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <tr v-for="r in rankings" :key="r.student_code"
                                        :class="['hover:bg-slate-50 transition-colors', r.class_rank <= 3 ? 'bg-amber-50/40' : '']">
                                        <td class="px-4 py-3 text-center">
                                            <span v-if="r.class_rank === 1" class="text-lg">🥇</span>
                                            <span v-else-if="r.class_rank === 2" class="text-lg">🥈</span>
                                            <span v-else-if="r.class_rank === 3" class="text-lg">🥉</span>
                                            <span v-else class="font-semibold text-slate-500 text-sm">#{{ r.class_rank }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-semibold text-slate-900">{{ r.student_name }}</p>
                                            <p class="text-xs text-slate-400 font-mono">{{ r.student_code }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-slate-600 text-xs">{{ r.department }}</td>
                                        <td class="px-4 py-3 text-center font-semibold text-slate-800">
                                            {{ r.obtained_marks }}<span class="text-slate-400 font-normal">/{{ r.total_marks }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <div class="flex items-center justify-center gap-1.5">
                                                <div class="w-16 bg-slate-200 rounded-full h-1.5">
                                                    <div class="h-1.5 rounded-full transition-all"
                                                        :class="r.percentage >= 60 ? 'bg-emerald-500' : 'bg-red-400'"
                                                        :style="`width:${r.percentage}%`"></div>
                                                </div>
                                                <span class="text-xs font-semibold text-slate-700">{{ Number(r.percentage).toFixed(1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="inline-flex items-center justify-center w-10 h-7 rounded-lg text-xs font-bold"
                                                :class="gradeClass(r.grade)">
                                                {{ r.grade }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
