<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center justify-between gap-4 flex-wrap">
        <!-- Left: back + title + badge -->
        <div class="flex items-center gap-3 min-w-0">
          <Link
            href="/teacher/exams"
            class="flex items-center justify-center w-9 h-9 rounded-xl border border-slate-200 bg-white text-slate-500 hover:bg-slate-50 hover:text-slate-700 transition-colors flex-shrink-0"
            aria-label="Back to exams"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </Link>

          <div class="min-w-0">
            <div class="flex items-center gap-2 flex-wrap">
              <h1 class="text-xl font-bold text-slate-900 truncate">{{ exam.title }}</h1>
              <span
                :class="[
                  'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-semibold flex-shrink-0',
                  statusBadgeClass(exam.status),
                ]"
              >
                <span class="w-1.5 h-1.5 rounded-full" :class="statusDotClass(exam.status)"></span>
                {{ statusLabel(exam.status) }}
              </span>
            </div>
            <p class="text-sm text-slate-400 mt-0.5 truncate">
              {{ exam.course.code }} &mdash; {{ exam.course.title }}
            </p>
          </div>
        </div>

        <!-- Right: action buttons -->
        <div class="flex items-center gap-2 flex-shrink-0">
          <Link
            :href="`/teacher/exams/${exam.id}/edit`"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl border border-slate-200 bg-white text-sm font-medium text-slate-700 hover:bg-slate-50 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit
          </Link>

          <button
            v-if="exam.status === 'draft'"
            :disabled="publishing"
            @click="publishExam"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 disabled:cursor-not-allowed transition-colors"
          >
            <svg
              v-if="publishing"
              class="w-4 h-4 animate-spin"
              fill="none"
              viewBox="0 0 24 24"
            >
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
            </svg>
            <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            {{ publishing ? 'Publishing...' : 'Publish' }}
          </button>

          <Link
            :href="`/teacher/exams/${exam.id}/questions`"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
            style="background-color: #dc143c;"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
            </svg>
            Manage Questions
          </Link>
        </div>
      </div>
    </template>

    <div class="max-w-screen-xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">

      <!-- Stats row -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Questions -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Questions</span>
            <div
              class="w-8 h-8 rounded-xl flex items-center justify-center"
              style="background-color: #fde8ec;"
            >
              <svg class="w-4 h-4" style="color: #dc143c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900">{{ exam.questions.length }}</p>
          <p class="text-xs text-slate-400 mt-1">total questions</p>
        </div>

        <!-- Total Marks -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Total Marks</span>
            <div class="w-8 h-8 rounded-xl bg-violet-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900">{{ exam.total_marks }}</p>
          <p class="text-xs text-slate-400 mt-1">passing: {{ exam.passing_marks }}</p>
        </div>

        <!-- Duration -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Duration</span>
            <div class="w-8 h-8 rounded-xl bg-amber-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900">
            {{ exam.duration_minutes }}<span class="text-base font-medium text-slate-400 ml-1">min</span>
          </p>
          <p class="text-xs text-slate-400 mt-1">time limit</p>
        </div>

        <!-- Attempts -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
          <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Attempts</span>
            <div class="w-8 h-8 rounded-xl bg-sky-50 flex items-center justify-center">
              <svg class="w-4 h-4 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
          </div>
          <p class="text-3xl font-bold text-slate-900">{{ exam.attempts.length }}</p>
          <p class="text-xs text-slate-400 mt-1">{{ submittedCount }} submitted</p>
        </div>
      </div>

      <!-- Main content + side card -->
      <div class="flex flex-col lg:flex-row gap-6 items-start">

        <!-- Tab panel (main / left) -->
        <div class="flex-1 min-w-0">
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">

            <!-- Tab bar -->
            <div class="border-b border-slate-100">
              <div class="flex">
                <button
                  v-for="tab in tabs"
                  :key="tab.key"
                  @click="activeTab = tab.key"
                  :class="[
                    'px-6 py-3.5 text-sm font-medium transition-colors relative',
                    activeTab === tab.key
                      ? 'text-white'
                      : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50',
                  ]"
                  :style="activeTab === tab.key ? 'background-color: #dc143c;' : ''"
                >
                  {{ tab.label }}
                  <span
                    :class="[
                      'ml-2 inline-flex items-center justify-center min-w-[1.2rem] h-5 px-1 rounded-full text-xs font-semibold',
                      activeTab === tab.key
                        ? 'bg-white/25 text-white'
                        : 'bg-slate-100 text-slate-600',
                    ]"
                  >
                    {{ tab.count }}
                  </span>
                </button>
              </div>
            </div>

            <!-- Questions tab -->
            <div v-show="activeTab === 'questions'">
              <div
                v-if="sortedQuestions.length === 0"
                class="flex flex-col items-center justify-center py-16 text-center px-4"
              >
                <div
                  class="w-14 h-14 rounded-2xl flex items-center justify-center mb-4"
                  style="background-color: #fde8ec;"
                >
                  <svg class="w-7 h-7" style="color: #dc143c;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <p class="text-base font-semibold text-slate-700">No questions yet</p>
                <p class="text-sm text-slate-400 mt-1">
                  Add questions from the question bank to get started.
                </p>
                <Link
                  :href="`/teacher/exams/${exam.id}/questions`"
                  class="mt-4 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-white transition-opacity hover:opacity-90"
                  style="background-color: #dc143c;"
                >
                  Add Questions
                </Link>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-12">#</th>
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Question</th>
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-24">Type</th>
                      <th class="px-5 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wide w-20">Marks</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                    <tr
                      v-for="question in sortedQuestions"
                      :key="question.id"
                      class="hover:bg-slate-50/60 transition-colors"
                    >
                      <td class="px-5 py-3.5 text-slate-400 font-mono text-xs">
                        {{ question.pivot.order }}
                      </td>
                      <td class="px-5 py-3.5 text-slate-700 max-w-sm" :title="stripHtml(question.question_text)">
                        {{ truncate(stripHtml(question.question_text), 80) }}
                      </td>
                      <td class="px-5 py-3.5">
                        <span
                          :class="[
                            'inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-semibold',
                            questionTypeBadgeClass(question.type),
                          ]"
                        >
                          {{ questionTypeLabel(question.type) }}
                        </span>
                      </td>
                      <td class="px-5 py-3.5 text-right font-bold text-slate-900">
                        {{ question.pivot.marks }}
                      </td>
                    </tr>
                  </tbody>
                  <tfoot>
                    <tr class="bg-slate-50 border-t-2 border-slate-200">
                      <td colspan="3" class="px-5 py-3.5 text-sm font-semibold text-slate-600">
                        Marks total (sum)
                      </td>
                      <td class="px-5 py-3.5 text-right text-sm font-bold" style="color: #dc143c;">
                        {{ questionMarksSum }}
                      </td>
                    </tr>
                  </tfoot>
                </table>
              </div>
            </div>

            <!-- Attempts tab -->
            <div v-show="activeTab === 'attempts'">
              <div
                v-if="exam.attempts.length === 0"
                class="flex flex-col items-center justify-center py-16 text-center px-4"
              >
                <div class="w-14 h-14 rounded-2xl bg-sky-50 flex items-center justify-center mb-4">
                  <svg class="w-7 h-7 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                </div>
                <p class="text-base font-semibold text-slate-700">No attempts yet</p>
                <p class="text-sm text-slate-400 mt-1">
                  Students have not started this exam yet.
                </p>
              </div>

              <div v-else class="overflow-x-auto">
                <table class="w-full text-sm">
                  <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide">Student</th>
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Student ID</th>
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-36">Status</th>
                      <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wide w-44">Started At</th>
                    </tr>
                  </thead>
                  <tbody class="divide-y divide-slate-50">
                    <tr
                      v-for="attempt in exam.attempts"
                      :key="attempt.id"
                      class="hover:bg-slate-50/60 transition-colors"
                    >
                      <td class="px-5 py-3.5">
                        <div class="flex items-center gap-3">
                          <div
                            class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                            style="background-color: #dc143c;"
                          >
                            {{ initials(attempt.student.user.name) }}
                          </div>
                          <div class="min-w-0">
                            <p class="font-medium text-slate-900 truncate">{{ attempt.student.user.name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ attempt.student.user.email }}</p>
                          </div>
                        </div>
                      </td>
                      <td class="px-5 py-3.5 font-mono text-xs text-slate-500">
                        {{ attempt.student.student_id }}
                      </td>
                      <td class="px-5 py-3.5">
                        <span
                          :class="[
                            'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold',
                            attemptStatusBadgeClass(attempt.status),
                          ]"
                        >
                          {{ attemptStatusLabel(attempt.status) }}
                        </span>
                      </td>
                      <td class="px-5 py-3.5 text-xs text-slate-500">
                        {{ formatDateTime(attempt.started_at) }}
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>

          </div>
        </div>

        <!-- Right side card (always visible on lg) -->
        <div class="w-full lg:w-80 flex-shrink-0">
          <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-5">
            <h3 class="text-sm font-bold text-slate-900">Exam Details</h3>

            <!-- Course -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Course</p>
                <p class="text-sm font-medium text-slate-900">{{ exam.course.title }}</p>
                <p class="text-xs text-slate-400">{{ exam.course.code }}</p>
              </div>
            </div>

            <!-- Department -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Department</p>
                <p class="text-sm font-medium text-slate-900">{{ exam.course.department.name }}</p>
              </div>
            </div>

            <!-- Creator -->
            <div class="flex items-start gap-3">
              <div class="w-8 h-8 rounded-xl bg-slate-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <div class="min-w-0">
                <p class="text-xs font-semibold text-slate-400 uppercase tracking-wide mb-0.5">Created By</p>
                <p class="text-sm font-medium text-slate-900">{{ exam.creator.name }}</p>
              </div>
            </div>

            <div class="border-t border-slate-100 pt-4 space-y-3">
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Schedule</p>
              <div class="space-y-2">
                <div class="flex items-center justify-between gap-2">
                  <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Starts
                  </span>
                  <span class="text-xs font-medium text-slate-700 text-right">{{ formatDateTime(exam.start_time) }}</span>
                </div>
                <div class="flex items-center justify-between gap-2">
                  <span class="flex items-center gap-1.5 text-xs text-slate-500">
                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                    </svg>
                    Ends
                  </span>
                  <span class="text-xs font-medium text-slate-700 text-right">{{ formatDateTime(exam.end_time) }}</span>
                </div>
              </div>
            </div>

            <!-- Settings chips -->
            <div class="border-t border-slate-100 pt-4 space-y-2">
              <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Settings</p>
              <div class="flex flex-wrap gap-2">
                <span
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border',
                    exam.shuffle_questions
                      ? 'bg-indigo-50 text-indigo-700 border-indigo-100'
                      : 'bg-slate-50 text-slate-400 border-slate-100 line-through',
                  ]"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                  </svg>
                  Shuffle Questions
                </span>
                <span
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border',
                    exam.shuffle_options
                      ? 'bg-indigo-50 text-indigo-700 border-indigo-100'
                      : 'bg-slate-50 text-slate-400 border-slate-100 line-through',
                  ]"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                  </svg>
                  Shuffle Options
                </span>
                <span
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border',
                    exam.allow_backtrack
                      ? 'bg-emerald-50 text-emerald-700 border-emerald-100'
                      : 'bg-slate-50 text-slate-400 border-slate-100 line-through',
                  ]"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12" />
                  </svg>
                  Backtrack
                </span>
                <span
                  :class="[
                    'inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium border',
                    exam.show_result_immediately
                      ? 'bg-amber-50 text-amber-700 border-amber-100'
                      : 'bg-slate-50 text-slate-400 border-slate-100 line-through',
                  ]"
                >
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
                  Instant Results
                </span>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

