<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    courses:     { type: Object, default: () => ({ data: [], total: 0 }) },
    departments: { type: Array,  default: () => [] },
    filters:     { type: Object, default: () => ({}) },
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
                        <td class="px-5 py-3.5 text-center text-slate-700 font-medium">{{ course.teachers_count }}</td>
                        <td class="px-5 py-3.5 text-center text-slate-700 font-medium">{{ course.students_count }}</td>
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
    </AppLayout>
</template>
