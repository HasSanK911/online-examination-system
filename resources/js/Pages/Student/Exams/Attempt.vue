<template>
  <!-- Full-screen exam environment -->
  <div class="min-h-screen bg-slate-950 text-white flex flex-col select-none" @contextmenu.prevent @copy.prevent @paste.prevent @cut.prevent>
    <!-- Top bar -->
    <header class="flex items-center justify-between px-6 py-3 bg-slate-900 border-b border-slate-800">
      <div>
        <p class="text-xs text-slate-400">Examination</p>
        <h1 class="font-semibold text-white">{{ exam.title }}</h1>
      </div>
      <div class="flex items-center gap-6">
        <!-- Progress -->
        <div class="text-center">
          <p class="text-xs text-slate-400">Answered</p>
          <p class="text-lg font-bold text-emerald-400">{{ answeredCount }} / {{ questions.length }}</p>
        </div>
        <!-- Timer -->
        <div :class="['text-center px-4 py-2 rounded-xl font-mono font-bold text-xl', timerClass]">
          {{ formattedTime }}
        </div>
        <!-- Submit -->
        <button @click="confirmSubmit" class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl font-medium transition-colors">
          Submit Exam
        </button>
      </div>
    </header>

    <!-- Suspicious warning banner -->
    <div v-if="warningVisible" class="bg-amber-500 text-amber-950 px-6 py-2 text-sm font-medium flex items-center justify-between">
      <span>⚠️ Warning: Tab switching detected ({{ tabSwitchCount }}/{{ tabSwitchLimit }}). Continuing may cause auto-submission.</span>
      <button @click="warningVisible = false" class="underline">Dismiss</button>
    </div>

    <div class="flex flex-1 overflow-hidden">
      <!-- Question Navigator -->
      <aside class="w-64 flex-shrink-0 bg-slate-900 border-r border-slate-800 flex flex-col p-4 overflow-y-auto">
        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-3">Question Navigator</p>
        <div class="grid grid-cols-5 gap-2 mb-4">
          <button
            v-for="(q, idx) in questions"
            :key="q.id"
            @click="currentIndex = idx"
            :class="[
              'w-9 h-9 rounded-lg text-xs font-bold transition-all',
              currentIndex === idx ? 'ring-2 ring-indigo-400 ring-offset-1 ring-offset-slate-900' : '',
              answers[q.id]?.is_marked_for_review ? 'bg-amber-500 text-white' :
              answers[q.id]?.is_answered ? 'bg-emerald-500 text-white' :
              'bg-slate-700 text-slate-300 hover:bg-slate-600'
            ]"
          >{{ idx + 1 }}</button>
        </div>
        <!-- Legend -->
        <div class="space-y-2 mt-auto text-xs">
          <div class="flex items-center gap-2"><div class="w-4 h-4 rounded bg-emerald-500"></div> Answered</div>
          <div class="flex items-center gap-2"><div class="w-4 h-4 rounded bg-amber-500"></div> Marked for review</div>
          <div class="flex items-center gap-2"><div class="w-4 h-4 rounded bg-slate-700"></div> Not answered</div>
        </div>
      </aside>

      <!-- Question Area -->
      <main class="flex-1 overflow-y-auto p-8">
        <div v-if="currentQuestion" class="max-w-2xl mx-auto">
          <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-slate-400">Question {{ currentIndex + 1 }} of {{ questions.length }}</p>
            <span class="text-xs bg-slate-800 px-3 py-1 rounded-full text-slate-300">{{ currentQuestion.marks }} mark(s)</span>
          </div>

          <!-- Question text -->
          <div class="text-lg font-medium text-white leading-relaxed mb-8" v-html="currentQuestion.question_text"></div>

          <!-- MCQ Single / True-False -->
          <div v-if="['mcq_single', 'true_false'].includes(currentQuestion.type)" class="space-y-3">
            <label
              v-for="option in currentQuestion.options"
              :key="option.id"
              :class="[
                'flex items-center gap-4 p-4 rounded-xl border cursor-pointer transition-all',
                isOptionSelected(option.id)
                  ? 'border-indigo-500 bg-indigo-500/10 text-white'
                  : 'border-slate-700 hover:border-slate-500 text-slate-300'
              ]"
            >
              <input
                type="radio"
                :name="`q_${currentQuestion.id}`"
                :value="option.id"
                class="hidden"
                @change="selectOption(option.id)"
                :checked="isOptionSelected(option.id)"
              />
              <div :class="['w-5 h-5 rounded-full border-2 flex items-center justify-center flex-shrink-0', isOptionSelected(option.id) ? 'border-indigo-500' : 'border-slate-600']">
                <div v-if="isOptionSelected(option.id)" class="w-2.5 h-2.5 rounded-full bg-indigo-500"></div>
              </div>
              <span>{{ option.text }}</span>
            </label>
          </div>

          <!-- MCQ Multiple -->
          <div v-else-if="currentQuestion.type === 'mcq_multiple'" class="space-y-3">
            <p class="text-xs text-slate-400 mb-2">Select all that apply.</p>
            <label
              v-for="option in currentQuestion.options"
              :key="option.id"
              :class="[
                'flex items-center gap-4 p-4 rounded-xl border cursor-pointer transition-all',
                isOptionSelected(option.id)
                  ? 'border-indigo-500 bg-indigo-500/10 text-white'
                  : 'border-slate-700 hover:border-slate-500 text-slate-300'
              ]"
            >
              <input type="checkbox" :checked="isOptionSelected(option.id)" @change="toggleOption(option.id)" class="hidden" />
              <div :class="['w-5 h-5 rounded border-2 flex items-center justify-center flex-shrink-0', isOptionSelected(option.id) ? 'border-indigo-500 bg-indigo-500' : 'border-slate-600']">
                <svg v-if="isOptionSelected(option.id)" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                </svg>
              </div>
              <span>{{ option.text }}</span>
            </label>
          </div>

          <!-- Fill blank / Short / Descriptive -->
          <div v-else class="space-y-2">
            <p class="text-xs text-slate-400">
              {{ currentQuestion.type === 'fill_blank' ? 'Fill in the blank.' : currentQuestion.type === 'short' ? 'Write a short answer.' : 'Write a detailed answer.' }}
            </p>
            <textarea
              v-model="answers[currentQuestion.id].text_answer"
              @input="onTextInput"
              :rows="currentQuestion.type === 'descriptive' ? 10 : 4"
              placeholder="Type your answer here…"
              class="w-full bg-slate-800 border border-slate-700 rounded-xl p-4 text-white placeholder-slate-500 resize-none focus:outline-none focus:border-indigo-500 transition-colors"
            ></textarea>
          </div>

          <!-- Navigation & Mark for review -->
          <div class="flex items-center justify-between mt-8">
            <button
              @click="toggleMarkForReview"
              :class="['px-4 py-2 rounded-xl text-sm font-medium transition-colors', answers[currentQuestion.id]?.is_marked_for_review ? 'bg-amber-500 text-white' : 'bg-slate-800 text-slate-300 hover:bg-slate-700']"
            >
              {{ answers[currentQuestion.id]?.is_marked_for_review ? '★ Marked' : '☆ Mark for Review' }}
            </button>
            <div class="flex gap-3">
              <button
                v-if="exam.allow_backtrack && currentIndex > 0"
                @click="currentIndex--"
                class="px-5 py-2 bg-slate-800 hover:bg-slate-700 rounded-xl text-sm font-medium transition-colors"
              >← Previous</button>
              <button
                v-if="currentIndex < questions.length - 1"
                @click="nextQuestion"
                class="px-5 py-2 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-sm font-medium transition-colors"
              >Next →</button>
              <button
                v-else
                @click="confirmSubmit"
                class="px-5 py-2 bg-emerald-600 hover:bg-emerald-500 rounded-xl text-sm font-medium transition-colors"
              >Submit Exam ✓</button>
            </div>
          </div>
        </div>
      </main>
    </div>

    <!-- Submit confirmation modal -->
    <div v-if="showSubmitModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/70">
      <div class="bg-slate-900 border border-slate-700 rounded-2xl p-8 max-w-sm mx-4 text-center">
        <div class="w-16 h-16 bg-indigo-600/20 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-8 h-8 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
        <h3 class="text-xl font-bold text-white mb-2">Submit Exam?</h3>
        <p class="text-slate-400 text-sm mb-2">
          You have answered <strong class="text-white">{{ answeredCount }}</strong> out of <strong class="text-white">{{ questions.length }}</strong> questions.
        </p>
        <p v-if="markedCount > 0" class="text-amber-400 text-sm mb-4">{{ markedCount }} question(s) still marked for review.</p>
        <p class="text-slate-500 text-xs mb-6">This action cannot be undone.</p>
        <div class="flex gap-3">
          <button @click="showSubmitModal = false" class="flex-1 py-2.5 bg-slate-800 hover:bg-slate-700 rounded-xl text-sm font-medium">Cancel</button>
          <button @click="doSubmit" :disabled="submitting" class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-500 rounded-xl text-sm font-medium">
            {{ submitting ? 'Submitting…' : 'Confirm Submit' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { router } from '@inertiajs/vue3'
import axios from 'axios'

const props = defineProps({
  attempt: Object,
  exam: Object,
  questions: Array,
  saved_answers: Object,
  started_at: String,
  time_limit: Number,
})

const currentIndex   = ref(0)
const warningVisible = ref(false)
const showSubmitModal = ref(false)
const submitting     = ref(false)
const tabSwitchCount = ref(0)
const tabSwitchLimit = 10

// Initialize answers map
const answers = ref({})
props.questions.forEach(q => {
  const saved = props.saved_answers[q.id]
  answers.value[q.id] = {
    is_answered:          saved?.is_answered ?? false,
    is_marked_for_review: saved?.is_marked_for_review ?? false,
    selected_option_ids:  saved?.selected_option_ids ?? [],
    text_answer:          saved?.text_answer ?? '',
  }
})

// Timer
const secondsLeft = ref(props.time_limit)
let timerInterval = null

function startTimer() {
  const elapsed = Math.floor((Date.now() - new Date(props.started_at).getTime()) / 1000)
  secondsLeft.value = Math.max(0, props.time_limit - elapsed)

  timerInterval = setInterval(() => {
    secondsLeft.value--
    if (secondsLeft.value <= 0) {
      clearInterval(timerInterval)
      doSubmit(true)
    }
  }, 1000)
}

const formattedTime = computed(() => {
  const m = Math.floor(secondsLeft.value / 60).toString().padStart(2, '0')
  const s = (secondsLeft.value % 60).toString().padStart(2, '0')
  return `${m}:${s}`
})

const timerClass = computed(() => {
  if (secondsLeft.value <= 60) return 'bg-red-600 text-white animate-pulse'
  if (secondsLeft.value <= 300) return 'bg-amber-500 text-white'
  return 'bg-slate-800 text-white'
})

const currentQuestion = computed(() => props.questions[currentIndex.value])

const answeredCount = computed(() =>
  Object.values(answers.value).filter(a => a.is_answered).length
)
const markedCount = computed(() =>
  Object.values(answers.value).filter(a => a.is_marked_for_review).length
)

// Auto-save
let saveInterval = null
function startAutoSave() {
  saveInterval = setInterval(saveCurrentAnswer, 10000)
}

async function saveCurrentAnswer() {
  const q  = currentQuestion.value
  if (!q) return
  const a  = answers.value[q.id]
  try {
    await axios.post(`/api/exam/${props.attempt.id}/save-answer`, {
      question_id:           q.id,
      selected_option_ids:   a.selected_option_ids,
      text_answer:           a.text_answer,
      is_marked_for_review:  a.is_marked_for_review,
      is_answered:           a.is_answered,
    })
  } catch (e) { /* silent */ }
}

function isOptionSelected(optionId) {
  return answers.value[currentQuestion.value.id]?.selected_option_ids?.includes(optionId)
}

function selectOption(optionId) {
  answers.value[currentQuestion.value.id].selected_option_ids = [optionId]
  answers.value[currentQuestion.value.id].is_answered = true
  saveCurrentAnswer()
}

function toggleOption(optionId) {
  const ids = answers.value[currentQuestion.value.id].selected_option_ids
  const idx = ids.indexOf(optionId)
  if (idx >= 0) ids.splice(idx, 1)
  else ids.push(optionId)
  answers.value[currentQuestion.value.id].is_answered = ids.length > 0
  saveCurrentAnswer()
}

let textTimer = null
function onTextInput() {
  const q = currentQuestion.value
  answers.value[q.id].is_answered = !!answers.value[q.id].text_answer?.trim()
  clearTimeout(textTimer)
  textTimer = setTimeout(saveCurrentAnswer, 1500)
}

function toggleMarkForReview() {
  const q = currentQuestion.value
  answers.value[q.id].is_marked_for_review = !answers.value[q.id].is_marked_for_review
  saveCurrentAnswer()
}

function nextQuestion() {
  saveCurrentAnswer()
  currentIndex.value++
}

function confirmSubmit() {
  saveCurrentAnswer()
  showSubmitModal.value = true
}

async function doSubmit(isAuto = false) {
  if (submitting.value) return
  submitting.value = true
  await saveCurrentAnswer()
  try {
    await axios.post(`/api/exam/${props.attempt.id}/submit`)
    router.visit('/student/results')
  } catch (e) {
    submitting.value = false
  }
}

// Anti-cheat
function handleVisibilityChange() {
  if (document.hidden) {
    tabSwitchCount.value++
    axios.post(`/api/exam/${props.attempt.id}/log-activity`, { type: 'tab_switch' }).catch(() => {})
    if (tabSwitchCount.value >= 5) warningVisible.value = true
  }
}

function blockShortcuts(e) {
  if (e.key === 'F12' || (e.ctrlKey && ['u', 's', 'i', 'j'].includes(e.key.toLowerCase()))) {
    e.preventDefault()
  }
}

onMounted(() => {
  startTimer()
  startAutoSave()
  document.addEventListener('visibilitychange', handleVisibilityChange)
  document.addEventListener('keydown', blockShortcuts)

  // Restore dark background after leaving exam
  const savedTheme = localStorage.getItem('theme')
  if (savedTheme === 'light') document.documentElement.classList.remove('dark')
})

onBeforeUnmount(() => {
  clearInterval(timerInterval)
  clearInterval(saveInterval)
  document.removeEventListener('visibilitychange', handleVisibilityChange)
  document.removeEventListener('keydown', blockShortcuts)
})
</script>
