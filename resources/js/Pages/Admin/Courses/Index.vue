<script setup>
import { ref, reactive, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    courses:        { type: Object, default: () => ({ data: [], total: 0 }) },
    departments:    { type: Array,  default: () => [] },
    teachers:       { type: Array,  default: () => [] },
    manageStudents: { type: Object, default: null },
    filters:        { type: Object, default: () => ({}) },
})

const search  = ref(props.filters.search ?? '')
const deptId  = ref(props.filters.department_id ?? '')

const showModal  = ref(false)
const editTarget = ref(null)

const form = reactive({
    department_id: '', title: '', code: '',
    credit_hours: 3, semester: 1, status: 'active',
})
const errors = reactive({})

function openCreate() {
    Object.assign(form, { department_id: '', title: '', code: '', credit_hours: 3, semester: 1, status: 'active' })
    Object.keys(errors).forEach(k => delete errors[k])
    editTarget.value = null
    showModal.value = true
}
function openEdit(course) {
    Object.assign(form, {
        department_id: course.department_id, title: course.title,
        code: course.code, credit_hours: course.credit_hours,
        semester: course.semester, status: course.status,
    })
    Object.keys(errors).forEach(k => delete errors[k])
    editTarget.value = course
    showModal.value = true
}
function closeModal() { showModal.value = false }

function doSearch() {
    router.get('/admin/courses', {
        search: search.value || undefined,
        department_id: deptId.value || undefined,
    }, { preserveState: true })
}

function submit() {
    Object.keys(errors).forEach(k => delete errors[k])
    const url    = editTarget.value ? `/admin/courses/${editTarget.value.id}` : '/admin/courses'
    const method = editTarget.value ? 'put' : 'post'
    router[method](url, form, {
        onError: e => Object.assign(errors, e),
        onSuccess: () => closeModal(),
    })
}

function destroy(course) {
    if (confirm(`Delete course "${course.title}"?`)) {
        router.delete(`/admin/courses/${course.id}`)
    }
}

// ── Teacher assignment ──────────────────────────────────────────────
const showTeachers   = ref(false)
const manageId       = ref(null)
const selectedTeacher = ref('')

// Re-resolve from props so the modal reflects fresh data after assign/remove
const manageCourse = computed(() => props.courses.data.find(c => c.id === manageId.value) ?? null)
const availableTeachers = computed(() => {
    const assigned = new Set((manageCourse.value?.teachers ?? []).map(t => t.id))
    return props.teachers.filter(t => !assigned.has(t.id))
})

function openTeachers(course) {
    manageId.value = course.id
    selectedTeacher.value = ''
    showTeachers.value = true
}
function closeTeachers() { showTeachers.value = false }

function assignTeacher() {
    if (!selectedTeacher.value) return
    router.post(`/admin/courses/${manageId.value}/teachers`,
        { user_id: selectedTeacher.value },
        { preserveState: true, preserveScroll: true, onSuccess: () => { selectedTeacher.value = '' } })
}
function removeTeacher(userId) {
    router.delete(`/admin/courses/${manageId.value}/teachers/${userId}`,
        { preserveState: true, preserveScroll: true })
}

// ── Student enrollment ──────────────────────────────────────────────
const showStudents     = ref(false)
const loadingStudents  = ref(false)
const savingStudents   = ref(false)
const studentManageId  = ref(null)
const studentSearch    = ref('')
const selectedStudents = ref(new Set())

const studentManageCourse = computed(() => props.courses.data.find(c => c.id === studentManageId.value) ?? null)

const enrollList = computed(() => props.manageStudents?.students ?? [])
const filteredStudents = computed(() => {
    const q = studentSearch.value.trim().toLowerCase()
    if (!q) return enrollList.value
    return enrollList.value.filter(s =>
        (s.name || '').toLowerCase().includes(q) ||
        (s.student_id || '').toLowerCase().includes(q) ||
        (s.roll_number || '').toLowerCase().includes(q))
})
const allFilteredSelected = computed(() =>
    filteredStudents.value.length > 0 && filteredStudents.value.every(s => selectedStudents.value.has(s.id)))

