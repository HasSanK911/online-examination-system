<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    exam:          { type: Object, required: true },
    questionBanks: { type: Array,  default: () => [] },
})

const selectedBankId = ref(props.questionBanks[0]?.id ?? null)
const search        = ref('')
const typeFilter    = ref('all')
const diffFilter    = ref('all')
const selectedIds   = ref([])

const currentBank = computed(() =>
    props.questionBanks.find(b => b.id === selectedBankId.value) ?? null
)

const examQuestionIds = computed(() =>
    new Set(props.exam.questions?.map(q => q.id) ?? [])
)

const filteredQuestions = computed(() => {
    if (!currentBank.value) return []
    return currentBank.value.questions.filter(q => {
        if (typeFilter.value !== 'all' && q.type !== typeFilter.value) return false
        if (diffFilter.value !== 'all' && q.difficulty !== diffFilter.value) return false
        if (search.value.trim()) {
            const plain = q.question_text.replace(/<[^>]+>/g, '').toLowerCase()
            if (!plain.includes(search.value.toLowerCase())) return false
        }
        return true
    })
})

const examQuestions = computed(() =>
    [...(props.exam.questions ?? [])].sort((a, b) => (a.pivot?.order ?? 0) - (b.pivot?.order ?? 0))
)

const totalMarks = computed(() =>
    examQuestions.value.reduce((s, q) => s + Number(q.pivot?.marks ?? q.marks ?? 0), 0)
)

const typeConfig = {
    mcq_single:   { label: 'MCQ',   cls: 'bg-blue-50 text-blue-700' },
    mcq_multiple: { label: 'Multi', cls: 'bg-violet-50 text-violet-700' },
    true_false:   { label: 'T/F',   cls: 'bg-emerald-50 text-emerald-700' },
    fill_blank:   { label: 'Fill',  cls: 'bg-amber-50 text-amber-700' },
    short:        { label: 'Short', cls: 'bg-orange-50 text-orange-700' },
    descriptive:  { label: 'Essay', cls: 'bg-rose-50 text-rose-700' },
}

const diffConfig = {
    easy:   'bg-emerald-100 text-emerald-700',
    medium: 'bg-amber-100 text-amber-700',
    hard:   'bg-red-100 text-red-700',
}

function toggleSelect(id) {
    const idx = selectedIds.value.indexOf(id)
    if (idx === -1) selectedIds.value.push(id)
    else selectedIds.value.splice(idx, 1)
}

function addQuestion(id) {
    router.post(`/teacher/exams/${props.exam.id}/questions`, { question_ids: [id] }, { preserveScroll: true })
}

function addSelected() {
    if (!selectedIds.value.length) return
    router.post(`/teacher/exams/${props.exam.id}/questions`, { question_ids: selectedIds.value }, {
        preserveScroll: true,
        onSuccess: () => { selectedIds.value = [] },
    })
}

function removeQuestion(qId) {
    router.delete(`/teacher/exams/${props.exam.id}/questions/${qId}`, { preserveScroll: true })
}

function updateMark(qId, marks) {
    router.patch(`/teacher/exams/${props.exam.id}/questions/${qId}`, { marks }, { preserveScroll: true })
}

function truncate(text, len = 80) {
    const plain = (text ?? '').replace(/<[^>]+>/g, '')
    return plain.length > len ? plain.slice(0, len) + '…' : plain
}

