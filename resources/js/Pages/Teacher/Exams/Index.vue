<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import { Link, router } from '@inertiajs/vue3'
import { ref, computed } from 'vue'

const props = defineProps({
  exams: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({}),
  },
})

const activeStatus = ref(props.filters.status || 'all')

const tabs = [
  { key: 'all',       label: 'All' },
  { key: 'draft',     label: 'Draft' },
  { key: 'scheduled', label: 'Scheduled' },
  { key: 'active',    label: 'Live' },
  { key: 'completed', label: 'Completed' },
  { key: 'cancelled', label: 'Cancelled' },
]

const statusConfig = {
  draft: {
    dot: 'bg-slate-400',
    badge: 'bg-slate-100 text-slate-600 border-slate-200',
    label: 'Draft',
  },
  scheduled: {
    dot: 'bg-blue-500',
    badge: 'bg-blue-50 text-blue-700 border-blue-200',
    label: 'Scheduled',
  },
  active: {
    dot: 'bg-emerald-500',
    badge: 'bg-emerald-50 text-emerald-700 border-emerald-200',
    label: 'Live',
  },
  completed: {
    dot: 'bg-primary-600',
    badge: 'bg-red-50 text-primary-700 border-primary-200',
    label: 'Completed',
  },
  cancelled: {
    dot: 'bg-red-500',
    badge: 'bg-red-50 text-red-700 border-red-200',
    label: 'Cancelled',
  },
}

function getStatusConfig(status) {
  return statusConfig[status] ?? {
    dot: 'bg-slate-400',
    badge: 'bg-slate-100 text-slate-600 border-slate-200',
    label: status,
  }
}

function filterByStatus(key) {
  activeStatus.value = key
  router.get(
    '/teacher/exams',
    key === 'all' ? {} : { status: key },
    { preserveState: true, replace: true }
  )
}

