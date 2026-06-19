<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    banks:   { type: Array, default: () => [] },
    courses: { type: Array, default: () => [] },
})

// ── Modal state ──────────────────────────────────────────────────────────────
const showModal = ref(false)
const submitting = ref(false)
const errors = reactive({})

const form = reactive({
    title:       '',
    course_id:   '',
    description: '',
})

function openModal() {
    form.title       = ''
    form.course_id   = ''
    form.description = ''
    Object.keys(errors).forEach(k => delete errors[k])
    showModal.value = true
}

function closeModal() {
    showModal.value = false
}

function handleSubmit() {
    Object.keys(errors).forEach(k => delete errors[k])

    if (!form.title.trim()) {
        errors.title = 'Title is required.'
        return
    }
    if (!form.course_id) {
        errors.course_id = 'Please select a course.'
        return
    }

    submitting.value = true

    router.post('/teacher/question-banks', form, {
        onError: (errs) => {
            Object.assign(errors, errs)
            submitting.value = false
        },
        onSuccess: () => {
            submitting.value = false
            closeModal()
        },
        onFinish: () => {
            submitting.value = false
        },
    })
}

// ── Helpers ──────────────────────────────────────────────────────────────────
function formatDate(dateStr) {
    if (!dateStr) return '—'
    return new Date(dateStr).toLocaleDateString('en-GB', {
        day:   '2-digit',
        month: 'short',
        year:  'numeric',
    })
}

function closeOnBackdrop(e) {
    if (e.target === e.currentTarget) closeModal()
}
</script>