const typeFilters = [
    { key: 'all', label: 'All' },
    { key: 'mcq_single', label: 'MCQ' },
    { key: 'mcq_multiple', label: 'Multi' },
    { key: 'true_false', label: 'T/F' },
    { key: 'fill_blank', label: 'Fill' },
    { key: 'short', label: 'Short' },
    { key: 'descriptive', label: 'Essay' },
]
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link :href="`/teacher/exams/${exam.id}`"
                    class="p-2 rounded-lg text-slate-500 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </Link>
                <div>
                    <h1 class="text-xl font-bold text-slate-900 font-heading">Manage Questions</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ exam.title }}</p>
                </div>
            </div>
        </template>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

            <!-- LEFT: Question Bank Browser -->
            <div class="lg:col-span-3 bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100">
                    <h2 class="font-bold text-slate-900 text-sm mb-3">Question Bank</h2>

                    <!-- Bank selector -->
                    <select v-model="selectedBankId"
                        class="w-full h-9 px-3 rounded-xl border border-slate-200 text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-primary-500 bg-white mb-3">
                        <option v-if="!questionBanks.length" :value="null">No banks for this course</option>
                        <option v-for="b in questionBanks" :key="b.id" :value="b.id">{{ b.title }}</option>
                    </select>

                    <!-- Search -->
                    <div class="relative mb-3">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                        </svg>
                        <input v-model="search" type="text" placeholder="Search questions…"
                            class="w-full h-9 pl-9 pr-4 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"/>
                    </div>

                    <!-- Type filters -->
                    <div class="flex gap-1 flex-wrap mb-2">
                        <button v-for="f in typeFilters" :key="f.key"
                            @click="typeFilter = f.key"
                            :class="['px-2.5 py-1 rounded-lg text-xs font-medium transition-colors',
                                typeFilter === f.key ? 'text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200']"
                            :style="typeFilter === f.key ? 'background:#BC2739' : ''">
                            {{ f.label }}
                        </button>
                    </div>

                    <!-- Diff filters -->
                    <div class="flex gap-1">
                        <button v-for="d in ['all','easy','medium','hard']" :key="d"
                            @click="diffFilter = d"
                            :class="['px-2.5 py-1 rounded-lg text-xs font-medium capitalize transition-colors',
                                diffFilter === d ? 'text-white' : 'bg-slate-100 text-slate-500 hover:bg-slate-200']"
                            :style="diffFilter === d ? 'background:#BC2739' : ''">
                            {{ d === 'all' ? 'All Levels' : d }}
                        </button>
                    </div>
                </div>

                <!-- Add selected banner -->
                <div v-if="selectedIds.length" class="px-5 py-2.5 bg-primary-50 border-b border-primary-100 flex items-center justify-between">
                    <span class="text-sm font-medium text-primary-700">{{ selectedIds.length }} selected</span>
                    <button @click="addSelected"
                        class="px-3 py-1 rounded-lg text-xs font-semibold text-white"
                        style="background:#BC2739;">
                        Add Selected
                    </button>
                </div>

                <!-- Question list -->
                <div class="divide-y divide-slate-100 overflow-y-auto max-h-[560px]">
                    <div v-if="!questionBanks.length" class="py-12 text-center text-sm text-slate-400">
                        No question banks for this course.
                        <br/>
                        <Link href="/teacher/question-banks" class="text-primary-600 hover:underline mt-1 inline-block">Create one →</Link>
                    </div>
                    <div v-else-if="!filteredQuestions.length" class="py-12 text-center text-sm text-slate-400">
                        No questions match your filters.
                    </div>

                    <div v-for="q in filteredQuestions" :key="q.id"
                        class="flex items-start gap-3 px-4 py-3.5 hover:bg-slate-50 transition-colors">
                        <input type="checkbox"
                            :checked="selectedIds.includes(q.id)"
                            :disabled="examQuestionIds.has(q.id)"
                            @change="toggleSelect(q.id)"
                            class="mt-1 w-4 h-4 rounded border-slate-300 accent-primary-600 flex-shrink-0"/>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 leading-snug">{{ truncate(q.question_text) }}</p>
                            <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                                <span :class="['px-1.5 py-0.5 rounded text-xs font-semibold', typeConfig[q.type]?.cls ?? 'bg-slate-100 text-slate-500']">
                                    {{ typeConfig[q.type]?.label ?? q.type }}
                                </span>
                                <span :class="['px-1.5 py-0.5 rounded text-xs capitalize', diffConfig[q.difficulty] ?? 'bg-slate-100 text-slate-500']">
                                    {{ q.difficulty }}
                                </span>
                                <span class="px-1.5 py-0.5 rounded text-xs bg-slate-100 text-slate-500">{{ q.marks }} mk</span>
                            </div>
                        </div>

                        <div class="flex-shrink-0">
                            <div v-if="examQuestionIds.has(q.id)"
                                class="w-7 h-7 rounded-lg bg-emerald-50 flex items-center justify-center">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <button v-else @click="addQuestion(q.id)"
                                class="w-7 h-7 rounded-lg flex items-center justify-center text-white transition-all"
                                style="background:#BC2739;">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Exam Question List -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 overflow-hidden flex flex-col">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                    <h2 class="font-bold text-slate-900 text-sm">In This Exam</h2>
                    <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                        {{ examQuestions.length }} Q · {{ totalMarks }} marks
                    </span>
                </div>

                <div class="divide-y divide-slate-100 overflow-y-auto max-h-[600px]">
                    <div v-if="!examQuestions.length" class="py-12 text-center text-sm text-slate-400">
                        No questions yet. Add from the left panel.
                    </div>

                    <div v-for="(q, i) in examQuestions" :key="q.id"
                        class="flex items-start gap-3 px-4 py-3.5">

                        <!-- Order badge -->
                        <span class="w-6 h-6 rounded-md bg-slate-100 text-slate-500 text-xs font-bold flex items-center justify-center flex-shrink-0 mt-0.5">
                            {{ q.pivot?.order ?? i + 1 }}
                        </span>

                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-slate-700 leading-snug">{{ truncate(q.question_text, 60) }}</p>
                            <span :class="['px-1.5 py-0.5 rounded text-xs font-semibold mt-1 inline-block', typeConfig[q.type]?.cls ?? 'bg-slate-100 text-slate-500']">
                                {{ typeConfig[q.type]?.label ?? q.type }}
                            </span>
                        </div>

                        <!-- Marks input -->
                        <input type="number" min="0" step="0.5"
                            :value="q.pivot?.marks ?? q.marks"
                            @change="updateMark(q.id, $event.target.value)"
                            class="w-14 h-7 text-center rounded-lg border border-slate-200 text-sm font-semibold text-slate-900 focus:outline-none focus:ring-2 focus:ring-primary-500"/>

                        <!-- Remove -->
                        <button @click="removeQuestion(q.id)"
                            class="p-1 rounded-lg text-slate-300 hover:bg-red-50 hover:text-red-500 transition-colors flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div v-if="examQuestions.length" class="px-5 py-3 border-t border-slate-100 bg-slate-50 flex justify-between items-center">
                    <span class="text-xs text-slate-500">Total marks</span>
                    <span class="font-bold text-slate-900">{{ totalMarks }}</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
