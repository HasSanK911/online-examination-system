<template>
  <AppLayout>
    <template #header>
      <div>
        <h1 class="text-xl font-bold text-slate-900 font-heading">Student Dashboard</h1>
        <p class="text-sm text-slate-400 mt-0.5">Welcome back, {{ $page.props.auth.user?.name }}</p>
      </div>
    </template>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-slate-900">{{ stats.total_exams }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Exams Taken</p>
        </div>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-emerald-600">{{ stats.passed }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Passed</p>
        </div>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-sky-600">{{ stats.avg_score }}%</p>
          <p class="text-xs text-slate-400 mt-0.5">Avg Score</p>
        </div>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-amber-600">{{ stats.rank }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Class Rank</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Upcoming Exams -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Upcoming Exams</h3>
          </div>
          <Link href="/student/exams" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View all</Link>
        </div>
        <div v-if="!upcomingExams.length" class="py-10 text-center">
          <p class="text-sm text-slate-400">No upcoming exams scheduled.</p>
        </div>
        <div v-else class="divide-y divide-slate-100">
          <div v-for="exam in upcomingExams" :key="exam.id"
            class="flex items-start gap-3 px-5 py-4 hover:bg-slate-50 transition-colors">
            <div :class="['w-2 h-2 rounded-full mt-1.5 flex-shrink-0', exam.status === 'active' ? 'bg-emerald-500' : 'bg-blue-500']"></div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-slate-900 text-sm truncate">{{ exam.title }}</p>
              <p class="text-xs text-slate-400 mt-0.5">{{ exam.course?.code }} · {{ exam.duration_minutes }} min</p>
              <p v-if="exam.start_time" class="text-xs text-slate-400 mt-0.5">
                {{ new Date(exam.start_time).toLocaleString('en-GB') }}
              </p>
            </div>
            <span :class="['text-xs font-semibold px-2 py-0.5 rounded-full', exam.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700']">
              {{ exam.status === 'active' ? 'Live' : 'Scheduled' }}
            </span>
          </div>
        </div>
      </div>

      <!-- Recent Results -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-emerald-50 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Recent Results</h3>
          </div>
          <Link href="/student/results" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View all</Link>
        </div>
        <div v-if="!recentResults.length" class="py-10 text-center">
          <p class="text-sm text-slate-400">No results published yet.</p>
        </div>
        <div v-else class="divide-y divide-slate-100">
          <div v-for="result in recentResults" :key="result.id"
            class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-slate-900 text-sm truncate">{{ result.exam?.title }}</p>
              <p class="text-xs text-slate-400">{{ result.exam?.course?.code }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold" :class="Number(result.percentage) >= 60 ? 'text-emerald-600' : 'text-red-500'">
                {{ Number(result.percentage).toFixed(1) }}%
              </p>
              <span :class="['text-xs font-semibold px-1.5 py-0.5 rounded', gradeColors[result.grade] ?? 'bg-slate-100 text-slate-600']">
                {{ result.grade }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

defineProps({
  student:        { type: Object, default: null },
  upcomingExams:  { type: Array,  default: () => [] },
  recentResults:  { type: Array,  default: () => [] },
  stats:          { type: Object, default: () => ({ total_exams: 0, passed: 0, avg_score: 0, rank: '—' }) },
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
