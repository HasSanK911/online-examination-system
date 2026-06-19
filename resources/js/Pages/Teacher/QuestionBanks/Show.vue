<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// ---------------------------------------------------------------------------
// Props
// ---------------------------------------------------------------------------
const props = defineProps({
    bank: {
        type: Object,
        required: true,
        // shape: { id, title, description, course:{title,code}, creator:{name},
        //          questions: [{ id, type, question_text, marks, difficulty, tags[], options[] }] }
    },
})

// ---------------------------------------------------------------------------
// Type meta
// ---------------------------------------------------------------------------
const TYPE_META = {
    mcq_single:   { label: 'MCQ Single',   color: 'bg-blue-50 text-blue-700 ring-blue-200',     icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', autoGraded: true  },
    mcq_multiple: { label: 'MCQ Multiple', color: 'bg-violet-50 text-violet-700 ring-violet-200', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', autoGraded: true  },
    true_false:   { label: 'True / False', color: 'bg-emerald-50 text-emerald-700 ring-emerald-200', icon: 'M5 13l4 4L19 7', autoGraded: true  },
    fill_blank:   { label: 'Fill Blank',   color: 'bg-amber-50 text-amber-700 ring-amber-200',   icon: 'M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z', autoGraded: true  },
    short:        { label: 'Short Answer', color: 'bg-orange-50 text-orange-700 ring-orange-200', icon: 'M4 6h16M4 12h8m-8 6h16', autoGraded: false },
    descriptive:  { label: 'Descriptive',  color: 'bg-rose-50 text-rose-700 ring-rose-200',      icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', autoGraded: false },
}

const DIFFICULTY_META = {
    easy:   { label: 'Easy',   cls: 'bg-green-50 text-green-700'  },
    medium: { label: 'Medium', cls: 'bg-amber-50 text-amber-700'  },
    hard:   { label: 'Hard',   cls: 'bg-red-50 text-red-600'      },
}

// ---------------------------------------------------------------------------
// Stats
// ---------------------------------------------------------------------------
const totalQuestions   = computed(() => props.bank.questions?.length ?? 0)
const mcqCount         = computed(() => props.bank.questions?.filter(q => q.type === 'mcq_single' || q.type === 'mcq_multiple' || q.type === 'true_false').length ?? 0)
const autoGradedCount  = computed(() => props.bank.questions?.filter(q => TYPE_META[q.type]?.autoGraded).length ?? 0)
const manualCount      = computed(() => totalQuestions.value - autoGradedCount.value)

// ---------------------------------------------------------------------------
// Delete question
// ---------------------------------------------------------------------------
function deleteQuestion(questionId) {
    if (!confirm('Delete this question permanently? This cannot be undone.')) return
    router.delete(`/teacher/question-banks/${props.bank.id}/questions/${questionId}`, {
        preserveScroll: true,
    })
}

// ---------------------------------------------------------------------------
// Modal state
// ---------------------------------------------------------------------------
const showModal = ref(false)
const submitting = ref(false)

const defaultForm = () => ({
    type:            'mcq_single',
    question_text:   '',
    marks:           1,
    difficulty:      'medium',
    tags:            '',
    correct_answer:  '',
    options: [
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
        { text: '', is_correct: false },
    ],
})

const form = ref(defaultForm())
const formErrors = ref({})

function openModal() {
    form.value = defaultForm()
    formErrors.value = {}
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

// When type changes, reset option correctness to avoid carrying over incompatible state
function onTypeChange() {
    form.value.options.forEach(o => { o.is_correct = false })
    form.value.correct_answer = ''
}

// mcq_single: only one option can be correct
function selectSingleCorrect(index) {
    form.value.options.forEach((o, i) => { o.is_correct = (i === index) })
}

// true_false helpers
function setTFCorrect(val) {
    // val: 'true' | 'false'
    form.value.options[0].is_correct = (val === 'true')
    form.value.options[1].is_correct = (val === 'false')
}
const tfCorrectValue = computed(() => {
    if (form.value.options[0].is_correct) return 'true'
    if (form.value.options[1].is_correct) return 'false'
    return ''
})

function submitForm() {
    formErrors.value = {}

    // Basic client-side validation
    if (!form.value.question_text.trim()) {
        formErrors.value.question_text = 'Question text is required.'
        return
    }
    if (!form.value.marks || form.value.marks <= 0) {
        formErrors.value.marks = 'Marks must be greater than 0.'
        return
    }

    const payload = {
        type:           form.value.type,
        question_text:  form.value.question_text,
        marks:          form.value.marks,
        difficulty:     form.value.difficulty,
        tags:           form.value.tags,
        correct_answer: form.value.correct_answer,
        options:        [],
    }

    if (['mcq_single', 'mcq_multiple'].includes(form.value.type)) {
        payload.options = form.value.options.map((o, i) => ({
            option_text: o.text,
            is_correct:  o.is_correct,
            order:       i + 1,
        }))
    } else if (form.value.type === 'true_false') {
        payload.options = [
            { option_text: 'True',  is_correct: form.value.options[0].is_correct, order: 1 },
            { option_text: 'False', is_correct: form.value.options[1].is_correct, order: 2 },
        ]
    }

    submitting.value = true
    router.post(`/teacher/question-banks/${props.bank.id}/questions`, payload, {
        preserveScroll: true,
        onSuccess: () => {
            closeModal()
            submitting.value = false
        },
        onError: (errors) => {
            formErrors.value = errors
            submitting.value = false
        },
    })
}

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
function typeMeta(type) {
    return TYPE_META[type] ?? TYPE_META.descriptive
}
function difficultyMeta(difficulty) {
    return DIFFICULTY_META[difficulty] ?? DIFFICULTY_META.medium
}
function hasOptions(type) {
    return ['mcq_single', 'mcq_multiple', 'true_false'].includes(type)
}
function correctOptions(question) {
    return question.options?.filter(o => o.is_correct) ?? []
}
function sortedOptions(question) {
    return [...(question.options ?? [])].sort((a, b) => a.order - b.order)
}
</script>

<template>
    <AppLayout>
        <!-- ================================================================ -->
        <!-- Header slot                                                       -->
        <!-- ================================================================ -->
        <template #header>
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <!-- Left: back + title -->
                <div class="flex items-center gap-3 min-w-0">
                    <Link href="/teacher/question-banks"
                        class="flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-700 transition-colors flex-shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </Link>
                    <div class="min-w-0">
                        <div class="flex items-center gap-2.5 flex-wrap">
                            <h1 class="text-xl font-bold text-slate-900 font-heading truncate">{{ bank.title }}</h1>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-primary-50 text-primary-700 ring-1 ring-primary-200 flex-shrink-0">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ bank.course?.code }} &mdash; {{ bank.course?.title }}
                            </span>
                        </div>
                        <p class="text-sm text-slate-400 mt-0.5">
                            Created by {{ bank.creator?.name }}
                            <span v-if="bank.description"> &middot; {{ bank.description }}</span>
                        </p>
                    </div>
                </div>

                <!-- Right: Add Question button -->
                <button @click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95 flex-shrink-0"
                    style="background:#BC2739;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Question
                </button>
            </div>
        </template>

        <!-- ================================================================ -->
        <!-- Stats row                                                         -->
        <!-- ================================================================ -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <!-- Total Questions -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ totalQuestions }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Total Questions</p>
                </div>
            </div>

            <!-- MCQ Count -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ mcqCount }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">MCQ / T-F</p>
                </div>
            </div>

            <!-- Auto-graded -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ autoGradedCount }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Auto-Graded</p>
                </div>
            </div>

            <!-- Manual -->
            <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
                <div class="w-11 h-11 rounded-xl bg-rose-50 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-slate-900">{{ manualCount }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">Manual Review</p>
                </div>
            </div>
        </div>

        <!-- ================================================================ -->
        <!-- Empty state                                                       -->
        <!-- ================================================================ -->
        <div v-if="!bank.questions?.length"
            class="bg-white rounded-2xl border border-slate-200 py-20 text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="text-slate-600 font-semibold mb-1">No questions yet</p>
            <p class="text-slate-400 text-sm mb-6">Start building your question bank by adding the first question.</p>
            <button @click="openModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                style="background:#BC2739;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add First Question
            </button>
        </div>

        <!-- ================================================================ -->
        <!-- Questions list                                                    -->
        <!-- ================================================================ -->
        <div v-else class="flex flex-col gap-4">
            <div v-for="(question, qIdx) in bank.questions" :key="question.id"
                class="bg-white rounded-2xl border border-slate-200 p-5 hover:border-slate-300 hover:shadow-sm transition-all">

                <!-- Top row: badges + marks + delete -->
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div class="flex items-center gap-2 flex-wrap">
                        <!-- Question number -->
                        <span class="w-6 h-6 rounded-md bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500 flex-shrink-0">
                            {{ qIdx + 1 }}
                        </span>

                        <!-- Type badge -->
                        <span :class="['inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold ring-1', typeMeta(question.type).color]">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="typeMeta(question.type).icon"/>
                            </svg>
                            {{ typeMeta(question.type).label }}
                        </span>

                        <!-- Difficulty chip -->
                        <span :class="['px-2 py-0.5 rounded-full text-xs font-medium', difficultyMeta(question.difficulty).cls]">
                            {{ difficultyMeta(question.difficulty).label }}
                        </span>

                        <!-- Auto/Manual chip -->
                        <span v-if="typeMeta(question.type).autoGraded"
                            class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-600">
                            Auto-graded
                        </span>
                        <span v-else class="px-2 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-600">
                            Manual review
                        </span>
                    </div>

                    <!-- Right side: marks badge + delete -->
                    <div class="flex items-center gap-2 flex-shrink-0">
                        <span class="px-3 py-1 rounded-xl bg-slate-100 text-slate-700 text-xs font-bold whitespace-nowrap">
                            {{ question.marks }} mark{{ question.marks != 1 ? 's' : '' }}
                        </span>
                        <button @click="deleteQuestion(question.id)"
                            title="Delete question"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Question text (supports HTML) -->
                <div class="prose prose-sm max-w-none text-slate-800 mb-3 leading-relaxed"
                    v-html="question.question_text"/>

                <!-- Options (MCQ / True-False) -->
                <div v-if="hasOptions(question.type)" class="mb-3 grid gap-1.5">
                    <div v-for="option in sortedOptions(question)" :key="option.id"
                        :class="['flex items-start gap-2.5 px-3 py-2 rounded-xl text-sm transition-colors',
                            option.is_correct
                                ? 'bg-emerald-50 border border-emerald-200'
                                : 'bg-slate-50 border border-slate-100']">
                        <!-- Correct indicator -->
                        <span v-if="option.is_correct"
                            class="mt-0.5 flex-shrink-0 w-4 h-4 rounded-full bg-emerald-500 flex items-center justify-center">
                            <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span v-else
                            class="mt-0.5 flex-shrink-0 w-4 h-4 rounded-full border-2 border-slate-300"/>
                        <span :class="option.is_correct ? 'text-emerald-800 font-medium' : 'text-slate-600'">
                            {{ option.option_text }}
                        </span>
                    </div>
                </div>

                <!-- Correct answer (fill blank) -->
                <div v-if="question.type === 'fill_blank' && question.options?.length"
                    class="mb-3 flex items-center gap-2 px-3 py-2 bg-amber-50 border border-amber-200 rounded-xl text-sm">
                    <svg class="w-4 h-4 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                    </svg>
                    <span class="text-amber-700 font-medium">Answer:</span>
                    <span class="text-amber-800">{{ question.options[0]?.option_text }}</span>
                </div>

                <!-- Tags -->
                <div v-if="question.tags?.length" class="flex flex-wrap gap-1.5">
                    <span v-for="tag in question.tags" :key="tag"
                        class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-500 text-xs font-medium">
                        #{{ tag }}
                    </span>
                </div>
            </div>
        </div>
    </AppLayout>

    <!-- ================================================================== -->
    <!-- Add Question Modal (Teleported to body)                             -->
    <!-- ================================================================== -->
    <Teleport to="body">
        <Transition
            enter-active-class="transition-opacity duration-200"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition-opacity duration-150"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0">
            <div v-if="showModal"
                class="fixed inset-0 z-50 flex items-center justify-center p-4"
                @click.self="closeModal">
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"/>

                <!-- Panel -->
                <div class="relative z-10 w-full max-w-2xl max-h-[90vh] flex flex-col bg-white rounded-2xl shadow-2xl">
                    <!-- Modal header -->
                    <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                        <div>
                            <h2 class="text-lg font-bold text-slate-900">Add Question</h2>
                            <p class="text-xs text-slate-400 mt-0.5">{{ bank.title }}</p>
                        </div>
                        <button @click="closeModal"
                            class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Scrollable body -->
                    <div class="overflow-y-auto flex-1 px-6 py-5 space-y-5">

                        <!-- Question type selector -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-2">Question Type</label>
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                <button v-for="(meta, typeKey) in TYPE_META" :key="typeKey"
                                    type="button"
                                    @click="form.type = typeKey; onTypeChange()"
                                    :class="['flex items-center gap-2 px-3 py-2.5 rounded-xl text-xs font-semibold border transition-all text-left',
                                        form.type === typeKey
                                            ? 'ring-2 ring-offset-1 border-transparent ' + meta.color.replace('ring-', 'ring-')
                                            : 'border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50']"
                                    :style="form.type === typeKey ? '' : ''">
                                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="meta.icon"/>
                                    </svg>
                                    {{ meta.label }}
                                </button>
                            </div>
                        </div>

                        <!-- Question text -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Question Text <span class="text-red-500">*</span>
                            </label>
                            <textarea
                                v-model="form.question_text"
                                rows="4"
                                placeholder="Enter the question text here..."
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:border-transparent resize-none transition-colors"
                                style="--tw-ring-color:#BC2739;"
                                :class="formErrors.question_text ? 'border-red-400 bg-red-50' : 'focus:ring-[#BC2739]'"/>
                            <p v-if="formErrors.question_text" class="mt-1 text-xs text-red-500">{{ formErrors.question_text }}</p>
                        </div>

                        <!-- Marks + Difficulty row -->
                        <div class="grid grid-cols-2 gap-4">
                            <!-- Marks -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                    Marks <span class="text-red-500">*</span>
                                </label>
                                <input
                                    v-model.number="form.marks"
                                    type="number"
                                    min="0.5"
                                    step="0.5"
                                    placeholder="e.g. 2"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#BC2739] focus:border-transparent transition-colors"
                                    :class="formErrors.marks ? 'border-red-400 bg-red-50' : ''"/>
                                <p v-if="formErrors.marks" class="mt-1 text-xs text-red-500">{{ formErrors.marks }}</p>
                            </div>

                            <!-- Difficulty -->
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Difficulty</label>
                                <select
                                    v-model="form.difficulty"
                                    class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-[#BC2739] focus:border-transparent transition-colors bg-white">
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tags -->
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tags</label>
                            <input
                                v-model="form.tags"
                                type="text"
                                placeholder="e.g. algebra, chapter-3, important"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#BC2739] focus:border-transparent transition-colors"/>
                            <p class="mt-1 text-xs text-slate-400">Separate multiple tags with commas.</p>
                        </div>

                        <!-- ============================================ -->
                        <!-- Conditional: MCQ Single / Multiple options   -->
                        <!-- ============================================ -->
                        <div v-if="form.type === 'mcq_single' || form.type === 'mcq_multiple'">
                            <label class="block text-xs font-semibold text-slate-700 mb-2">
                                Answer Options
                                <span class="ml-1 font-normal text-slate-400">
                                    ({{ form.type === 'mcq_single' ? 'select one correct' : 'select all correct' }})
                                </span>
                            </label>
                            <div class="space-y-2">
                                <div v-for="(opt, idx) in form.options" :key="idx"
                                    class="flex items-center gap-2.5">
                                    <!-- Correct indicator -->
                                    <div v-if="form.type === 'mcq_single'">
                                        <input
                                            type="radio"
                                            :name="'correct_opt'"
                                            :value="idx"
                                            :checked="opt.is_correct"
                                            @change="selectSingleCorrect(idx)"
                                            class="w-4 h-4 cursor-pointer accent-[#BC2739]"
                                            :title="`Mark option ${idx + 1} as correct`"/>
                                    </div>
                                    <div v-else>
                                        <input
                                            type="checkbox"
                                            v-model="opt.is_correct"
                                            class="w-4 h-4 cursor-pointer rounded accent-[#BC2739]"
                                            :title="`Mark option ${idx + 1} as correct`"/>
                                    </div>
                                    <!-- Option text -->
                                    <input
                                        v-model="opt.text"
                                        type="text"
                                        :placeholder="`Option ${idx + 1}`"
                                        class="flex-1 rounded-xl border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#BC2739] focus:border-transparent transition-colors"/>
                                </div>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-400">
                                <svg class="inline w-3 h-3 mr-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Click the radio/checkbox to the left to mark the correct answer(s).
                            </p>
                        </div>

                        <!-- ============================================ -->
                        <!-- Conditional: True / False                    -->
                        <!-- ============================================ -->
                        <div v-if="form.type === 'true_false'">
                            <label class="block text-xs font-semibold text-slate-700 mb-2">Correct Answer</label>
                            <div class="flex gap-3">
                                <label :class="['flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all',
                                    tfCorrectValue === 'true'
                                        ? 'border-emerald-400 bg-emerald-50'
                                        : 'border-slate-200 hover:border-slate-300']">
                                    <input type="radio" name="tf_correct" value="true"
                                        :checked="tfCorrectValue === 'true'"
                                        @change="setTFCorrect('true')"
                                        class="w-4 h-4 accent-emerald-500"/>
                                    <span :class="['font-semibold text-sm', tfCorrectValue === 'true' ? 'text-emerald-700' : 'text-slate-600']">
                                        True
                                    </span>
                                </label>
                                <label :class="['flex-1 flex items-center gap-3 px-4 py-3 rounded-xl border-2 cursor-pointer transition-all',
                                    tfCorrectValue === 'false'
                                        ? 'border-red-400 bg-red-50'
                                        : 'border-slate-200 hover:border-slate-300']">
                                    <input type="radio" name="tf_correct" value="false"
                                        :checked="tfCorrectValue === 'false'"
                                        @change="setTFCorrect('false')"
                                        class="w-4 h-4 accent-red-500"/>
                                    <span :class="['font-semibold text-sm', tfCorrectValue === 'false' ? 'text-red-600' : 'text-slate-600']">
                                        False
                                    </span>
                                </label>
                            </div>
                        </div>

                        <!-- ============================================ -->
                        <!-- Conditional: Fill in the Blank               -->
                        <!-- ============================================ -->
                        <div v-if="form.type === 'fill_blank'">
                            <label class="block text-xs font-semibold text-slate-700 mb-1.5">
                                Correct Answer <span class="text-red-500">*</span>
                            </label>
                            <input
                                v-model="form.correct_answer"
                                type="text"
                                placeholder="Expected answer (case-insensitive match)"
                                class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-[#BC2739] focus:border-transparent transition-colors"
                                :class="formErrors.correct_answer ? 'border-red-400 bg-red-50' : ''"/>
                            <p v-if="formErrors.correct_answer" class="mt-1 text-xs text-red-500">{{ formErrors.correct_answer }}</p>
                            <p class="mt-1 text-xs text-slate-400">Matching is case-insensitive by default.</p>
                        </div>

                        <!-- Short / Descriptive: info note only -->
                        <div v-if="form.type === 'short' || form.type === 'descriptive'"
                            class="flex items-start gap-2.5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl">
                            <svg class="w-4 h-4 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <p class="text-xs text-amber-700">
                                <span class="font-semibold">Manual evaluation required.</span>
                                This question type requires teacher review — students' answers will be shown to you after the exam for marking.
                            </p>
                        </div>

                        <!-- General server errors -->
                        <div v-if="Object.keys(formErrors).length && !formErrors.question_text && !formErrors.marks && !formErrors.correct_answer"
                            class="px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-xs text-red-600">
                            <p v-for="(msg, field) in formErrors" :key="field">{{ msg }}</p>
                        </div>

                    </div><!-- /scroll body -->

                    <!-- Modal footer -->
                    <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100">
                        <button type="button" @click="closeModal"
                            class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors">
                            Cancel
                        </button>
                        <button type="button" @click="submitForm" :disabled="submitting"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 disabled:opacity-60 disabled:cursor-not-allowed"
                            style="background:#BC2739;">
                            <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
                            </svg>
                            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            {{ submitting ? 'Saving...' : 'Add Question' }}
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
