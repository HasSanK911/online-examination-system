<script setup>
import { computed } from 'vue'
import { Link, useForm, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    courses: { type: Array, default: () => [] },
    exam:    { type: Object, default: null },
})

const isEdit = computed(() => !!props.exam)

const form = useForm({
    course_id:               props.exam?.course_id ?? '',
    title:                   props.exam?.title ?? '',
    description:             props.exam?.description ?? '',
    instructions:            props.exam?.instructions ?? '',
    total_marks:             props.exam?.total_marks ?? 100,
    passing_marks:           props.exam?.passing_marks ?? 50,
    duration_minutes:        props.exam?.duration_minutes ?? 60,
    start_time:              props.exam?.start_time?.replace(' ', 'T').slice(0, 16) ?? '',
    end_time:                props.exam?.end_time?.replace(' ', 'T').slice(0, 16) ?? '',
    shuffle_questions:       props.exam?.shuffle_questions ?? false,
    shuffle_options:         props.exam?.shuffle_options ?? false,
    allow_backtrack:         props.exam?.allow_backtrack ?? true,
    show_result_immediately: props.exam?.show_result_immediately ?? false,
})

function submit() {
    if (isEdit.value) {
        form.put(`/teacher/exams/${props.exam.id}`)
    } else {
        form.post('/teacher/exams')
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="isEdit ? `/teacher/exams/${exam.id}` : '/teacher/exams'"
                    class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 font-heading">{{ isEdit ? 'Edit Exam' : 'Create Exam' }}</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ isEdit ? 'Update exam details' : 'Set up a new examination' }}</p>
                </div>
            </div>
        </template>

        <form @submit.prevent="submit" class="max-w-5xl">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                <!-- Left: Main info -->
                <div class="lg:col-span-2 space-y-4">

                    <!-- Basic info -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                        <h2 class="font-bold text-slate-900 text-sm uppercase tracking-wider text-slate-400">Basic Information</h2>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Course <span class="text-red-500">*</span></label>
                            <select v-model="form.course_id"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white"
                                :class="{ 'border-red-400': form.errors.course_id }">
                                <option value="">Select a course…</option>
                                <option v-for="c in courses" :key="c.id" :value="c.id">
                                    {{ c.title }} ({{ c.code }}) — {{ c.department?.name }}
                                </option>
                            </select>
                            <p v-if="form.errors.course_id" class="mt-1 text-xs text-red-500">{{ form.errors.course_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Exam Title <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" placeholder="e.g. Mid-Term Examination – Spring 2026"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': form.errors.title }"/>
                            <p v-if="form.errors.title" class="mt-1 text-xs text-red-500">{{ form.errors.title }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                            <textarea v-model="form.description" rows="2" placeholder="Brief exam description (optional)"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"/>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Student Instructions</label>
                            <textarea v-model="form.instructions" rows="4" placeholder="Rules, guidelines, and instructions shown to students before starting the exam…"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"/>
                        </div>
                    </div>
                </div>

                <!-- Right: Settings -->
                <div class="space-y-4">

                    <!-- Marks & Duration -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                        <h2 class="font-bold text-sm uppercase tracking-wider text-slate-400">Marks & Duration</h2>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Total Marks <span class="text-red-500">*</span></label>
                            <input v-model.number="form.total_marks" type="number" min="1"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': form.errors.total_marks }"/>
                            <p v-if="form.errors.total_marks" class="mt-1 text-xs text-red-500">{{ form.errors.total_marks }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Passing Marks <span class="text-red-500">*</span></label>
                            <input v-model.number="form.passing_marks" type="number" min="0"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': form.errors.passing_marks }"/>
                            <p v-if="form.errors.passing_marks" class="mt-1 text-xs text-red-500">{{ form.errors.passing_marks }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Duration (minutes) <span class="text-red-500">*</span></label>
                            <input v-model.number="form.duration_minutes" type="number" min="5" max="300"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': form.errors.duration_minutes }"/>
                            <p v-if="form.errors.duration_minutes" class="mt-1 text-xs text-red-500">{{ form.errors.duration_minutes }}</p>
                        </div>
                    </div>

                    <!-- Schedule -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-4">
                        <h2 class="font-bold text-sm uppercase tracking-wider text-slate-400">Schedule</h2>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Start Time <span class="text-red-500">*</span></label>
                            <input v-model="form.start_time" type="datetime-local"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': form.errors.start_time }"/>
                            <p v-if="form.errors.start_time" class="mt-1 text-xs text-red-500">{{ form.errors.start_time }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">End Time <span class="text-red-500">*</span></label>
                            <input v-model="form.end_time" type="datetime-local"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': form.errors.end_time }"/>
                            <p v-if="form.errors.end_time" class="mt-1 text-xs text-red-500">{{ form.errors.end_time }}</p>
                        </div>
                    </div>

                    <!-- Options -->
                    <div class="bg-white rounded-2xl border border-slate-200 p-6 space-y-3">
                        <h2 class="font-bold text-sm uppercase tracking-wider text-slate-400">Options</h2>

                        <label v-for="opt in [
                            { key: 'shuffle_questions',       label: 'Shuffle question order' },
                            { key: 'shuffle_options',         label: 'Shuffle answer options' },
                            { key: 'allow_backtrack',         label: 'Allow backtracking' },
                            { key: 'show_result_immediately', label: 'Show results immediately' },
                        ]" :key="opt.key"
                            class="flex items-center gap-3 cursor-pointer select-none group">
                            <button type="button" @click="form[opt.key] = !form[opt.key]"
                                :class="['relative w-10 h-5 rounded-full transition-colors duration-200 flex-shrink-0', form[opt.key] ? '' : 'bg-slate-200']"
                                :style="form[opt.key] ? 'background:#BC2739' : ''">
                                <span :class="['absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200', form[opt.key] ? 'translate-x-5' : 'translate-x-0.5']"></span>
                            </button>
                            <span class="text-sm text-slate-700 group-hover:text-slate-900">{{ opt.label }}</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full h-11 rounded-xl text-sm font-semibold text-white transition-all disabled:opacity-60 flex items-center justify-center gap-2"
                        style="background:#BC2739;">
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Saving…' : (isEdit ? 'Update Exam' : 'Create Exam') }}
                    </button>
                </div>
            </div>
        </form>
    </AppLayout>
</template>
