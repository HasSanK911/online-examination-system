<template>
  <AppLayout>
    <template #header>
      <div>
        <h1 class="text-xl font-bold text-slate-900 font-heading">Analytics</h1>
        <p class="text-sm text-slate-500 mt-0.5">OLAP performance insights across the institution</p>
      </div>
    </template>

    <!-- Overview stat cards -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
        <div class="w-9 h-9 bg-primary-50 rounded-xl flex items-center justify-center mx-auto mb-2">
          <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
        </div>
        <p class="text-2xl font-bold text-slate-900">{{ stats.total_results ?? 0 }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total Results</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
        <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-2">
          <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <p class="text-2xl font-bold text-emerald-600">{{ stats.pass_rate ?? 0 }}%</p>
        <p class="text-xs text-slate-400 mt-0.5">Pass Rate</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
        <div class="w-9 h-9 bg-sky-50 rounded-xl flex items-center justify-center mx-auto mb-2">
          <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
          </svg>
        </div>
        <p class="text-2xl font-bold text-sky-600">{{ stats.overall_avg ?? 0 }}%</p>
        <p class="text-xs text-slate-400 mt-0.5">Average Score</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
        <div class="w-9 h-9 bg-amber-50 rounded-xl flex items-center justify-center mx-auto mb-2">
          <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3l14 9-14 9V3z"/>
          </svg>
        </div>
        <p class="text-2xl font-bold text-amber-600">{{ stats.highest ?? 0 }}%</p>
        <p class="text-xs text-slate-400 mt-0.5">Highest Score</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4 text-center">
        <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center mx-auto mb-2">
          <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
          </svg>
        </div>
        <p class="text-2xl font-bold text-red-500">{{ stats.lowest ?? 0 }}%</p>
        <p class="text-xs text-slate-400 mt-0.5">Lowest Score</p>
      </div>
    </div>

    <!-- Charts row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 mb-5">
      <!-- Monthly Trend -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-center gap-2.5 mb-4">
          <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4"/>
            </svg>
          </div>
          <h3 class="text-sm font-bold text-slate-900">Monthly Performance Trend</h3>
        </div>
        <div v-if="!monthly_trend?.length" class="h-48 flex items-center justify-center">
          <p class="text-sm text-slate-400">No trend data available yet.</p>
        </div>
        <canvas v-else ref="trendChart" height="200"></canvas>
      </div>

      <!-- Grade Distribution -->
      <div class="bg-white rounded-2xl border border-slate-200 p-5">
        <div class="flex items-center gap-2.5 mb-4">
          <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
          </div>
          <h3 class="text-sm font-bold text-slate-900">Grade Distribution</h3>
        </div>
        <div v-if="!grade_distribution?.length" class="h-48 flex items-center justify-center">
          <p class="text-sm text-slate-400">No grade data available yet.</p>
        </div>
        <canvas v-else ref="gradeChart" height="200"></canvas>
      </div>
    </div>

    <!-- Department Performance table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-5">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Department Performance</h3>
      </div>
      <div v-if="!department_performance?.length" class="py-10 text-center">
        <p class="text-sm text-slate-400">No department data yet.</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Department</th>
              <th class="text-left py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Faculty</th>
              <th class="text-right py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Students</th>
              <th class="text-right py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Avg Score</th>
              <th class="text-right py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Pass Rate</th>
              <th class="py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider w-36">Progress</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="dept in department_performance" :key="dept.department_id"
                class="hover:bg-slate-50 transition-colors">
              <td class="py-3.5 px-5 font-semibold text-slate-900">{{ dept.department }}</td>
              <td class="py-3.5 px-5 text-slate-500 hidden sm:table-cell">{{ dept.faculty }}</td>
              <td class="py-3.5 px-5 text-right text-slate-700 font-medium">{{ dept.total_students }}</td>
              <td class="py-3.5 px-5 text-right font-bold" :class="scoreColor(dept.avg_percentage)">
                {{ Number(dept.avg_percentage).toFixed(1) }}%
              </td>
              <td class="py-3.5 px-5 text-right font-bold" :class="scoreColor(dept.pass_rate)">
                {{ dept.pass_rate }}%
              </td>
              <td class="py-3.5 px-5">
                <div class="w-full bg-slate-100 rounded-full h-2">
                  <div class="h-2 rounded-full transition-all"
                    :class="dept.pass_rate >= 75 ? 'bg-emerald-500' : dept.pass_rate >= 50 ? 'bg-amber-400' : 'bg-red-400'"
                    :style="`width: ${Math.min(dept.pass_rate,100)}%`">
                  </div>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Top Students table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
        <div class="w-8 h-8 bg-amber-50 rounded-lg flex items-center justify-center">
          <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
          </svg>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Top Students Per Course</h3>
      </div>
      <div v-if="!top_students?.length" class="py-10 text-center">
        <p class="text-sm text-slate-400">No results published yet.</p>
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-sm">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-100">
              <th class="text-left py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Rank</th>
              <th class="text-left py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Student</th>
              <th class="text-left py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Course</th>
              <th class="text-right py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Score</th>
              <th class="text-center py-3 px-5 text-xs font-semibold text-slate-500 uppercase tracking-wider">Grade</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="s in top_students" :key="`${s.student_code}-${s.course_code}`"
                class="hover:bg-slate-50 transition-colors">
              <td class="py-3.5 px-5">
                <span v-if="s.course_rank === 1" class="text-lg">🥇</span>
                <span v-else-if="s.course_rank === 2" class="text-lg">🥈</span>
                <span v-else-if="s.course_rank === 3" class="text-lg">🥉</span>
                <span v-else
                  class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                  #{{ s.course_rank }}
                </span>
              </td>
              <td class="py-3.5 px-5">
                <p class="font-semibold text-slate-900">{{ s.student_name }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ s.student_code }}</p>
              </td>
              <td class="py-3.5 px-5">
                <p class="text-slate-700">{{ s.course }}</p>
                <p class="text-xs text-slate-400 font-mono">{{ s.course_code }}</p>
              </td>
              <td class="py-3.5 px-5 text-right font-bold" :class="scoreColor(s.percentage)">
                {{ s.percentage }}%
              </td>
              <td class="py-3.5 px-5 text-center">
                <span :class="['inline-flex items-center justify-center w-10 h-7 rounded-lg text-xs font-bold', gradeClass(s.grade)]">
                  {{ s.grade }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
  stats:                 Object,
  monthly_trend:         Array,
  grade_distribution:    Array,
  department_performance:Array,
  course_performance:    Array,
  semester_matrix:       Array,
  top_students:          Array,
})

