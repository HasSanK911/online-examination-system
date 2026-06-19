<script setup>
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    result: { type: Object, required: true },
})

const gradeColors = {
    'A+': 'text-emerald-600', 'A': 'text-emerald-600',
    'B+': 'text-sky-600',     'B': 'text-sky-600',
    'C+': 'text-amber-600',   'C': 'text-amber-600',
    'D':  'text-orange-600',  'F': 'text-red-600',
}

const typeLabel = {
    mcq_single:   'MCQ', mcq_multiple: 'Multi',
    true_false:   'T/F', fill_blank:   'Fill',
    short:        'Short', descriptive: 'Essay',
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link href="/student/results"
                    class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 font-heading">Result Details</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ result.exam?.title }}</p>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Result Card -->
            <div class="lg:col-span-2 space-y-5">

                <!-- Score Summary -->
                <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Your Score</p>
                    <div :class="['text-7xl font-black mb-2', gradeColors[result.grade] ?? 'text-slate-900']">
                        {{ result.grade }}
                    </div>
                    <p class="text-2xl font-bold text-slate-900">{{ result.obtained_marks }} / {{ result.total_marks }}</p>
                    <p class="text-slate-400 mt-1">{{ Number(result.percentage).toFixed(1) }}%</p>

                    <div class="mt-4 flex justify-center gap-4">
                        <div class="px-4 py-2 rounded-xl bg-slate-50 border border-slate-200">
                            <p class="text-lg font-bold text-slate-900">{{ result.gpa }}</p>
                            <p class="text-xs text-slate-400">GPA</p>
                        </div>
                        <div :class="['px-4 py-2 rounded-xl', result.is_pass ? 'bg-emerald-50 border border-emerald-200' : 'bg-red-50 border border-red-200']">
                            <p :class="['text-lg font-bold', result.is_pass ? 'text-emerald-700' : 'text-red-600']">
                                {{ result.is_pass ? 'Pass' : 'Fail' }}
                            </p>
                            <p class="text-xs text-slate-400">Status</p>
                        </div>
                        <div v-if="result.class_rank" class="px-4 py-2 rounded-xl bg-amber-50 border border-amber-200">
                            <p class="text-lg font-bold text-amber-700">#{{ result.class_rank }}</p>
                            <p class="text-xs text-slate-400">Class Rank</p>
                        </div>
                    </div>

                    <a :href="`/student/results/${result.id}/download`"
                        class="mt-5 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white"
                        style="background:#BC2739;">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Download Result Card
                    </a>
                </div>

                <!-- Question Breakdown -->
                <div v-if="result.details?.length" class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100">
                        <h3 class="font-bold text-slate-900">Question Breakdown</h3>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 border-b border-slate-100">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">#</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Question</th>
                                <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Type</th>
                                <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase">Marks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr v-for="(detail, i) in result.details" :key="detail.id"
                                :class="['hover:bg-slate-50', detail.is_correct === false ? 'bg-red-50/30' : detail.is_correct === true ? 'bg-emerald-50/30' : '']">
                                <td class="px-5 py-3 text-slate-400 text-xs">{{ i + 1 }}</td>
                                <td class="px-5 py-3 text-slate-700 max-w-xs">
                                    {{ detail.question?.question_text?.replace(/<[^>]+>/g,'')?.slice(0,70) ?? '—' }}
                                </td>
                                <td class="px-5 py-3 text-center">
                                    <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-500">
                                        {{ typeLabel[detail.question?.type] ?? detail.question?.type }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right">
                                    <span :class="['font-semibold', Number(detail.obtained_marks) > 0 ? 'text-emerald-600' : 'text-slate-400']">
                                        {{ detail.obtained_marks }}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-4">
                <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
                    <h3 class="font-bold text-slate-900 text-sm">Exam Info</h3>
                    <div class="space-y-3 text-sm">
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Exam</p>
                            <p class="font-medium text-slate-900">{{ result.exam?.title }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Course</p>
                            <p class="font-medium text-slate-900">{{ result.exam?.course?.title }}</p>
                            <p class="text-xs text-slate-400">{{ result.exam?.course?.department?.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400 mb-0.5">Published</p>
                            <p class="font-medium text-slate-900">
                                {{ result.published_at ? new Date(result.published_at).toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' }) : '—' }}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-slate-200 p-5">
                    <h3 class="font-bold text-slate-900 text-sm mb-3">Student</h3>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm"
                            style="background:linear-gradient(135deg,#BC2739,#e05a6b);">
                            {{ result.student?.user?.name?.charAt(0) }}
                        </div>
                        <div>
                            <p class="font-medium text-slate-900">{{ result.student?.user?.name }}</p>
                            <p class="text-xs text-slate-400 font-mono">{{ result.student?.student_id }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
