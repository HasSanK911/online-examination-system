<script setup>
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'

const props = defineProps({
    logs:        { type: Object, default: () => ({ data: [] }) },
    event_types: { type: Array,  default: () => [] },
    users:       { type: Array,  default: () => [] },
    filters:     { type: Object, default: () => ({}) },
})

const f = ref({ ...props.filters })
const expandedId = ref(null)

function doSearch() {
    router.get('/admin/audit-logs', {
        event:   f.value.event   || undefined,
        user_id: f.value.user_id || undefined,
        from:    f.value.from    || undefined,
        to:      f.value.to      || undefined,
        ip:      f.value.ip      || undefined,
    }, { preserveState: true })
}

function clear() {
    f.value = {}
    doSearch()
}

const eventColors = {
    login:               'bg-emerald-50 text-emerald-700',
    logout:              'bg-slate-100 text-slate-600',
    exam_start:          'bg-sky-50 text-sky-700',
    exam_submit:         'bg-blue-50 text-blue-700',
    exam_auto_submitted: 'bg-amber-50 text-amber-700',
    suspicious_activity: 'bg-red-50 text-red-700',
    result_published:    'bg-violet-50 text-violet-700',
}

function colorFor(event) {
    return eventColors[event] ?? 'bg-slate-100 text-slate-600'
}

function fmt(dt) {
    if (!dt) return '—'
    return new Date(dt).toLocaleString('en-US', { dateStyle: 'short', timeStyle: 'medium' })
}

function toggle(id) {
    expandedId.value = expandedId.value === id ? null : id
}
</script>

<template>
    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Audit Logs</h1>
                <p class="text-sm text-slate-400 mt-0.5">{{ logs.total ?? logs.data?.length }} log entries</p>
            </div>
        </template>

        <!-- Filters -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-5">
            <div class="grid grid-cols-2 md:grid-cols-5 gap-3">
                <select v-model="f.event"
                    class="h-9 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">All Events</option>
                    <option v-for="e in event_types" :key="e" :value="e">{{ e }}</option>
                </select>
                <select v-model="f.user_id"
                    class="h-9 px-3 rounded-xl border border-slate-200 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">All Users</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                </select>
                <input v-model="f.from" type="date" placeholder="From"
                    class="h-9 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"/>
                <input v-model="f.to" type="date" placeholder="To"
                    class="h-9 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"/>
                <input v-model="f.ip" type="text" placeholder="IP address…"
                    class="h-9 px-3 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500"/>
            </div>
            <div class="flex gap-2 mt-3">
                <button @click="doSearch" class="h-9 px-4 rounded-xl text-sm font-medium text-white" style="background:#BC2739;">Search</button>
                <button @click="clear" class="h-9 px-4 rounded-xl text-sm font-medium text-slate-600 bg-slate-100">Clear</button>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Event</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">User</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Subject</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">IP</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Time</th>
                        <th class="px-5 py-3 w-8"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr v-if="!logs.data?.length">
                        <td colspan="6" class="px-5 py-12 text-center text-slate-400">No log entries found.</td>
                    </tr>
                    <template v-for="log in logs.data" :key="log.id">
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer" @click="toggle(log.id)">
                            <td class="px-5 py-3">
                                <span :class="['px-2 py-0.5 rounded-md text-xs font-medium', colorFor(log.event)]">{{ log.event }}</span>
                            </td>
                            <td class="px-5 py-3">
                                <p class="text-slate-800 font-medium">{{ log.user?.name ?? 'System' }}</p>
                                <p class="text-xs text-slate-400">{{ log.user?.email }}</p>
                            </td>
                            <td class="px-5 py-3 text-slate-500 text-xs">
                                {{ log.auditable_type }} #{{ log.auditable_id }}
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ log.ip_address }}</td>
                            <td class="px-5 py-3 text-xs text-slate-500">{{ fmt(log.created_at) }}</td>
                            <td class="px-5 py-3">
                                <svg :class="['w-4 h-4 text-slate-400 transition-transform', expandedId === log.id ? 'rotate-180' : '']"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </td>
                        </tr>
                        <tr v-if="expandedId === log.id">
                            <td colspan="6" class="px-5 py-4 bg-slate-50">
                                <div class="grid grid-cols-2 gap-4 text-xs">
                                    <div v-if="log.old_values">
                                        <p class="font-semibold text-slate-500 mb-1 uppercase">Before</p>
                                        <pre class="bg-white rounded-xl p-3 text-slate-600 overflow-auto max-h-32 border border-slate-200">{{ JSON.stringify(log.old_values, null, 2) }}</pre>
                                    </div>
                                    <div v-if="log.new_values">
                                        <p class="font-semibold text-slate-500 mb-1 uppercase">After</p>
                                        <pre class="bg-white rounded-xl p-3 text-slate-600 overflow-auto max-h-32 border border-slate-200">{{ JSON.stringify(log.new_values, null, 2) }}</pre>
                                    </div>
                                    <div class="col-span-2">
                                        <p class="font-semibold text-slate-500 mb-1 uppercase">User Agent</p>
                                        <p class="text-slate-500 break-all">{{ log.user_agent }}</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="logs.links?.length > 3" class="px-5 py-3 border-t border-slate-100 flex gap-1">
                <template v-for="link in logs.links" :key="link.label">
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
    </AppLayout>
</template>