const trendChart = ref(null)
const gradeChart = ref(null)

function scoreColor(val) {
  const v = Number(val)
  if (v >= 75) return 'text-emerald-600'
  if (v >= 50) return 'text-amber-600'
  return 'text-red-500'
}

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

onMounted(async () => {
  const { Chart, registerables } = await import('chart.js')
  Chart.register(...registerables)

  // Monthly Trend — Line chart
  if (trendChart.value && props.monthly_trend?.length) {
    new Chart(trendChart.value, {
      type: 'line',
      data: {
        labels: props.monthly_trend.map(r => r.label),
        datasets: [
          {
            label: 'Avg Score',
            data: props.monthly_trend.map(r => r.avg_score),
            borderColor: '#BC2739',
            backgroundColor: 'rgba(188,39,57,0.08)',
            tension: 0.4,
            fill: true,
            pointRadius: 4,
            pointBackgroundColor: '#BC2739',
          },
          {
            label: 'Pass %',
            data: props.monthly_trend.map(r => r.passed && r.total ? Math.round(r.passed / r.total * 100) : 0),
            borderColor: '#10b981',
            backgroundColor: 'transparent',
            tension: 0.4,
            borderDash: [4, 4],
            pointRadius: 3,
          },
        ],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top', labels: { font: { family: 'Poppins' }, color: '#64748b' } },
        },
        scales: {
          y: { min: 0, max: 100, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8' } },
          x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
        },
      },
    })
  }

  // Grade Distribution — Bar chart
  if (gradeChart.value && props.grade_distribution?.length) {
    const barColors = {
      'A+': '#059669', 'A': '#10b981',
      'B+': '#0ea5e9', 'B': '#38bdf8',
      'C+': '#f59e0b', 'C': '#fbbf24',
      'D':  '#f97316', 'F': '#ef4444',
    }
    new Chart(gradeChart.value, {
      type: 'bar',
      data: {
        labels: props.grade_distribution.map(r => r.grade),
        datasets: [{
          label: 'Students',
          data: props.grade_distribution.map(r => r.count),
          backgroundColor: props.grade_distribution.map(r => barColors[r.grade] || '#94a3b8'),
          borderRadius: 8,
          borderSkipped: false,
        }],
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
        },
        scales: {
          y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', stepSize: 1 } },
          x: { grid: { display: false }, ticks: { color: '#94a3b8' } },
        },
      },
    })
  }
})
</script>
