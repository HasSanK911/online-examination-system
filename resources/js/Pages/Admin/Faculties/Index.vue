<template>
  <AppLayout>
    <template #header>
      <div>
        <h1 class="text-xl font-bold text-slate-900 font-heading">Faculties</h1>
        <p class="text-sm text-slate-500 mt-0.5">Manage university faculties</p>
      </div>
    </template>

    <!-- Stats row -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-slate-900">{{ faculties.total }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Total Faculties</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-emerald-600">{{ faculties.data.filter(f => f.status === 'active').length }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Active</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-slate-400">{{ faculties.data.filter(f => f.status === 'inactive').length }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Inactive</p>
      </div>
      <div class="bg-white rounded-2xl border border-slate-200 p-4">
        <p class="text-2xl font-bold text-primary-600">{{ faculties.data.reduce((s,f) => s + (f.departments_count||0), 0) }}</p>
        <p class="text-xs text-slate-400 mt-0.5">Departments</p>
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
          placeholder="Search by name or code…"
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
        Add Faculty
      </button>
    </div>

    <!-- Table card -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead>
          <tr class="border-b border-slate-100 bg-slate-50">
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Faculty</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Code</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider hidden sm:table-cell">Dean</th>
            <th class="text-center px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Departments</th>
            <th class="text-left px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
            <th class="text-right px-5 py-3 text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-if="faculties.data.length === 0">
            <td colspan="6" class="px-5 py-12 text-center">
              <div class="flex flex-col items-center gap-2">
                <svg class="w-10 h-10 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <p class="text-sm font-medium text-slate-500">No faculties found</p>
                <p class="text-xs text-slate-400">Add the first faculty to get started.</p>
              </div>
            </td>
          </tr>
          <tr v-for="faculty in faculties.data" :key="faculty.id"
              class="hover:bg-slate-50 transition-colors group">
            <td class="px-5 py-4">
              <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-primary-50 flex items-center justify-center flex-shrink-0">
                  <span class="text-sm font-bold text-primary-600">{{ faculty.code?.charAt(0) }}</span>
                </div>
                <p class="font-semibold text-slate-900">{{ faculty.name }}</p>
              </div>
            </td>
            <td class="px-5 py-4">
              <span class="font-mono text-xs bg-slate-100 text-slate-700 px-2 py-1 rounded-lg">{{ faculty.code }}</span>
            </td>
            <td class="px-5 py-4 text-slate-500 text-sm hidden sm:table-cell">{{ faculty.dean_name || '—' }}</td>
            <td class="px-5 py-4 text-center">
              <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-slate-100 text-slate-700 text-sm font-semibold">
                {{ faculty.departments_count }}
              </span>
            </td>
            <td class="px-5 py-4">
              <span :class="[
                'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold',
                faculty.status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-500'
              ]">
                <span class="w-1.5 h-1.5 rounded-full"
                  :class="faculty.status === 'active' ? 'bg-emerald-500' : 'bg-slate-400'"></span>
                {{ faculty.status === 'active' ? 'Active' : 'Inactive' }}
              </span>
            </td>
            <td class="px-5 py-4 text-right">
              <div class="flex items-center justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                <button @click="openModal(faculty)"
                  class="p-2 rounded-xl text-slate-500 hover:bg-primary-50 hover:text-primary-600 transition-colors"
                  title="Edit">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                  </svg>
                </button>
                <button @click="deleteFaculty(faculty)"
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
      <div v-if="faculties.last_page > 1"
           class="px-5 py-4 border-t border-slate-100 flex items-center justify-between text-sm">
        <p class="text-slate-400 text-xs">Showing {{ faculties.from }}–{{ faculties.to }} of {{ faculties.total }}</p>
        <div class="flex gap-1.5">
          <Link
            v-for="link in faculties.links"
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
              {{ form.id ? 'Edit Faculty' : 'Add Faculty' }}
            </h3>
            <button @click="showModal = false" class="p-1.5 rounded-xl text-slate-400 hover:bg-slate-100 transition-colors">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
          <form @submit.prevent="submit" class="space-y-4">
            <div>
              <label class="label">Faculty Name *</label>
              <input v-model="form.name" class="input" placeholder="e.g. Faculty of Computing" required />
              <p v-if="$page.props.errors?.name" class="error">{{ $page.props.errors.name }}</p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="label">Code *</label>
                <input v-model="form.code" class="input font-mono uppercase" placeholder="e.g. FCS" required />
                <p v-if="$page.props.errors?.code" class="error">{{ $page.props.errors.code }}</p>
              </div>
              <div>
                <label class="label">Status</label>
                <select v-model="form.status" class="input">
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
            <div>
              <label class="label">Dean Name</label>
              <input v-model="form.dean_name" class="input" placeholder="Full name of dean (optional)" />
            </div>
            <div>
              <label class="label">Email</label>
              <input v-model="form.email" type="email" class="input" placeholder="faculty@university.edu (optional)" />
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
import { Link, router, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({ faculties: Object, filters: Object })

const showModal   = ref(false)
const processing  = ref(false)
const searchQuery = ref(props.filters?.search || '')
const statusFilter= ref(props.filters?.status || '')

const form = ref({ id: null, name: '', code: '', dean_name: '', email: '', status: 'active' })

function openModal(faculty = null) {
  form.value = faculty
    ? { id: faculty.id, name: faculty.name, code: faculty.code, dean_name: faculty.dean_name || '', email: faculty.email || '', status: faculty.status }
    : { id: null, name: '', code: '', dean_name: '', email: '', status: 'active' }
  showModal.value = true
}

function submit() {
  processing.value = true
  const url    = form.value.id ? `/admin/faculties/${form.value.id}` : '/admin/faculties'
  const method = form.value.id ? 'put' : 'post'
  router[method](url, form.value, {
    onSuccess: () => { showModal.value = false; processing.value = false },
    onError:   () => { processing.value = false },
  })
}

function deleteFaculty(faculty) {
  if (confirm(`Delete "${faculty.name}"? This cannot be undone.`)) {
    router.delete(`/admin/faculties/${faculty.id}`)
  }
}

function search() {
  router.get('/admin/faculties', { search: searchQuery.value, status: statusFilter.value }, { preserveState: true, replace: true })
}
</script>
