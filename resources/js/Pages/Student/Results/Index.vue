<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
    results: { type: Array, default: () => [] },
})

const gradeColors = {
    'A+': 'bg-emerald-100 text-emerald-700',
    'A':  'bg-emerald-100 text-emerald-700',
    'B+': 'bg-sky-100 text-sky-700',
    'B':  'bg-sky-100 text-sky-700',
    'C+': 'bg-amber-100 text-amber-700',
    'C':  'bg-amber-100 text-amber-700',
    'D':  'bg-orange-100 text-orange-700',
    'F':  'bg-red-100 text-red-700',
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900 font-heading">My Results</h1>
                <p class="text-sm text-slate-400 mt-0.5">{{ results.length }} published result{{ results.length !== 1 ? 's' : '' }}</p>
            </div>
        </template>

        <div v-if="!results.length" class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
            <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <p class="text-slate-500 font-medium">No results yet</p>
            <p class="text-sm text-slate-400 mt-1">Results will appear here after they are published.</p>
        </div>

        <div v-else class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Exam</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Course</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Score</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Grade</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Rank</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Published</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-for="result in results" :key="result.id" class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5 font-medium text-slate-900 max-w-xs truncate">{{ result.exam_title }}</td>
                        <td class="px-5 py-3.5 text-slate-500">
                            <span class="font-mono text-xs">{{ result.course_code }}</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <div>
                                <span class="font-bold text-slate-900">{{ result.obtained_marks }}</span>
                                <span class="text-slate-400">/{{ result.total_marks }}</span>
                            </div>
                            <div class="text-xs text-slate-400">{{ Number(result.percentage).toFixed(1) }}%</div>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span :class="['px-2 py-0.5 rounded text-xs font-bold', gradeColors[result.grade] ?? 'bg-slate-100 text-slate-600']">
                                {{ result.grade }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-slate-500">
                            {{ result.class_rank ? '#' + result.class_rank : '—' }}
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span :class="['px-2.5 py-0.5 rounded-full text-xs font-semibold', result.is_pass ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-700']">
                                {{ result.is_pass ? 'Pass' : 'Fail' }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right text-slate-400 text-xs">{{ result.published_at }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <Link :href="`/student/results/${result.id}`"
                                class="text-xs font-medium text-primary-600 hover:text-primary-700">
                                View →
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
