<template>
  <AppLayout>
    <template #header>
      <div>
        <h1 class="text-xl font-bold text-slate-900 font-heading">Teacher Dashboard</h1>
        <p class="text-sm text-slate-400 mt-0.5">Welcome back, {{ $page.props.auth.user?.name }}</p>
      </div>
    </template>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
      <div v-for="stat in stats" :key="stat.label"
        class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4">
        <div :class="['w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0', stat.iconBg]">
          <svg class="w-5 h-5" :class="stat.iconColor" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stat.icon"/>
          </svg>
        </div>
        <div>
          <p class="text-2xl font-bold text-slate-900">{{ stat.value }}</p>
          <p class="text-xs text-slate-400 mt-0.5">{{ stat.label }}</p>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- My Courses -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">My Courses</h3>
          </div>
          <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">{{ courses.length }}</span>
        </div>
        <div v-if="!courses.length" class="py-10 text-center text-sm text-slate-400">No courses assigned yet.</div>
        <div v-else class="divide-y divide-slate-100">
          <div v-for="course in courses" :key="course.id"
            class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors">
            <div class="w-9 h-9 rounded-xl bg-sky-50 flex items-center justify-center flex-shrink-0">
              <span class="text-xs font-bold text-sky-600">{{ course.code?.substring(0,2) }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-slate-900 text-sm truncate">{{ course.title }}</p>
              <p class="text-xs text-slate-400">{{ course.department?.name }} · {{ course.code }}</p>
            </div>
            <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-medium">
              {{ course.students_count }} students
            </span>
          </div>
        </div>
      </div>

      <!-- Recent Exams -->
      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
          <div class="flex items-center gap-2.5">
            <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">My Exams</h3>
          </div>
          <Link href="/teacher/exams" class="text-xs text-primary-600 hover:text-primary-700 font-medium">View all</Link>
        </div>
        <div v-if="!recentExams.length" class="py-10 text-center text-sm text-slate-400">No exams created yet.</div>
        <div v-else class="divide-y divide-slate-100">
          <div v-for="exam in recentExams" :key="exam.id"
            class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors">
            <div class="flex-1 min-w-0">
              <p class="font-semibold text-slate-900 text-sm truncate">{{ exam.title }}</p>
              <p class="text-xs text-slate-400">{{ exam.course?.code }} · {{ exam.attempts_count }} attempts</p>
            </div>
            <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold', statusCls(exam.status)]">
              <span class="w-1.5 h-1.5 rounded-full" :class="statusDot(exam.status)"></span>
              {{ exam.status }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Pending evaluations banner -->
    <div v-if="pendingEvaluations > 0"
      class="mt-5 bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center gap-4">
      <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <div class="flex-1">
        <p class="text-sm font-semibold text-amber-800">{{ pendingEvaluations }} exam{{ pendingEvaluations > 1 ? 's' : '' }} pending evaluation</p>
        <p class="text-xs text-amber-600 mt-0.5">Descriptive/short answers require manual review.</p>
      </div>
      <Link href="/teacher/exams" class="btn-primary text-xs px-3 py-2">Review Now</Link>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  courses: { type: Array, default: () => [] },
  recentExams: { type: Array, default: () => [] },
  pendingEvaluations: { type: Number, default: 0 },
  totalStudents: { type: Number, default: 0 },
  publishedResults: { type: Number, default: 0 },
  totalExams: { type: Number, default: 0 },
})

const stats = computed(() => [
  { label: 'My Courses',    value: props.courses.length,      icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', iconBg: 'bg-sky-50',     iconColor: 'text-sky-600'     },
  { label: 'My Students',   value: props.totalStudents,       icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', iconBg: 'bg-emerald-50', iconColor: 'text-emerald-600' },
  { label: 'Total Exams',   value: props.totalExams,          icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', iconBg: 'bg-primary-50',  iconColor: 'text-primary-600' },
  { label: 'Results Published', value: props.publishedResults, icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', iconBg: 'bg-amber-50',  iconColor: 'text-amber-600'  },
])

const statusMap = {
  draft:     { cls: 'bg-slate-100 text-slate-600',    dot: 'bg-slate-400'   },
  scheduled: { cls: 'bg-blue-50 text-blue-700',       dot: 'bg-blue-500'    },
  active:    { cls: 'bg-emerald-50 text-emerald-700', dot: 'bg-emerald-500' },
  completed: { cls: 'bg-primary-50 text-primary-700', dot: 'bg-primary-500' },
  cancelled: { cls: 'bg-red-50 text-red-600',         dot: 'bg-red-400'     },
}
function statusCls(s) { return (statusMap[s] ?? statusMap.draft).cls }
function statusDot(s) { return (statusMap[s] ?? statusMap.draft).dot }
</script>
