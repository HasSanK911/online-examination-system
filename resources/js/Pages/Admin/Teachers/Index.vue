<template>
  <AppLayout>
    <template #header>
      <div>
        <h1 class="text-xl font-bold text-slate-900 font-heading">Teachers</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage teaching staff accounts</p>
      </div>
    </template>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-slate-900">{{ teachers.total }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total Teachers</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-emerald-600">{{ teachers.data.filter(t => t.is_active).length }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Active</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-slate-400">{{ teachers.data.filter(t => !t.is_active).length }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Inactive</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-primary-600">{{ teachers.data.reduce((s,t) => s + (t.taught_courses_count||0), 0) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Courses Assigned</p>
      </div>
    </div>

    <!-- Toolbar -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-5">
      <div class="relative flex-1 max-w-sm">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
        </svg>
        <input
          v-model="searchQuery"
          @keydown.enter="search"
          placeholder="Search by name or email…"
          class="input pl-9"
        />
      </div>
      <select v-model="statusFilter" @change="search" class="input w-36">
        <option value="">All Status</option>
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
      <button @click="openModal()" class="btn-primary ml-auto sm:ml-0 whitespace-nowrap">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Add Teacher
      </button>
    </div>

    <!-- Table card -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Teacher</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Email</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Courses</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="teachers.data.length === 0">
            <td colspan="5" class="px-5 py-12 text-center">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/>
                </svg>
                <p class="text-sm font-medium text-slate-500">No teachers found</p>
                <p class="text-xs text-slate-400">Add the first teacher to get started.</p>
              </div>
            </td>
          </tr>
          <tr v-for="teacher in teachers.data" :key="teacher.id"
              class="hover:bg-slate-50 transition-colors group">
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <img :src="teacher.avatar_url" :alt="teacher.name"
                     class="w-9 h-9 rounded-full object-cover flex-shrink-0 bg-slate-100" />
                <div>
                  <p class="font-semibold text-slate-900">{{ teacher.name }}</p>
                  <p class="text-xs text-slate-400 sm:hidden">{{ teacher.email }}</p>
                </div>
              </div>
            </td>
            <td class="px-5 py-4 text-slate-500 text-sm hidden sm:table-cell">{{ teacher.email }}</td>
            <td class="px-5 py-4 text-center">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-sm font-semibold">
                {{ teacher.taught_courses_count }}
              </span>
            </td>
            <td class="px-5 py-4">
              <span :class="[
                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold',
                teacher.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
              ]">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="teacher.is_active ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                {{ teacher.is_active ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-5 py-4 text-right">
              <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openModal(teacher)"
                  class="p-2 rounded-xl text-slate-500 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                  title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="deleteTeacher(teacher)"
                  class="p-2 rounded-xl text-slate-500 hover:bg-red-50 hover:text-red-600 transition-colors"
                  title="Delete">
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
      <div v-if="teachers.last_page > 1"
           class="px-5 py-4 border-t border-slate-100 flex items-center justify-between text-sm">
        <p class="text-slate-400 text-xs">Showing {{ teachers.from }}–{{ teachers.to }} of {{ teachers.total }}</p>
        <div class="flex gap-1.5">
          <Link
            v-for="link in teachers.links"
            :key="link.label"
            :href="link.url || '#'"
            :class="[
              'px-3 py-1.5 rounded-lg text-xs font-medium transition-colors',
              link.active ? 'bg-primary-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100',
              !link.url ? 'opacity-40 pointer-events-none' : ''
            ]"
            v-html="link.label"
          />
        </div>
      </div>
    </div>

    <!-- Add/Edit Modal -->
    <Teleport to="body">
      <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm" @click="showModal = false" />
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6 z-10">
          <div class="flex items-center justify-between mb-5">
            <h3 class="text-lg font-bold text-slate-900 font-heading">
              {{ form.id ? 'Edit Teacher' : 'Add Teacher' }}
            </h3>
            <button @click="showModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="label">Full Name *</label>
              <input v-model="form.name" class="input" placeholder="e.g. Dr. Sarah Ahmed" required />
              <p v-if="$page.props.errors?.name" class="error">{{ $page.props.errors.name }}</p>
            </div>
            <div>
              <label class="label">Email *</label>
              <input v-model="form.email" type="email" class="input" placeholder="teacher@university.edu" required />
              <p v-if="$page.props.errors?.email" class="error">{{ $page.props.errors.email }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">{{ form.id ? 'New Password' : 'Password *' }}</label>
                <input v-model="form.password" type="password" class="input" :placeholder="form.id ? 'Leave blank to keep' : '••••••••'" :required="!form.id" autocomplete="new-password" />
                <p v-if="$page.props.errors?.password" class="error">{{ $page.props.errors.password }}</p>
              </div>
              <div>
                <label class="label">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" class="input" placeholder="••••••••" :required="!form.id || !!form.password" autocomplete="new-password" />
              </div>
            </div>
            <div>
              <label class="label">Status</label>
              <select v-model="form.is_active" class="input">
                <option :value="true">Active</option>
                <option :value="false">Inactive</option>
              </select>
            </div>
            <div class="flex gap-3 pt-2">
              <button type="button" @click="showModal = false" class="btn-ghost flex-1">Cancel</button>
              <button type="submit" :disabled="processing" class="btn-primary flex-1">
                <svg v-if="processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                </svg>
                {{ processing ? 'Saving…' : (form.id ? 'Update' : 'Create') }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </AppLayout>
</template>

<script setup>
import { ref } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ teachers: Object, filters: Object })

const showModal   = ref(false)
const processing  = ref(false)
const searchQuery = ref(props.filters?.search || '')
const statusFilter= ref(props.filters?.status || '')

const emptyForm = () => ({ id: null, name: '', email: '', password: '', password_confirmation: '', is_active: true })
const form = ref(emptyForm())

function openModal(teacher = null) {
  form.value = teacher
    ? { id: teacher.id, name: teacher.name, email: teacher.email, password: '', password_confirmation: '', is_active: !!teacher.is_active }
    : emptyForm()
  showModal.value = true
}

function submit() {
  processing.value = true
  const url    = form.value.id ? `/admin/teachers/${form.value.id}` : '/admin/teachers'
  const method = form.value.id ? 'put' : 'post'
  router[method](url, form.value, {
    onSuccess: () => { showModal.value = false; processing.value = false },
    onError:   () => { processing.value = false },
  })
}

function deleteTeacher(teacher) {
  if (confirm(`Delete "${teacher.name}"? This cannot be undone.`)) {
    router.delete(`/admin/teachers/${teacher.id}`)
  }
}

function search() {
  router.get('/admin/teachers', { search: searchQuery.value, status: statusFilter.value }, { preserveState: true, replace: true })
}
</script>
