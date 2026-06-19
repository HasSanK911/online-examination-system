<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    exam:             { type: Object, required: true },
    attempt:          { type: Object, required: true },
    manual_questions: { type: Array,  default: () => [] },
})

const evaluations = reactive(
    props.manual_questions.map(q => ({
        question_id:    q.question_id,
        obtained_marks: q.obtained_marks ?? '',
        feedback:       q.feedback ?? '',
        max_marks:      q.max_marks,
    }))
)

function submit() {
    router.post(
        `/teacher/exams/${props.exam.id}/evaluate/${props.attempt.id}`,
        { evaluations },
        { preserveScroll: true }
    )
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="`/teacher/exams/${exam.id}/evaluate`"
                    class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900">Evaluate: {{ attempt.student.name }}</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ attempt.student.student_id }} · {{ exam.title }}</p>
                </div>
            </div>
        </template>

        <div v-if="!manual_questions.length" class="bg-white rounded-2xl border border-slate-200 p-12 text-center text-slate-400">
            No manual questions to evaluate.
        </div>

        <form v-else @submit.prevent="submit" class="space-y-5">
            <div v-for="(q, i) in manual_questions" :key="q.question_id"
                class="bg-white rounded-2xl border border-slate-200 p-5">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600 capitalize">{{ q.type?.replace('_', ' ') }}</span>
                            <span class="text-xs text-slate-400">Max: {{ q.max_marks }} marks</span>
                        </div>
                        <div class="text-sm text-slate-800 font-medium leading-relaxed" v-html="q.question_text"/>
                    </div>
                </div>

                <!-- Student answer -->
                <div class="bg-slate-50 rounded-xl p-4 mb-4">
                    <p class="text-xs text-slate-400 font-medium mb-1.5 uppercase">Student's Answer</p>
                    <p class="text-sm text-slate-700 whitespace-pre-wrap leading-relaxed">{{ q.text_answer || 'No answer provided.' }}</p>
                </div>

                <!-- Marks + feedback -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">
                            Marks Awarded <span class="text-slate-400 font-normal">(0 – {{ q.max_marks }})</span>
                        </label>
                        <input v-model.number="evaluations[i].obtained_marks"
                            type="number" min="0" :max="q.max_marks" step="0.5"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="0"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1.5">Feedback <span class="text-slate-400 font-normal">(optional)</span></label>
                        <input v-model="evaluations[i].feedback" type="text"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                            placeholder="Brief feedback for student…"/>
                    </div>
                </div>
            </div>

            <!-- Submit -->
            <div class="flex justify-end gap-3 pt-2">
                <Link :href="`/teacher/exams/${exam.id}/evaluate`"
                    class="px-5 h-10 rounded-xl text-sm font-medium text-slate-600 bg-slate-100 inline-flex items-center">
                    Cancel
                </Link>
                <button type="submit"
                    class="px-6 h-10 rounded-xl text-sm font-semibold text-white"
                    style="background:#BC2739;">
                    Save Evaluation
                </button>
            </div>
        </form>
    </AppLayout>
</template>
