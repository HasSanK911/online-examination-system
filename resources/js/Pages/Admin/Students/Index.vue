<template>
  <AppLayout>
    <template #header>
      <div class="flex items-center gap-3 w-full">
        <!-- Breadcrumb navigation -->
        <template v-if="filters.department_id">
          <button @click="goToDepts"
            class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-700 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
        </template>
        <div class="flex-1">
          <div class="flex items-center gap-2 text-xs text-slate-400 mb-0.5">
            <button @click="goToDepts"
              :class="['hover:text-primary-600 transition-colors font-medium', !filters.department_id ? 'text-slate-900 font-semibold pointer-events-none' : '']">
              Departments
            </button>
            <template v-if="filters.department_id">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <button @click="goToSemesters"
                :class="['hover:text-primary-600 transition-colors font-medium', !filters.semester ? 'text-slate-900 font-semibold pointer-events-none' : '']">
                {{ selectedDepartment?.name }}
              </button>
            </template>
            <template v-if="filters.semester">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
              <span class="text-slate-900 font-semibold">Semester {{ filters.semester }}</span>
            </template>
          </div>
          <h1 class="text-xl font-bold text-slate-900 font-heading leading-none">
            <template v-if="!filters.department_id">Students</template>
            <template v-else-if="!filters.semester">{{ selectedDepartment?.name }}</template>
            <template v-else>Semester {{ filters.semester }} · {{ selectedDepartment?.name }}</template>
          </h1>
        </div>

        <button @click="showAddModal = true" class="btn-primary ml-auto whitespace-nowrap">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
          </svg>
          Add Student
        </button>
      </div>
    </template>

    <!-- ─── VIEW 1: Department cards ──────────────────────────────────── -->
    <template v-if="!filters.department_id">
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
          <p class="text-2xl font-bold text-slate-900">{{ totalStudents }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Total Students</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
          <p class="text-2xl font-bold text-emerald-600">{{ totalActive }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Active</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
          <p class="text-2xl font-bold text-primary-600">{{ departmentGroups.length }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Departments</p>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
          <p class="text-2xl font-bold text-sky-600">{{ totalSemesters }}</p>
          <p class="text-xs text-slate-400 mt-0.5">Active Semesters</p>
        </div>
      </div>

      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <button
          v-for="dept in departmentGroups"
          :key="dept.id"
          @click="selectDepartment(dept)"
          class="bg-white rounded-2xl border border-slate-200 p-5 text-left hover:border-primary-300 hover:shadow-lg hover:shadow-primary-100/50 transition-all duration-200 group"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="w-11 h-11 rounded-xl bg-primary-50 group-hover:bg-primary-100 flex items-center justify-center transition-colors">
              <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
              </svg>
            </div>
            <span class="font-mono text-xs bg-slate-100 text-slate-500 px-2 py-1 rounded-lg">{{ dept.code }}</span>
          </div>

          <h3 class="font-bold text-slate-900 mb-0.5 group-hover:text-primary-700 transition-colors">{{ dept.name }}</h3>
          <p class="text-xs text-slate-400 mb-4">{{ dept.faculty }}</p>

          <div class="flex items-center justify-between text-xs mb-4">
            <div class="text-center">
              <p class="text-xl font-black text-slate-900">{{ dept.total }}</p>
              <p class="text-slate-400 mt-0.5">Students</p>
            </div>
            <div class="text-center">
              <p class="text-xl font-black text-emerald-600">{{ dept.active }}</p>
              <p class="text-slate-400 mt-0.5">Active</p>
            </div>
            <div class="text-center">
              <p class="text-xl font-black text-sky-600">{{ dept.semesters.length }}</p>
              <p class="text-slate-400 mt-0.5">Semesters</p>
            </div>
          </div>

          <!-- Semester mini-dots -->
          <div v-if="dept.semesters.length" class="flex gap-1.5 mb-3">
            <div
              v-for="sem in dept.semesters"
              :key="sem.semester"
              class="flex-1 h-1.5 bg-primary-200 rounded-full"
              :title="`Sem ${sem.semester}: ${sem.count}`"
            ></div>
          </div>

          <div class="flex items-center justify-end gap-1 text-xs text-slate-400 group-hover:text-primary-500 transition-colors">
            <span>View semesters</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>
      </div>
    </template>

    <!-- ─── VIEW 2: Semester cards ─────────────────────────────────────── -->
    <template v-else-if="filters.department_id && !filters.semester">
      <div class="bg-white rounded-2xl border border-slate-200 p-5 flex items-center gap-4 mb-6">
        <div class="w-12 h-12 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
          </svg>
        </div>
        <div class="flex-1">
          <h2 class="text-lg font-bold text-slate-900 font-heading">{{ selectedDepartment?.name }}</h2>
          <p class="text-sm text-slate-400">{{ selectedDepartment?.faculty?.name ?? selectedDepartment?.faculty }} · Select a semester to view students</p>
        </div>
        <div class="text-right">
          <p class="text-2xl font-bold text-primary-600">{{ currentDeptGroup?.total ?? 0 }}</p>
          <p class="text-xs text-slate-400">Total Students</p>
        </div>
      </div>

      <div v-if="!currentDeptGroup?.semesters?.length" class="text-center py-16">
        <p class="text-slate-500">No students enrolled in this department yet.</p>
      </div>

      <div v-else class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
        <button
          v-for="sem in currentDeptGroup.semesters"
          :key="sem.semester"
          @click="selectSemester(sem.semester)"
          class="bg-white rounded-2xl border border-slate-200 p-6 text-center hover:border-primary-300 hover:shadow-lg hover:shadow-primary-100/50 transition-all duration-200 group"
        >
          <div class="w-14 h-14 rounded-full mx-auto mb-3 flex items-center justify-center text-2xl font-black transition-colors"
            :class="semesterColor(sem.semester).bg">
            <span :class="semesterColor(sem.semester).text">{{ sem.semester }}</span>
          </div>
          <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-2">Semester</p>
          <p class="text-4xl font-black text-slate-900">{{ sem.count }}</p>
          <p class="text-xs text-slate-400 mt-1">Students</p>
          <div class="mt-4 flex items-center justify-center gap-1 text-xs text-slate-400 group-hover:text-primary-500 transition-colors">
            <span>View list</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </div>
        </button>
      </div>
    </template>

    <!-- ─── VIEW 3: Students DataTable ────────────────────────────────── -->
    <template v-else>
      <div class="flex flex-col sm:flex-row gap-3 mb-5">
        <div class="relative flex-1 max-w-sm">
          <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
          </svg>
          <input v-model="searchQuery" @keydown.enter="applySearch" placeholder="Search by name, ID or roll no…" class="input pl-9" />
        </div>
        <select v-model="statusFilter" @change="applySearch" class="input w-36">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="graduated">Graduated</option>
        </select>
        <button v-if="searchQuery || statusFilter" @click="clearFilters" class="btn-ghost">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          Clear
        </button>
      </div>

      <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
          <div class="w-8 h-8 bg-primary-50 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
          </div>
          <span class="text-sm font-semibold text-slate-900">{{ students?.total ?? 0 }} student{{ (students?.total ?? 0) !== 1 ? 's' : '' }}</span>
        </div>

        <DataTable
          :value="studentsData"
          :pt="tablePT"
          removableSort
          @sort="onSort"
        >
          <Column field="name" header="Student" :sortable="true" :pt="colPT">
            <template #body="{ data }">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0"
                  :style="{ background: avatarGradient(data.name) }">
                  {{ data.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div>
                  <p class="font-semibold text-slate-900 text-sm">{{ data.name }}</p>
                  <p class="text-xs text-slate-400">{{ data.email }}</p>
                </div>
              </div>
            </template>
          </Column>

          <Column field="student_id" header="Student ID" :sortable="true" :pt="colPT">
            <template #body="{ data }">
              <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded-lg">{{ data.student_id }}</span>
            </template>
          </Column>

          <Column field="roll_number" header="Roll No." :sortable="true" :pt="colPT">
            <template #body="{ data }">
              <span class="text-sm text-slate-600 font-mono">{{ data.roll_number }}</span>
            </template>
          </Column>

          <Column field="batch" header="Batch" :pt="colPT">
            <template #body="{ data }">
              <span class="text-sm text-slate-600">{{ data.batch || '—' }}</span>
            </template>
          </Column>

          <Column field="status" header="Status" :pt="colPT">
            <template #body="{ data }">
              <span :class="[
                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold',
                data.status === 'active'    ? 'bg-emerald-50 text-emerald-700' :
                data.status === 'graduated' ? 'bg-sky-50 text-sky-700' :
                                              'bg-slate-100 text-slate-500'
              ]">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="data.status === 'active' ? 'bg-emerald-500' : data.status === 'graduated' ? 'bg-sky-500' : 'bg-slate-400'">
                </span>
                {{ data.status?.charAt(0).toUpperCase() + data.status?.slice(1) }}
              </span>
            </template>
          </Column>

          <Column header="Actions" :pt="colPT">
            <template #body="{ data }">
              <div class="flex items-center gap-1">
                <button @click="viewStudent(data.id)"
                  class="p-2 rounded-xl text-slate-400 hover:bg-primary-50 hover:text-primary-600 transition-colors" title="View">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                  </svg>
                </button>
                <button @click="deleteStudent(data)"
                  class="p-2 rounded-xl text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors" title="Delete">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                  </svg>
                </button>
              </div>
            </template>
          </Column>

          <template #empty>
            <div class="py-12 text-center">
              <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <p class="text-sm font-medium text-slate-500">No students found</p>
            </div>
          </template>
        </DataTable>

        <div v-if="students && students.last_page > 1"
             class="px-5 py-4 border-t border-slate-100 flex items-center justify-between">
          <p class="text-xs text-slate-400">Showing {{ students.from }}–{{ students.to }} of {{ students.total }}</p>
          <div class="flex gap-1.5">
            <Link
              v-for="link in students.links" :key="link.label"
              :href="link.url || '#'"
              :class="[
                'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
                link.active ? 'bg-primary-600 text-white' : 'text-slate-600 hover:bg-slate-100',
                !link.url ? 'opacity-40 pointer-events-none' : ''
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </template>

    <!-- ─── Add Student Modal ─────────────────────────────────────────── -->
    <Teleport to="body">
      <div v-if="showAddModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showAddModal = false" />
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 z-10 max-h-[90vh] overflow-y-auto">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 font-heading">Add New Student</h3>
            <button @click="showAddModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <form @submit.prevent="submitStudent" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="label">Full Name *</label>
                <input v-model="addForm.name" class="input" placeholder="Full legal name" required />
              </div>
              <div class="col-span-2">
                <label class="label">Email *</label>
                <input v-model="addForm.email" type="email" class="input" placeholder="student@university.edu" required />
              </div>
              <div>
                <label class="label">Student ID *</label>
                <input v-model="addForm.student_id" class="input font-mono" placeholder="CS-2024-001" required />
              </div>
              <div>
                <label class="label">Roll Number *</label>
                <input v-model="addForm.roll_number" class="input font-mono" placeholder="2024CS001" required />
              </div>
              <div>
                <label class="label">Department *</label>
                <select v-model="addForm.department_id" class="input" required>
                  <option value="">Select…</option>
                  <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                </select>
              </div>
              <div>
                <label class="label">Semester *</label>
                <select v-model="addForm.semester" class="input" required>
                  <option value="">Select…</option>
                  <option v-for="s in 8" :key="s" :value="s">Semester {{ s }}</option>
                </select>
              </div>
              <div>
                <label class="label">Batch</label>
                <input v-model="addForm.batch" class="input" placeholder="2024–2028" />
              </div>
              <div>
                <label class="label">Phone</label>
                <input v-model="addForm.phone" class="input" placeholder="+92 300 0000000" />
              </div>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" @click="showAddModal = false" class="btn-ghost flex-1">Cancel</button>
              <button type="submit" :disabled="addProcessing" class="btn-primary flex-1">
                <svg v-if="addProcessing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ addProcessing ? 'Creating…' : 'Create Student' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import DataTable from 'primevue/datatable'
import Column from 'primevue/column'

const props = defineProps({
  departmentGroups:   { type: Array,  default: () => [] },
  selectedDepartment: { type: Object, default: null },
  students:           { type: Object, default: null },
  departments:        { type: Array,  default: () => [] },
  filters:            { type: Object, default: () => ({}) },
})

const showAddModal  = ref(false)
const addProcessing = ref(false)
const searchQuery   = ref(props.filters?.search || '')
const statusFilter  = ref(props.filters?.status || '')

const addForm = ref({
  name: '', email: '', student_id: '', roll_number: '',
  department_id: props.filters?.department_id || '',
  semester: props.filters?.semester || '',
  batch: '', phone: '',
})

const totalStudents  = computed(() => props.departmentGroups.reduce((s, d) => s + (d.total  ?? 0), 0))
const totalActive    = computed(() => props.departmentGroups.reduce((s, d) => s + (d.active ?? 0), 0))
const totalSemesters = computed(() => {
  const sems = new Set()
  props.departmentGroups.forEach(d => d.semesters?.forEach(s => sems.add(s.semester)))
  return sems.size
})
const currentDeptGroup = computed(() =>
  props.departmentGroups.find(d => d.id == props.filters?.department_id)
)
const studentsData = computed(() =>
  (props.students?.data ?? []).map(s => ({
    id:          s.id,
    name:        s.user?.name,
    email:       s.user?.email,
    student_id:  s.student_id,
    roll_number: s.roll_number,
    batch:       s.batch,
    status:      s.status,
  }))
)

const semColors = [
  { bg: 'bg-primary-100', text: 'text-primary-700' },
  { bg: 'bg-emerald-100', text: 'text-emerald-700' },
  { bg: 'bg-sky-100',     text: 'text-sky-700'     },
  { bg: 'bg-amber-100',   text: 'text-amber-700'   },
  { bg: 'bg-violet-100',  text: 'text-violet-700'  },
  { bg: 'bg-pink-100',    text: 'text-pink-700'    },
  { bg: 'bg-teal-100',    text: 'text-teal-700'    },
  { bg: 'bg-orange-100',  text: 'text-orange-700'  },
]
function semesterColor(s) { return semColors[(s - 1) % semColors.length] }

const gradients = [
  'linear-gradient(135deg,#BC2739,#e05a6b)',
  'linear-gradient(135deg,#0ea5e9,#38bdf8)',
  'linear-gradient(135deg,#10b981,#34d399)',
  'linear-gradient(135deg,#f59e0b,#fbbf24)',
  'linear-gradient(135deg,#8b5cf6,#a78bfa)',
]
function avatarGradient(name) { return gradients[(name?.charCodeAt(0) ?? 0) % gradients.length] }

function selectDepartment(dept) { router.get('/admin/students', { department_id: dept.id }) }
function selectSemester(sem)    { router.get('/admin/students', { department_id: props.filters.department_id, semester: sem }) }
function goToDepts()            { router.get('/admin/students', {}) }
function goToSemesters()        { router.get('/admin/students', { department_id: props.filters.department_id }) }

function applySearch() {
  router.get('/admin/students', {
    department_id: props.filters.department_id,
    semester:      props.filters.semester,
    search:        searchQuery.value,
    status:        statusFilter.value,
  }, { preserveState: true, replace: true })
}
function clearFilters() {
  searchQuery.value  = ''
  statusFilter.value = ''
  applySearch()
}
function onSort(e) {
  router.get('/admin/students', {
    department_id: props.filters.department_id,
    semester:      props.filters.semester,
    search:        searchQuery.value,
    status:        statusFilter.value,
    sort_field:    e.sortField,
    sort_dir:      e.sortOrder === 1 ? 'asc' : 'desc',
  }, { preserveState: true, replace: true })
}
function viewStudent(id)   { router.get(`/admin/students/${id}`) }
function deleteStudent(s)  {
  if (confirm(`Delete "${s.name}"?`)) router.delete(`/admin/students/${s.id}`)
}
function submitStudent() {
  addProcessing.value = true
  router.post('/admin/students', addForm.value, {
    onSuccess: () => { showAddModal.value = false; addProcessing.value = false },
    onError:   () => { addProcessing.value = false },
  })
}

// PrimeVue passthrough — pure Tailwind styling
const tablePT = {
  root:              { class: 'w-full' },
  table:             { class: 'w-full text-sm' },
  thead:             { class: '' },
  tbody:             { class: 'divide-y divide-slate-100' },
  headerRow:         { class: 'bg-slate-50 border-b border-slate-100' },
  bodyRow:           () => ({ class: 'hover:bg-slate-50 transition-colors' }),
  emptyMessageCell:  { class: 'px-5 py-12 text-center' },
  sortIcon:          { class: 'ml-1 w-3.5 h-3.5 text-slate-400 inline' },
}
const colPT = {
  headerCell: { class: 'px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider whitespace-nowrap select-none' },
  bodyCell:   { class: 'px-5 py-3.5' },
  headerContent: { class: 'flex items-center gap-1' },
}
</script>