function openStudents(course) {
    studentManageId.value = course.id
    studentSearch.value = ''
    loadingStudents.value = true
    showStudents.value = true
    router.reload({
        only: ['manageStudents'],
        data: { manage_course: course.id },
        preserveScroll: true,
        onSuccess: () => {
            selectedStudents.value = new Set((props.manageStudents?.enrolled_ids ?? []).map(Number))
            loadingStudents.value = false
        },
        onError: () => { loadingStudents.value = false },
    })
}
function closeStudents() { showStudents.value = false }

function toggleStudent(id) {
    const set = new Set(selectedStudents.value)
    set.has(id) ? set.delete(id) : set.add(id)
    selectedStudents.value = set
}
function toggleAllFiltered() {
    const set = new Set(selectedStudents.value)
    if (allFilteredSelected.value) filteredStudents.value.forEach(s => set.delete(s.id))
    else filteredStudents.value.forEach(s => set.add(s.id))
    selectedStudents.value = set
}
function saveEnrollment() {
    savingStudents.value = true
    router.post(`/admin/courses/${studentManageId.value}/students`,
        { student_ids: [...selectedStudents.value] },
        {
            preserveScroll: true,
            onSuccess: () => { showStudents.value = false },
            onFinish: () => { savingStudents.value = false },
        })
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 font-heading">Courses</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ courses.total ?? courses.data?.length }} courses</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                    style="background:#BC2739;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Course
                </button>
            </div>
        </template>

        <!-- Filters -->
        <div class="flex gap-3 mb-5 flex-wrap">
            <input v-model="search" @keyup.enter="doSearch" type="text" placeholder="Search title or code…"
                class="h-9 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 w-56"/>
            <select v-model="deptId" @change="doSearch"
                class="h-9 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Departments</option>
                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
            </select>
            <button @click="doSearch"
                class="h-9 px-4 rounded-xl text-sm font-medium text-white"
                style="background:#BC2739;">Search</button>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Course</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Department</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Sem</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Credits</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Teachers</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Students</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Status</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!courses.data?.length">
                        <td colspan="8" class="px-5 py-12 text-center text-slate-400">No courses found.</td>
                    </tr>
                    <tr v-for="course in courses.data" :key="course.id" class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-900">{{ course.title }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ course.code }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">
                            {{ course.department?.name }}
                            <p class="text-xs text-slate-300">{{ course.department?.faculty?.name }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-center text-slate-600">{{ course.semester }}</td>
                        <td class="px-5 py-3.5 text-center text-slate-600">{{ course.credit_hours }}</td>
                        <td class="px-5 py-3.5 text-center">
                            <button @click="openTeachers(course)"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                                title="Manage teachers">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                {{ course.teachers_count }}
                            </button>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <button @click="openStudents(course)"
                                class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium text-slate-700 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                                title="Manage enrollment">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                {{ course.students_count }}
                            </button>
                        </td>
                        <td class="px-5 py-3.5 text-center">
                            <span :class="['px-2 py-0.5 rounded-full text-xs font-semibold', course.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500']">
                                {{ course.status }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(course)" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="destroy(course)" class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="courses.links?.length > 3" class="px-5 py-3 border-t border-slate-100 flex gap-1">
                <template v-for="link in courses.links" :key="link.label">
                    <component :is="link.url ? 'a' : 'span'"
                        v-html="link.label"
                        :href="link.url"
                        @click.prevent="link.url && router.get(link.url)"
                        :class="['px-3 py-1.5 rounded-lg text-xs transition-colors',
                            link.active ? 'text-white' : link.url ? 'text-slate-500 hover:bg-slate-100' : 'text-slate-300']"
                        :style="link.active ? 'background:#BC2739' : ''"/>
                </template>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal"/>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-slate-900 text-lg">{{ editTarget ? 'Edit Course' : 'New Course' }}</h3>
                        <button @click="closeModal" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Department <span class="text-red-500">*</span></label>
                            <select v-model="form.department_id"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': errors.department_id }">
                                <option value="">Select department…</option>
                                <option v-for="d in departments" :key="d.id" :value="d.id">{{ d.name }}</option>
                            </select>
                            <p v-if="errors.department_id" class="mt-1 text-xs text-red-500">{{ errors.department_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Title <span class="text-red-500">*</span></label>
                            <input v-model="form.title" type="text" placeholder="e.g. Data Structures & Algorithms"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': errors.title }"/>
                            <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                                <input v-model="form.code" type="text" placeholder="CS-301"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500"
                                    :class="{ 'border-red-400': errors.code }"/>
                                <p v-if="errors.code" class="mt-1 text-xs text-red-500">{{ errors.code }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Credits <span class="text-red-500">*</span></label>
                                <input v-model.number="form.credit_hours" type="number" min="1" max="6"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"/>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Semester</label>
                                <select v-model.number="form.semester"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option v-for="s in 8" :key="s" :value="s">Semester {{ s }}</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1.5">Status</label>
                                <select v-model="form.status"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="flex gap-3 pt-1">
                            <button type="button" @click="closeModal"
                                class="flex-1 h-10 rounded-xl text-sm font-medium bg-slate-100 text-slate-600">Cancel</button>
                            <button type="submit"
                                class="flex-1 h-10 rounded-xl text-sm font-semibold text-white"
                                style="background:#BC2739;">
                                {{ editTarget ? 'Update' : 'Create' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </Teleport>

        <!-- Manage Teachers Modal -->
        <Teleport to="body">
            <div v-if="showTeachers && manageCourse" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeTeachers"/>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3 px-6 pt-6 pb-5 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white"
                                 style="background:#BC2739;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-lg leading-tight font-heading">Manage Teachers</h3>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">{{ manageCourse.title }} · <span class="font-mono">{{ manageCourse.code }}</span></p>
                            </div>
                        </div>
                        <button @click="closeTeachers" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <div class="p-6">
                        <!-- Add teacher -->
                        <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Assign a teacher</label>
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <select v-model="selectedTeacher" :disabled="!availableTeachers.length"
                                    class="w-full h-10 pl-3 pr-9 rounded-xl border border-slate-200 text-sm bg-white appearance-none focus:outline-none focus:ring-2 focus:ring-primary-500 disabled:bg-slate-50 disabled:text-slate-400">
                                    <option value="">{{ availableTeachers.length ? 'Select a teacher…' : 'All teachers assigned' }}</option>
                                    <option v-for="t in availableTeachers" :key="t.id" :value="t.id">{{ t.name }} — {{ t.email }}</option>
                                </select>
                                <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <button @click="assignTeacher" :disabled="!selectedTeacher"
                                class="h-10 px-5 rounded-xl text-sm font-semibold text-white transition-opacity disabled:opacity-40 disabled:cursor-not-allowed"
                                style="background:#BC2739;">Assign</button>
                        </div>

                        <!-- Assigned teachers -->
                        <div class="flex items-center gap-2 mt-6 mb-3">
                            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Assigned</p>
                            <span class="inline-flex items-center justify-center min-w-5 h-5 px-1.5 rounded-full bg-slate-100 text-slate-600 text-xs font-bold">
                                {{ manageCourse.teachers?.length ?? 0 }}
                            </span>
                        </div>

                        <!-- Empty state -->
                        <div v-if="!manageCourse.teachers?.length"
                             class="flex flex-col items-center justify-center gap-2 py-8 rounded-xl border border-dashed border-slate-200 bg-slate-50/50">
                            <svg class="w-9 h-9 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                            </svg>
                            <p class="text-sm font-medium text-slate-500">No teachers assigned</p>
                            <p class="text-xs text-slate-400">Pick a teacher above to assign them.</p>
                        </div>

                        <!-- List -->
                        <div v-else class="space-y-2 max-h-64 overflow-y-auto -mx-1 px-1">
                            <div v-for="t in manageCourse.teachers" :key="t.id"
                                class="flex items-center justify-between gap-3 p-2.5 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors group">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                                         style="background:linear-gradient(135deg,#BC2739,#e05a6b);">
                                        {{ t.name?.charAt(0)?.toUpperCase() }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-slate-800 truncate">{{ t.name }}</p>
                                        <p class="text-xs text-slate-400 truncate">{{ t.email }}</p>
                                    </div>
                                </div>
                                <button @click="removeTeacher(t.id)"
                                    class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors flex-shrink-0 opacity-0 group-hover:opacity-100"
                                    title="Remove">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Manage Enrollment Modal -->
        <Teleport to="body">
            <div v-if="showStudents" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="closeStudents"/>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col max-h-[85vh] overflow-hidden">
                    <!-- Header -->
                    <div class="flex items-start justify-between gap-3 px-6 pt-6 pb-5 border-b border-slate-100">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 text-white" style="background:#BC2739;">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-slate-900 text-lg leading-tight font-heading">Manage Enrollment</h3>
                                <p class="text-xs text-slate-400 mt-0.5 truncate">
                                    <template v-if="studentManageCourse">{{ studentManageCourse.title }} · <span class="font-mono">{{ studentManageCourse.code }}</span></template>
                                </p>
                            </div>
                        </div>
                        <button @click="closeStudents" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 transition-colors flex-shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Search + select-all -->
                    <div class="px-6 pt-4 pb-3 border-b border-slate-100 space-y-3">
                        <div class="relative">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                            </svg>
                            <input v-model="studentSearch" placeholder="Search by name, ID or roll number…"
                                class="w-full h-10 pl-9 pr-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"/>
                        </div>
                        <div class="flex items-center justify-between">
                            <label v-if="filteredStudents.length" class="flex items-center gap-2 text-xs font-medium text-slate-600 cursor-pointer select-none">
                                <input type="checkbox" :checked="allFilteredSelected" @change="toggleAllFiltered"
                                    class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500" style="accent-color:#BC2739;"/>
                                Select all{{ studentSearch ? ' (filtered)' : '' }}
                            </label>
                            <span class="text-xs text-slate-400">{{ selectedStudents.size }} selected</span>
                        </div>
                    </div>

                    <!-- List -->
                    <div class="flex-1 overflow-y-auto px-3 py-3">
                        <div v-if="loadingStudents" class="flex items-center justify-center py-12 text-slate-400">
                            <svg class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                        </div>
                        <div v-else-if="!enrollList.length" class="flex flex-col items-center justify-center gap-2 py-10 text-center">
                            <p class="text-sm font-medium text-slate-500">No students in this department</p>
                            <p class="text-xs text-slate-400">Add students to {{ studentManageCourse?.department?.name || 'this department' }} first.</p>
                        </div>
                        <div v-else-if="!filteredStudents.length" class="py-10 text-center text-sm text-slate-400">No students match “{{ studentSearch }}”.</div>
                        <div v-else class="space-y-1">
                            <label v-for="s in filteredStudents" :key="s.id"
                                class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 cursor-pointer select-none transition-colors">
                                <input type="checkbox" :checked="selectedStudents.has(s.id)" @change="toggleStudent(s.id)"
                                    class="w-4 h-4 rounded border-slate-300 flex-shrink-0" style="accent-color:#BC2739;"/>
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0"
                                     style="background:linear-gradient(135deg,#BC2739,#e05a6b);">
                                    {{ s.name?.charAt(0)?.toUpperCase() }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="text-sm font-medium text-slate-800 truncate">{{ s.name }}</p>
                                    <p class="text-xs text-slate-400 truncate">
                                        <span class="font-mono">{{ s.student_id }}</span> · Sem {{ s.semester }}
                                    </p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button @click="closeStudents" class="h-10 px-4 rounded-xl text-sm font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors">Cancel</button>
                        <button @click="saveEnrollment" :disabled="savingStudents || loadingStudents"
                            class="h-10 px-5 rounded-xl text-sm font-semibold text-white transition-opacity disabled:opacity-50 flex items-center gap-2"
                            style="background:#BC2739;">
                            <svg v-if="savingStudents" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            {{ savingStudents ? 'Saving…' : 'Save Enrollment' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>
