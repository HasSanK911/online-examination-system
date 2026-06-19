<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    exam:     { type: Object, required: true },
    attempts: { type: Array,  default: () => [] },
})

const search = ref('')

const filtered = computed(() => {
    const q = search.value.toLowerCase()
    if (!q) return props.attempts
    return props.attempts.filter(a =>
        a.student.name.toLowerCase().includes(q) ||
        a.student.student_id.toLowerCase().includes(q)
    )
})

const pendingCount = computed(() =>
    props.attempts.filter(a => a.pending_count > 0 || !a.result?.is_evaluated).length
)

function gradeColor(grade) {
    const map = { 'A+': 'text-emerald-700 bg-emerald-50', A: 'text-emerald-700 bg-emerald-50', 'B+': 'text-sky-700 bg-sky-50', B: 'text-sky-700 bg-sky-50', 'C+': 'text-amber-700 bg-amber-50', C: 'text-amber-700 bg-amber-50', D: 'text-orange-700 bg-orange-50', F: 'text-red-700 bg-red-50' }
    return map[grade] ?? 'text-slate-700 bg-slate-100'
}

function fmt(dt) {
    if (!dt) return '—'
    return new Date(dt).toLocaleString('en-US', { dateStyle: 'medium', timeStyle: 'short' })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link href="/teacher/exams" class="text-sm text-slate-400 hover:text-slate-600 mb-1 inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        Exams
                    </Link>
                    <h1 class="text-xl font-bold text-slate-900">Evaluate: {{ exam.title }}</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ exam.course?.title }} · {{ exam.course?.code }}</p>
                </div>
                <div v-if="pendingCount > 0"
                    class="px-3 py-1.5 rounded-full text-sm font-semibold bg-amber-100 text-amber-700">
                    {{ pendingCount }} pending evaluation{{ pendingCount > 1 ? 's' : '' }}
                </div>
            </div>
        </template>

        <!-- Search -->
        <div class="mb-5">
            <input v-model="search" type="text" placeholder="Search student name or ID…"
                class="h-9 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 w-72"/>
        </div>

        <!-- Attempts table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Student</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Submitted</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Score</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Grade</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Published</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!filtered.length">
                        <td colspan="7" class="px-5 py-12 text-center text-slate-400">No submissions found.</td>
                    </tr>
                    <tr v-for="attempt in filtered" :key="attempt.id" class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-900">{{ attempt.student.name }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ attempt.student.student_id }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500 text-xs">{{ fmt(attempt.submitted_at) }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <template v-if="attempt.result">
                                <span class="font-semibold text-slate-900">{{ attempt.result.obtained_marks }}</span>
                                <span class="text-slate-400 text-xs"> / {{ attempt.result.total_marks }}</span>
                            </template>
                            <span v-else class="text-slate-300">—</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span v-if="attempt.result?.grade"
                                :class="['px-2 py-0.5 rounded-full text-xs font-bold', gradeColor(attempt.result.grade)]">
                                {{ attempt.result.grade }}
                            </span>
                            <span v-else class="text-slate-300">—</span>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span v-if="!attempt.result" class="px-2 py-0.5 rounded-full text-xs bg-slate-100 text-slate-500">No result</span>
                            <span v-else-if="attempt.pending_count > 0 || !attempt.result.is_evaluated"
                                class="px-2 py-0.5 rounded-full text-xs bg-amber-100 text-amber-700 font-semibold">
                                Needs evaluation
                            </span>
                            <span v-else class="px-2 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-700 font-semibold">Evaluated</span>
                        </td>
                        <td class="px-5 py-3.5 text-center text-xs">
                            <span v-if="attempt.result?.published_at" class="text-emerald-600 font-medium">Published</span>
                            <span v-else class="text-slate-400">Pending</span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <Link v-if="attempt.pending_count > 0 || !attempt.result?.is_evaluated"
                                :href="`/teacher/exams/${exam.id}/evaluate/${attempt.id}`"
                                class="px-3 py-1.5 rounded-lg text-xs font-semibold text-white"
                                style="background:#BC2739;">
                                Evaluate
                            </Link>
                            <Link v-else
                                :href="`/teacher/exams/${exam.id}/evaluate/${attempt.id}`"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium text-slate-600 bg-slate-100 hover:bg-slate-200">
                                Review
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </AppLayout>
</template>