function formatDate(dateStr) {
  if (!dateStr) return '—'
  const d = new Date(dateStr)
  return d.toLocaleDateString('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  })
}

function deleteExam(id) {
  if (!confirm('Are you sure you want to delete this exam? This action cannot be undone.')) return
  router.delete(`/teacher/exams/${id}`, {
    preserveScroll: true,
  })
}

const totalCount = computed(() => props.exams.total ?? props.exams.data.length)
</script>

<template>
  <AppLayout>
    <!-- Header slot -->
    <template #header>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-800">My Exams</h1>
          <p class="mt-0.5 text-sm text-slate-500">
            {{ totalCount }} exam{{ totalCount !== 1 ? 's' : '' }} total
          </p>
        </div>
        <Link
          href="/teacher/exams/create"
          class="inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Create Exam
        </Link>
      </div>
    </template>

    <!-- Page body -->
    <div class="py-6 px-4 sm:px-6 lg:px-8">

      <!-- Status tabs -->
      <div class="mb-6 border-b border-slate-200">
        <nav class="-mb-px flex gap-1 overflow-x-auto" aria-label="Exam status filter">
          <button
            v-for="tab in tabs"
            :key="tab.key"
            type="button"
            @click="filterByStatus(tab.key)"
            :class="[
              'whitespace-nowrap px-4 py-2.5 text-sm font-medium transition border-b-2 focus:outline-none',
              activeStatus === tab.key
                ? 'border-primary-600 text-primary-600'
                : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300',
            ]"
          >
            {{ tab.label }}
          </button>
        </nav>
      </div>

      <!-- Exam cards grid -->
      <div v-if="exams.data.length > 0" class="grid grid-cols-1 gap-5 lg:grid-cols-2">
        <div
          v-for="exam in exams.data"
          :key="exam.id"
          class="flex flex-col rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:shadow-md"
        >
          <!-- Card top bar -->
          <div class="flex items-start justify-between gap-3 px-5 pt-5">
            <!-- Status badge -->
            <span
              :class="[
                'inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium',
                getStatusConfig(exam.status).badge,
              ]"
            >
              <span
                :class="[
                  'h-1.5 w-1.5 rounded-full',
                  getStatusConfig(exam.status).dot,
                ]"
              />
              {{ getStatusConfig(exam.status).label }}
            </span>

            <!-- Course code tag -->
            <span class="rounded-md bg-slate-100 px-2.5 py-0.5 text-xs font-mono font-semibold text-slate-600">
              {{ exam.course?.code ?? '—' }}
            </span>
          </div>

          <!-- Card body -->
          <div class="flex flex-1 flex-col px-5 pt-3 pb-4">
            <!-- Title -->
            <h2 class="text-base font-bold text-slate-800 leading-snug">
              {{ exam.title }}
            </h2>

            <!-- Course + department -->
            <p class="mt-0.5 text-sm text-slate-400">
              {{ exam.course?.title ?? '—' }}
              <template v-if="exam.course?.department?.name">
                &middot; {{ exam.course.department.name }}
              </template>
            </p>

            <!-- Info chips row -->
            <div class="mt-4 flex flex-wrap gap-2">
              <!-- Total marks -->
              <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1 text-xs text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                {{ exam.total_marks }} marks
              </span>

              <!-- Duration -->
              <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1 text-xs text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                </svg>
                {{ exam.duration_minutes }} min
              </span>

              <!-- Questions -->
              <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1 text-xs text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                </svg>
                {{ exam.questions_count }} Qs
              </span>

              <!-- Attempts -->
              <span class="inline-flex items-center gap-1 rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1 text-xs text-slate-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v1h8v-1zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-1a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v1h-3zM4.75 12.094A5.973 5.973 0 004 15v1H1v-1a3 3 0 013.75-2.906z" />
                </svg>
                {{ exam.attempts_count }} attempts
              </span>
            </div>

            <!-- Schedule line -->
            <div class="mt-4 flex items-center gap-1.5 text-xs text-slate-500">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
              </svg>
              <span>{{ formatDate(exam.start_time) }}</span>
              <span class="text-slate-300">&rarr;</span>
              <span>{{ formatDate(exam.end_time) }}</span>
            </div>
          </div>

          <!-- Card action row -->
          <div class="flex items-center gap-2 border-t border-slate-100 px-5 py-3">
            <!-- Manage Questions -->
            <Link
              :href="`/teacher/exams/${exam.id}/questions`"
              class="inline-flex flex-1 items-center justify-center gap-1.5 rounded-lg bg-primary-600 px-3 py-2 text-xs font-semibold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-1"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
              </svg>
              Manage Questions
            </Link>

            <!-- View icon btn -->
            <Link
              :href="`/teacher/exams/${exam.id}`"
              class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300"
              title="View exam"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd" />
              </svg>
            </Link>

            <!-- Edit icon btn -->
            <Link
              :href="`/teacher/exams/${exam.id}/edit`"
              class="inline-flex items-center justify-center rounded-lg border border-slate-200 bg-white p-2 text-slate-500 transition hover:border-slate-300 hover:bg-slate-50 hover:text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-300"
              title="Edit exam"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
              </svg>
            </Link>

            <!-- Delete icon btn -->
            <button
              type="button"
              @click="deleteExam(exam.id)"
              class="inline-flex items-center justify-center rounded-lg border border-red-200 bg-white p-2 text-red-400 transition hover:border-red-300 hover:bg-red-50 hover:text-red-600 focus:outline-none focus:ring-2 focus:ring-red-300"
              title="Delete exam"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Empty state -->
      <div
        v-else
        class="flex flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-white py-20 text-center"
      >
        <div class="rounded-full bg-slate-100 p-5">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
        </div>
        <h3 class="mt-4 text-base font-semibold text-slate-700">No exams found</h3>
        <p class="mt-1 text-sm text-slate-400">
          <template v-if="activeStatus !== 'all'">
            No {{ activeStatus }} exams. Try a different filter or create a new one.
          </template>
          <template v-else>
            You haven't created any exams yet. Start by creating your first exam.
          </template>
        </p>
        <Link
          href="/teacher/exams/create"
          class="mt-5 inline-flex items-center gap-2 rounded-lg bg-primary-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-2"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
          </svg>
          Create your first exam
        </Link>
      </div>

      <!-- Pagination -->
      <div
        v-if="exams.links && exams.links.length > 3"
        class="mt-8 flex flex-wrap items-center justify-center gap-1"
      >
        <template v-for="link in exams.links" :key="link.label">
          <Link
            v-if="link.url"
            :href="link.url"
            preserve-state
            :class="[
              'inline-flex min-w-[2.25rem] items-center justify-center rounded-lg border px-3 py-2 text-sm font-medium transition focus:outline-none focus:ring-2 focus:ring-primary-600 focus:ring-offset-1',
              link.active
                ? 'border-primary-600 bg-primary-600 text-white shadow-sm'
                : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300 hover:bg-slate-50',
            ]"
            v-html="link.label"
          />
          <span
            v-else
            :class="[
              'inline-flex min-w-[2.25rem] items-center justify-center rounded-lg border border-slate-200 px-3 py-2 text-sm font-medium',
              link.active
                ? 'border-primary-600 bg-primary-600 text-white'
                : 'cursor-not-allowed bg-white text-slate-300',
            ]"
            v-html="link.label"
          />
        </template>
      </div>

    </div>
  </AppLayout>
</template>
