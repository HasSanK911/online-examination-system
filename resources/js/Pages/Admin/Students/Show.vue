<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center gap-3">
        <button @click="router.get('/admin/students')"
          class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
        </button>
        <div>
          <h1 class="text-xl font-bold text-slate-900 font-heading">{{ student.user?.name }}</h1>
          <p class="text-sm text-slate-400 mt-0.5">{{ student.student_id }} · {{ student.department?.name }}</p>
        </div>
      </div>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

      <!-- ── Left column: profile card ─────────────────────────────────── -->
      <div class="space-y-5">

        <!-- Avatar + identity -->
        <div class="bg-white rounded-2xl border border-slate-200 p-6 text-center">
          <div class="w-20 h-20 rounded-full mx-auto mb-4 flex items-center justify-center text-white text-3xl font-black"
            :style="{ background: avatarGradient(student.user?.name) }">
            {{ student.user?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <h2 class="text-lg font-bold text-slate-900 font-heading">{{ student.user?.name }}</h2>
          <p class="text-sm text-slate-400 mt-0.5">{{ student.user?.email }}</p>

          <span :class="[
            'inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold mt-3',
            student.status === 'active'    ? 'bg-emerald-50 text-emerald-700' :
            student.status === 'graduated' ? 'bg-sky-50 text-sky-700' :
                                             'bg-slate-100 text-slate-500'
          ]">
            <span class="w-1.5 h-1.5 rounded-full"
              :class="student.status === 'active' ? 'bg-emerald-500' : student.status === 'graduated' ? 'bg-sky-500' : 'bg-slate-400'">
            </span>
            {{ student.status?.charAt(0).toUpperCase() + student.status?.slice(1) }}
          </span>
        </div>

        <!-- Details -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-4">
          <h3 class="text-sm font-bold text-slate-900">Academic Info</h3>
          <dl class="space-y-3 text-sm">
            <div class="flex justify-between">
              <dt class="text-slate-400">Student ID</dt>
              <dd class="font-mono font-semibold text-slate-800 text-xs bg-slate-100 px-2 py-0.5 rounded">{{ student.student_id }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-400">Roll Number</dt>
              <dd class="font-mono font-semibold text-slate-800 text-xs bg-slate-100 px-2 py-0.5 rounded">{{ student.roll_number }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-400">Department</dt>
              <dd class="font-semibold text-slate-800 text-right max-w-[55%]">{{ student.department?.name }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-400">Faculty</dt>
              <dd class="font-semibold text-slate-800 text-right max-w-[55%]">{{ student.department?.faculty?.name }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-400">Semester</dt>
              <dd>
                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-primary-100 text-primary-700 text-xs font-bold">
                  {{ student.semester }}
                </span>
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-slate-400">Batch</dt>
              <dd class="font-semibold text-slate-800">{{ student.batch || '—' }}</dd>
            </div>
          </dl>
        </div>

        <!-- Contact -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 space-y-3">
          <h3 class="text-sm font-bold text-slate-900">Contact</h3>
          <div class="flex items-start gap-2.5 text-sm">
            <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="text-slate-700 break-all">{{ student.user?.email }}</span>
          </div>
          <div v-if="student.phone" class="flex items-start gap-2.5 text-sm">
            <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
            </svg>
            <span class="text-slate-700">{{ student.phone }}</span>
          </div>
          <div v-if="student.address" class="flex items-start gap-2.5 text-sm">
            <svg class="w-4 h-4 text-slate-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-slate-700">{{ student.address }}</span>
          </div>
          <p v-if="!student.phone && !student.address" class="text-xs text-slate-400">No contact info added.</p>
        </div>
      </div>

      <!-- ── Right column: courses + results ───────────────────────────── -->
      <div class="lg:col-span-2 space-y-5">

        <!-- Result summary cards -->
        <div class="grid grid-cols-3 gap-4" v-if="publishedResults.length">
          <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-slate-900">{{ publishedResults.length }}</p>
            <p class="text-xs text-slate-400 mt-0.5">Exams Taken</p>
          </div>
          <div class="bg-emerald-50 rounded-2xl border border-emerald-200 p-4 text-center">
            <p class="text-2xl font-bold text-emerald-700">{{ passedCount }}</p>
            <p class="text-xs text-emerald-500 mt-0.5">Passed</p>
          </div>
          <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
            <p class="text-2xl font-bold text-primary-600">{{ avgPercentage }}%</p>
            <p class="text-xs text-slate-400 mt-0.5">Avg Score</p>
          </div>
        </div>

        <!-- Enrolled courses -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
            <div class="w-8 h-8 bg-sky-50 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Enrolled Courses</h3>
            <span class="ml-auto text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-semibold">{{ student.courses?.length ?? 0 }}</span>
          </div>

          <div v-if="!student.courses?.length" class="py-8 text-center">
            <p class="text-sm text-slate-400">Not enrolled in any courses yet.</p>
          </div>
          <div v-else class="divide-y divide-slate-100">
            <div v-for="course in student.courses" :key="course.id"
              class="flex items-center gap-3 px-5 py-3.5 hover:bg-slate-50 transition-colors">
              <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-bold text-sky-600">{{ course.code?.substring(0, 2) }}</span>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-900 truncate">{{ course.title }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ course.code }}</p>
              </div>
              <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">{{ course.credit_hours }} cr</span>
            </div>
          </div>
        </div>

        <!-- Exam results history -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
          <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
            <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
              <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
              </svg>
            </div>
            <h3 class="text-sm font-bold text-slate-900">Exam Results</h3>
            <span class="ml-auto text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full font-semibold">{{ publishedResults.length }}</span>
          </div>

          <div v-if="!publishedResults.length" class="py-8 text-center">
            <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-sm text-slate-400">No published results yet.</p>
          </div>

          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                  <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Exam</th>
                  <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Course</th>
                  <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Marks</th>
                  <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Score</th>
                  <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Grade</th>
                  <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Result</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-100">
                <tr v-for="result in publishedResults" :key="result.id"
                  class="hover:bg-slate-50 transition-colors">
                  <td class="px-5 py-3.5">
                    <p class="font-semibold text-slate-900 text-sm">{{ result.exam?.title }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">{{ formatDate(result.published_at) }}</p>
                  </td>
                  <td class="px-5 py-3.5 text-slate-500 hidden sm:table-cell">{{ result.exam?.course?.title }}</td>
                  <td class="px-5 py-3.5 text-center font-semibold text-slate-800">
                    {{ result.obtained_marks }}<span class="text-slate-400 font-normal">/{{ result.total_marks }}</span>
                  </td>
                  <td class="px-5 py-3.5 text-center">
                    <div class="flex flex-col items-center gap-1">
                      <div class="w-16 bg-slate-200 rounded-full h-1.5">
                        <div class="h-1.5 rounded-full"
                          :class="Number(result.percentage) >= 60 ? 'bg-emerald-500' : 'bg-red-400'"
                          :style="`width:${result.percentage}%`">
                        </div>
                      </div>
                      <span class="text-xs font-semibold text-slate-700">{{ Number(result.percentage).toFixed(1) }}%</span>
                    </div>
                  </td>
                  <td class="px-5 py-3.5 text-center">
                    <span :class="['inline-flex items-center justify-center w-10 h-7 rounded-lg text-xs font-bold', gradeClass(result.grade)]">
                      {{ result.grade }}
                    </span>
                  </td>
                  <td class="px-5 py-3.5 text-center">
                    <span :class="[
                      'inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold',
                      result.is_pass ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600'
                    ]">
                      {{ result.is_pass ? 'Pass' : 'Fail' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  student: { type: Object, required: true },
})

const publishedResults = computed(() =>
  (props.student.results ?? []).filter(r => r.published_at)
)

const passedCount = computed(() =>
  publishedResults.value.filter(r => r.is_pass).length
)

const avgPercentage = computed(() => {
  if (!publishedResults.value.length) return 0
  const avg = publishedResults.value.reduce((s, r) => s + Number(r.percentage), 0) / publishedResults.value.length
  return avg.toFixed(1)
})

const gradients = [
  'linear-gradient(135deg,#BC2739,#e05a6b)',
  'linear-gradient(135deg,#0ea5e9,#38bdf8)',
  'linear-gradient(135deg,#10b981,#34d399)',
  'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#8b5cf6,#a78bfa)',
]
function avatarGradient(name) { return gradients[(name?.charCodeAt(0) ?? 0) % gradients.length] }

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
function gradeClass(g) { return gradeColors[g] ?? 'bg-slate-100 text-slate-600' }

function formatDate(d) {
  if (!d) return ''
  return new Date(d).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' })
}
</script>