// ── Props ─────────────────────────────────────────────────────────────────────

const props = defineProps({
  exam: {
    type: Object,
    required: true,
  },
})

// ── State ─────────────────────────────────────────────────────────────────────

const activeTab = ref('questions')
const publishing = ref(false)

// ── Tabs config ───────────────────────────────────────────────────────────────

const tabs = computed(() => [
  { key: 'questions', label: 'Questions', count: props.exam.questions.length },
  { key: 'attempts',  label: 'Attempts',  count: props.exam.attempts.length },
])

// ── Computed ──────────────────────────────────────────────────────────────────

const sortedQuestions = computed(() =>
  [...props.exam.questions].sort((a, b) => (a.pivot?.order ?? 0) - (b.pivot?.order ?? 0))
)

const questionMarksSum = computed(() =>
  sortedQuestions.value.reduce((sum, q) => sum + Number(q.pivot?.marks ?? q.marks ?? 0), 0)
)

const submittedCount = computed(() =>
  props.exam.attempts.filter(
    (a) => a.status === 'submitted' || a.status === 'auto_submitted'
  ).length
)

// ── Helpers ───────────────────────────────────────────────────────────────────

function truncate(text, length = 80) {
  if (!text) return ''
  return text.length > length ? text.slice(0, length) + '…' : text
}