<template>
    <AppLayout>
        <!-- ── Page header ───────────────────────────────────────────────── -->
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 font-heading">Question Banks</h1>
                    <p class="text-sm text-slate-400 mt-0.5">
                        {{ banks.length }} bank{{ banks.length !== 1 ? 's' : '' }}
                    </p>
                </div>

                <button
                    @click="openModal"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95"
                    style="background: #BC2739;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Bank
                </button>
            </div>
        </template>

        <!-- ── Empty state ───────────────────────────────────────────────── -->
        <div v-if="!banks.length"
            class="bg-white rounded-2xl border border-slate-200 py-24 flex flex-col items-center text-center px-6">
            <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-4"
                style="background: #fef2f2;">
                <svg class="w-8 h-8" style="color: #BC2739;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <p class="text-slate-700 font-semibold text-lg mb-1">No question banks yet</p>
            <p class="text-slate-400 text-sm mb-6 max-w-xs">
                Create your first question bank to start building exam questions for your courses.
            </p>
            <button
                @click="openModal"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90"
                style="background: #BC2739;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                New Bank
            </button>
        </div>

        <!-- ── Banks grid ────────────────────────────────────────────────── -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
                v-for="bank in banks"
                :key="bank.id"
                class="bg-white rounded-2xl border border-slate-200 p-5 flex flex-col gap-4 hover:border-slate-300 hover:shadow-sm transition-all">

                <!-- Card top: icon + title -->
                <div class="flex items-start gap-3">
                    <div class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0"
                        style="background: #fef2f2;">
                        <svg class="w-5 h-5" style="color: #BC2739;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-bold text-slate-900 text-base leading-tight truncate">
                            {{ bank.title }}
                        </h3>
                        <p v-if="bank.description" class="text-xs text-slate-400 mt-0.5 line-clamp-2">
                            {{ bank.description }}
                        </p>
                    </div>
                </div>

                <!-- Course chip -->
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-600">
                        <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span class="font-mono">{{ bank.course?.code }}</span>
                        <span class="text-slate-400">·</span>
                        <span>{{ bank.course?.title }}</span>
                    </span>

                    <span class="text-xs text-slate-400">{{ bank.course?.department?.name }}</span>
                </div>

                <!-- Stats row -->
                <div class="flex items-center gap-3">
                    <!-- Questions count badge -->
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-semibold"
                        style="background: #fef2f2; color: #BC2739;">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        {{ bank.questions_count }} question{{ bank.questions_count !== 1 ? 's' : '' }}
                    </div>
                </div>

                <!-- Creator + date footer -->
                <div class="flex items-center justify-between text-xs text-slate-400 border-t border-slate-100 pt-3">
                    <div class="flex items-center gap-1.5">
                        <div class="w-5 h-5 rounded-full bg-slate-200 flex items-center justify-center">
                            <svg class="w-3 h-3 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <span>{{ bank.creator?.name }}</span>
                    </div>

                    <div class="flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span>{{ formatDate(bank.created_at) }}</span>
                    </div>
                </div>

                <!-- View Questions button -->
                <Link
                    :href="`/teacher/question-banks/${bank.id}`"
                    class="flex items-center justify-center gap-2 w-full py-2 rounded-xl text-sm font-semibold border-2 transition-all hover:text-white group"
                    style="border-color: #BC2739; color: #BC2739;"
                    onmouseover="this.style.background='#BC2739'"
                    onmouseout="this.style.background=''">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Questions
                </Link>
            </div>
        </div>

        <!-- ── Create Bank Modal (Teleport) ──────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0">

                <div
                    v-if="showModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                    style="background: rgba(15,23,42,0.5); backdrop-filter: blur(4px);"
                    @click="closeOnBackdrop">

                    <Transition
                        enter-active-class="transition-all duration-200"
                        enter-from-class="opacity-0 scale-95 translate-y-2"
                        enter-to-class="opacity-100 scale-100 translate-y-0"
                        leave-active-class="transition-all duration-150"
                        leave-from-class="opacity-100 scale-100 translate-y-0"
                        leave-to-class="opacity-0 scale-95 translate-y-2"
                        appear>

                        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col">

                            <!-- Modal header -->
                            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl flex items-center justify-center"
                                        style="background: #fef2f2;">
                                        <svg class="w-5 h-5" style="color: #BC2739;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <h2 class="font-bold text-slate-900 text-base">New Question Bank</h2>
                                        <p class="text-xs text-slate-400">Organise your questions by course</p>
                                    </div>
                                </div>

                                <button
                                    @click="closeModal"
                                    class="p-2 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Modal body -->
                            <div class="px-6 py-5 flex flex-col gap-5">

                                <!-- Title field -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Bank Title <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        placeholder="e.g. Data Structures Mid-Term Bank"
                                        :class="['w-full px-3.5 py-2.5 rounded-xl border text-sm text-slate-900 placeholder-slate-400 outline-none transition-all',
                                            errors.title
                                                ? 'border-red-400 ring-2 ring-red-100'
                                                : 'border-slate-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-100']"
                                        @keyup.enter="handleSubmit"
                                    />
                                    <p v-if="errors.title" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ errors.title }}
                                    </p>
                                </div>

                                <!-- Course selector -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Course <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <select
                                            v-model="form.course_id"
                                            :class="['w-full px-3.5 py-2.5 rounded-xl border text-sm outline-none transition-all appearance-none pr-9',
                                                form.course_id ? 'text-slate-900' : 'text-slate-400',
                                                errors.course_id
                                                    ? 'border-red-400 ring-2 ring-red-100'
                                                    : 'border-slate-200 focus:border-primary-500 focus:ring-2 focus:ring-primary-100']">
                                            <option value="" disabled>Select a course…</option>
                                            <option
                                                v-for="course in courses"
                                                :key="course.id"
                                                :value="course.id">
                                                {{ course.code }} — {{ course.title }}
                                            </option>
                                        </select>
                                        <div class="pointer-events-none absolute inset-y-0 right-3 flex items-center">
                                            <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <p v-if="errors.course_id" class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ errors.course_id }}
                                    </p>
                                </div>

                                <!-- Description textarea -->
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                                        Description
                                        <span class="text-slate-400 font-normal ml-1">(optional)</span>
                                    </label>
                                    <textarea
                                        v-model="form.description"
                                        rows="3"
                                        placeholder="Brief description of this question bank…"
                                        class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm text-slate-900 placeholder-slate-400 outline-none transition-all resize-none focus:border-primary-500 focus:ring-2 focus:ring-primary-100"/>
                                    <p v-if="errors.description" class="mt-1.5 text-xs text-red-500">{{ errors.description }}</p>
                                </div>
                            </div>

                            <!-- Modal footer -->
                            <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100">
                                <button
                                    @click="closeModal"
                                    :disabled="submitting"
                                    class="px-4 py-2.5 rounded-xl text-sm font-semibold text-slate-600 bg-slate-100 hover:bg-slate-200 transition-colors disabled:opacity-50">
                                    Cancel
                                </button>

                                <button
                                    @click="handleSubmit"
                                    :disabled="submitting"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all hover:opacity-90 active:scale-95 disabled:opacity-60 disabled:cursor-not-allowed"
                                    style="background: #BC2739;">
                                    <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    {{ submitting ? 'Creating…' : 'Create Bank' }}
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>
            </Transition>
        </Teleport>
    </AppLayout>
</template>
