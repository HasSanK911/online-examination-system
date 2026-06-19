<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    departments: { type: Object, default: () => ({ data: [], total: 0 }) },
    faculties:   { type: Array,  default: () => [] },
    filters:     { type: Object, default: () => ({}) },
})

const search   = ref(props.filters.search ?? '')
const fId      = ref(props.filters.faculty_id ?? '')

const showModal  = ref(false)
const editTarget = ref(null)

const form = reactive({ faculty_id: '', name: '', code: '', description: '' })
const errors = reactive({})

function openCreate() {
    Object.assign(form, { faculty_id: '', name: '', code: '', description: '' })
    Object.keys(errors).forEach(k => delete errors[k])
    editTarget.value = null
    showModal.value = true
}
function openEdit(dept) {
    Object.assign(form, { faculty_id: dept.faculty_id, name: dept.name, code: dept.code, description: dept.description ?? '' })
    Object.keys(errors).forEach(k => delete errors[k])
    editTarget.value = dept
    showModal.value = true
}
function closeModal() { showModal.value = false }

function doSearch() {
    router.get('/admin/departments', { search: search.value || undefined, faculty_id: fId.value || undefined }, { preserveState: true })
}

function submit() {
    Object.keys(errors).forEach(k => delete errors[k])
    const url    = editTarget.value ? `/admin/departments/${editTarget.value.id}` : '/admin/departments'
    const method = editTarget.value ? 'put' : 'post'
    router[method](url, form, {
        onError: e => Object.assign(errors, e),
        onSuccess: () => closeModal(),
    })
}

function destroy(dept) {
    if (confirm(`Delete department "${dept.name}"?`)) {
        router.delete(`/admin/departments/${dept.id}`)
    }
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-slate-900 font-heading">Departments</h1>
                    <p class="text-sm text-slate-400 mt-0.5">{{ departments.total ?? departments.data?.length }} departments</p>
                </div>
                <button @click="openCreate"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl text-sm font-semibold text-white"
                    style="background:#BC2739;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    New Department
                </button>
            </div>
        </template>

        <!-- Filters -->
        <div class="flex gap-3 mb-5 flex-wrap">
            <input v-model="search" @keyup.enter="doSearch" type="text" placeholder="Search name or code…"
                class="h-9 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 w-56"/>
            <select v-model="fId" @change="doSearch"
                class="h-9 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">All Faculties</option>
                <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name }}</option>
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
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Department</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Faculty</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Students</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold text-slate-500 uppercase">Courses</th>
                        <th class="px-5 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!departments.data?.length">
                        <td colspan="5" class="px-5 py-12 text-center text-slate-400">No departments found.</td>
                    </tr>
                    <tr v-for="dept in departments.data" :key="dept.id" class="hover:bg-slate-50 transition-colors">
                        <td class="px-5 py-3.5">
                            <p class="font-semibold text-slate-900">{{ dept.name }}</p>
                            <p class="text-xs font-mono text-slate-400">{{ dept.code }}</p>
                        </td>
                        <td class="px-5 py-3.5 text-slate-500">{{ dept.faculty?.name }}</td>
                        <td class="px-5 py-3.5 text-center text-slate-700 font-medium">{{ dept.students_count }}</td>
                        <td class="px-5 py-3.5 text-center text-slate-700 font-medium">{{ dept.courses_count }}</td>
                        <td class="px-5 py-3.5 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button @click="openEdit(dept)" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100 hover:text-slate-600 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button @click="destroy(dept)" class="p-1.5 rounded-lg text-slate-400 hover:bg-red-50 hover:text-red-500 transition-colors">
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
            <div v-if="departments.links?.length > 3" class="px-5 py-3 border-t border-slate-100 flex gap-1">
                <template v-for="link in departments.links" :key="link.label">
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

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-black/40 backdrop-blur-sm" @click="closeModal"/>
                <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h3 class="font-bold text-slate-900 text-lg">{{ editTarget ? 'Edit Department' : 'New Department' }}</h3>
                        <button @click="closeModal" class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Faculty <span class="text-red-500">*</span></label>
                            <select v-model="form.faculty_id"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': errors.faculty_id }">
                                <option value="">Select faculty…</option>
                                <option v-for="f in faculties" :key="f.id" :value="f.id">{{ f.name }}</option>
                            </select>
                            <p v-if="errors.faculty_id" class="mt-1 text-xs text-red-500">{{ errors.faculty_id }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Name <span class="text-red-500">*</span></label>
                            <input v-model="form.name" type="text" placeholder="e.g. Computer Science"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': errors.name }"/>
                            <p v-if="errors.name" class="mt-1 text-xs text-red-500">{{ errors.name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Code <span class="text-red-500">*</span></label>
                            <input v-model="form.code" type="text" placeholder="e.g. CS"
                                class="w-full h-10 px-3 rounded-xl border border-slate-200 text-sm font-mono focus:outline-none focus:ring-2 focus:ring-primary-500"
                                :class="{ 'border-red-400': errors.code }"/>
                            <p v-if="errors.code" class="mt-1 text-xs text-red-500">{{ errors.code }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Description</label>
                            <textarea v-model="form.description" rows="2"
                                class="w-full px-3 py-2.5 rounded-xl border border-slate-200 text-sm resize-none focus:outline-none focus:ring-2 focus:ring-primary-500"/>
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