function stripHtml(html) {
  if (!html) return ''
  return html.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim()
}

function initials(name) {
  if (!name) return '?'
  const parts = name.trim().split(' ')
  if (parts.length === 1) return parts[0].slice(0, 2).toUpperCase()
  return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase()
}

function formatDateTime(dateStr) {
  if (!dateStr) return '—'
  return new Intl.DateTimeFormat('en-GB', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  }).format(new Date(dateStr))
}

// ── Status badges ─────────────────────────────────────────────────────────────

const STATUS_MAP = {
  draft:     { badge: 'bg-slate-100 text-slate-600',    dot: 'bg-slate-400',   label: 'Draft' },
  scheduled: { badge: 'bg-blue-100 text-blue-700',      dot: 'bg-blue-500',    label: 'Scheduled' },
  active:    { badge: 'bg-emerald-100 text-emerald-700',dot: 'bg-emerald-500', label: 'Active' },
  completed: { badge: 'text-white',                     dot: 'bg-white/60',    label: 'Completed', style: true },
  cancelled: { badge: 'bg-red-100 text-red-700',        dot: 'bg-red-400',     label: 'Cancelled' },
}

function statusBadgeClass(status) {
  return STATUS_MAP[status]?.badge ?? 'bg-slate-100 text-slate-600'
}

