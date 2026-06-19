<script setup>
import { ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    students:    { type: Array, default: () => [] },
    exams:       { type: Array, default: () => [] },
    departments: { type: Array, default: () => [] },
    courses:     { type: Array, default: () => [] },
})

const selected = reactive({
    student_id:    '',
    exam_id:       '',
    department_id: '',
    course_id:     '',
})

import { reactive } from 'vue'

function download(type) {
    const urls = {
        student:    selected.student_id    ? `/admin/reports/student/${selected.student_id}/pdf`    : null,
        exam:       selected.exam_id       ? `/admin/reports/exam/${selected.exam_id}/pdf`           : null,
        department: selected.department_id ? `/admin/reports/department/${selected.department_id}/pdf` : null,
        course:     selected.course_id     ? `/admin/reports/course/${selected.course_id}/pdf`       : null,
    }
    const url = urls[type]
    if (url) window.open(url, '_blank')
}

const cards = [
    {
        key: 'student',
        title: 'Student Report',
        desc: 'Full academic record: all exams, grades, GPA, and rankings for a single student.',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        color: 'bg-violet-50 text-violet-600',
        selector: 'student_id',
        label: 'Select Student',
        options: props.students,
        optionLabel: s => `${s.name} (${s.student_id})`,
    },
    {
        key: 'exam',
        title: 'Exam Report',
        desc: 'Complete result sheet for an exam: all students, marks, grades, and pass/fail summary.',
        icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        color: 'bg-sky-50 text-sky-600',
        selector: 'exam_id',
        label: 'Select Exam',
        options: props.exams,
        optionLabel: e => e.title,
    },
    {
        key: 'department',
        title: 'Department Report',
        desc: 'Performance overview for all students in a department across all exams and courses.',
        icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        color: 'bg-amber-50 text-amber-600',
        selector: 'department_id',
        label: 'Select Department',
        options: props.departments,
        optionLabel: d => d.name,
    },
    {
        key: 'course',
        title: 'Course Report',
        desc: 'Aggregate results for a course: pass rate, grade distribution, and top performers.',
        icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        color: 'bg-emerald-50 text-emerald-600',
        selector: 'course_id',
        label: 'Select Course',
        options: props.courses,
        optionLabel: c => `${c.title} (${c.code})`,
    },
]
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Reports</h1>
                <p class="text-sm text-slate-400 mt-0.5">Generate and download PDF reports</p>
            </div>
        </template>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div v-for="card in cards" :key="card.key"
                class="bg-white rounded-2xl border border-slate-200 p-6 flex flex-col gap-4">
                <!-- Header -->
                <div class="flex items-start gap-3">
                    <div :class="['w-10 h-10 rounded-xl flex items-center justify-center shrink-0', card.color]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-900">{{ card.title }}</h3>
                        <p class="text-sm text-slate-500 mt-0.5 leading-relaxed">{{ card.desc }}</p>
                    </div>
                </div>

                <!-- Selector -->
                <select v-model="selected[card.selector]"
                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">{{ card.label }}…</option>
                    <option v-for="opt in card.options" :key="opt.id" :value="opt.id">
                        {{ card.optionLabel(opt) }}
                    </option>
                </select>

                <!-- Download button -->
                <button @click="download(card.key)"
                    :disabled="!selected[card.selector]"
                    :class="['w-full h-10 rounded-xl text-sm font-semibold flex items-center justify-center gap-2 transition-opacity', selected[card.selector] ? 'text-white' : 'opacity-40 cursor-not-allowed text-white']"
                    :style="selected[card.selector] ? 'background:#BC2739' : 'background:#BC2739'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Download PDF
                </button>
            </div>
        </div>
    </AppLayout>
</template>
