<script setup>
import { computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    upcoming_exams: { type: Array, default: () => [] },
    active_exams:   { type: Array, default: () => [] },
    attempted_ids:  { type: Array, default: () => [] },
})

const attemptedSet = computed(() => new Set(props.attempted_ids))

function formatDate(dt) {
    if (!dt) return '—'
    return new Date(dt).toLocaleString('en-GB', {
        weekday: 'short', day: '2-digit', month: 'short',
        year: 'numeric', hour: '2-digit', minute: '2-digit',
    })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900 font-heading">My Exams</h1>
                <p class="text-sm text-slate-400 mt-0.5">Upcoming and active examinations</p>
            </div>
        </template>

        <!-- Live Exams Banner -->
        <div v-if="active_exams.length"
            class="mb-6 bg-emerald-50 border border-emerald-200 rounded-2xl p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center flex-shrink-0">
                    <span class="w-2.5 h-2.5 rounded-full bg-white animate-ping"></span>
                </div>
                <div>
                    <p class="font-bold text-emerald-900">Exams currently live!</p>
                    <p class="text-xs text-emerald-600">These exams are active right now.</p>
                </div>
            </div>
            <div class="space-y-3">
                <div v-for="exam in active_exams" :key="exam.id"
                    class="bg-white rounded-xl border border-emerald-200 p-4 flex items-center justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <p class="font-bold text-slate-900 truncate">{{ exam.title }}</p>
                        <p class="text-sm text-slate-400">{{ exam.course?.code }} · {{ exam.duration_minutes }} min</p>
                    </div>
                    <div v-if="attemptedSet.has(exam.id)"
                        class="px-3 py-1.5 rounded-xl text-sm font-medium bg-slate-100 text-slate-500">
                        Submitted
                    </div>
                    <Link v-else
                        :href="`/student/exams/${exam.id}/attempt`"
                        class="px-4 py-2 rounded-xl text-sm font-bold text-white bg-emerald-600 hover:bg-emerald-700 transition-colors flex-shrink-0">
                        Start Exam →
                    </Link>
                </div>
            </div>
        </div>

        <!-- Upcoming Exams -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <h2 class="font-bold text-slate-900">Scheduled Exams</h2>
            </div>

            <div v-if="!upcoming_exams.length" class="py-16 text-center">
                <div class="w-14 h-14 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="text-slate-500 font-medium">No upcoming exams</p>
                <p class="text-sm text-slate-400 mt-1">Check back later for scheduled examinations.</p>
            </div>

            <div v-else class="divide-y divide-slate-100">
                <div v-for="exam in upcoming_exams" :key="exam.id"
                    class="flex items-center gap-4 px-5 py-4 hover:bg-slate-50 transition-colors">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-semibold text-slate-900 truncate">{{ exam.title }}</p>
                        <p class="text-sm text-slate-400 mt-0.5">{{ exam.course?.code }} · {{ exam.duration_minutes }} min · {{ exam.total_marks }} marks</p>
                        <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ formatDate(exam.start_time) }}
                        </p>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 flex-shrink-0">Scheduled</span>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