function statusDotClass(status) {
  return STATUS_MAP[status]?.dot ?? 'bg-slate-400'
}

function statusLabel(status) {
  return STATUS_MAP[status]?.label ?? status
}

// ── Attempt status badges ─────────────────────────────────────────────────────

const ATTEMPT_STATUS_MAP = {
  in_progress:    { badge: 'bg-amber-100 text-amber-700',    label: 'In Progress' },
  submitted:      { badge: 'bg-emerald-100 text-emerald-700',label: 'Submitted' },
  auto_submitted: { badge: 'bg-sky-100 text-sky-700',        label: 'Auto-Submitted' },
  abandoned:      { badge: 'bg-slate-100 text-slate-500',    label: 'Abandoned' },
}

function attemptStatusBadgeClass(status) {
  return ATTEMPT_STATUS_MAP[status]?.badge ?? 'bg-slate-100 text-slate-500'
}

function attemptStatusLabel(status) {
  return ATTEMPT_STATUS_MAP[status]?.label ?? status
}

// ── Question type labels ──────────────────────────────────────────────────────

const QUESTION_TYPE_MAP = {
  mcq_single:   { label: 'MCQ',   badge: 'bg-blue-50 text-blue-700' },
  mcq_multiple: { label: 'Multi', badge: 'bg-violet-50 text-violet-700' },
  true_false:   { label: 'T/F',   badge: 'bg-sky-50 text-sky-700' },
  fill_blank:   { label: 'Fill',  badge: 'bg-teal-50 text-teal-700' },
  short:        { label: 'Short', badge: 'bg-amber-50 text-amber-700' },
  descriptive:  { label: 'Essay', badge: 'bg-orange-50 text-orange-700' },
}

function questionTypeLabel(type) {
  return QUESTION_TYPE_MAP[type]?.label ?? type
}

function questionTypeBadgeClass(type) {
  return QUESTION_TYPE_MAP[type]?.badge ?? 'bg-slate-50 text-slate-600'
}

// ── Actions ───────────────────────────────────────────────────────────────────

function publishExam() {
  if (publishing.value) return
  publishing.value = true
  router.patch(
    `/teacher/exams/${props.exam.id}/status`,
    { status: 'scheduled' },
    {
      preserveScroll: true,
      onFinish: () => { publishing.value = false },
    }
  )
}
</script>
