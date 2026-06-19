<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats:          Object,
    recentActivity: Array,
    upcomingExams:  Array,
});

const statCards = [
    { label: 'Total Students',     value: props.stats?.total_students    ?? 0, sub: `${props.stats?.active_students ?? 0} active`,   light: 'bg-indigo-50 text-indigo-600', icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { label: 'Total Exams',        value: props.stats?.total_exams       ?? 0, sub: `${props.stats?.active_exams ?? 0} live · ${props.stats?.scheduled_exams ?? 0} scheduled`, light: 'bg-violet-50 text-violet-600', icon: 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4' },
    { label: 'Courses',            value: props.stats?.total_courses     ?? 0, sub: 'Active courses',                                light: 'bg-emerald-50 text-emerald-600', icon: 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253' },
    { label: 'Departments',        value: props.stats?.total_departments ?? 0, sub: 'Across all faculties',                         light: 'bg-amber-50 text-amber-600', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
    { label: 'Published Results',  value: props.stats?.published_results ?? 0, sub: `${props.stats?.pending_results ?? 0} pending`, light: 'bg-rose-50 text-rose-600', icon: 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
    { label: 'Users',              value: props.stats?.total_users       ?? 0, sub: 'All roles combined',                           light: 'bg-sky-50 text-sky-600', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
];

const eventDot = {
    login:              'bg-slate-400',
    exam_created:       'bg-indigo-500',
    result_publish:     'bg-emerald-500',
    student_created:    'bg-violet-500',
    user_updated:       'bg-amber-500',
    faculty_created:    'bg-sky-500',
    question_added:     'bg-rose-400',
    department_created: 'bg-teal-500',
};
</script>

<template>
    <Head title="Admin Dashboard" />
    <AppLayout>
        <template #header>
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Dashboard</h1>
                <p class="text-sm text-slate-500 mt-0.5">Welcome back! Here's what's happening today.</p>
            </div>
        </template>

        <!-- Stats grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
            <div v-for="card in statCards" :key="card.label"
                class="bg-white rounded-2xl border border-slate-200 p-4 hover:shadow-md hover:border-slate-300 transition-all">
                <div :class="['w-10 h-10 rounded-xl flex items-center justify-center mb-3', card.light]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                    </svg>
                </div>
                <p class="text-2xl font-bold text-slate-900">{{ Number(card.value).toLocaleString() }}</p>
                <p class="text-xs font-semibold text-slate-600 mt-0.5">{{ card.label }}</p>
                <p class="text-[11px] text-slate-400 mt-0.5">{{ card.sub }}</p>
            </div>
        </div>

        <!-- Alert: pending evaluations -->
        <div v-if="(stats?.pending_results ?? 0) > 0"
            class="mb-6 flex items-center gap-3 p-4 bg-amber-50 border border-amber-200 rounded-2xl">
            <div class="w-9 h-9 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-semibold text-amber-800">{{ stats.pending_results }} result(s) awaiting evaluation</p>
                <p class="text-xs text-amber-600">Descriptive answers need teacher review before publishing.</p>
            </div>
            <Link href="/admin/exams" class="text-xs font-semibold text-amber-700 hover:underline whitespace-nowrap">Review →</Link>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            <!-- Left: exams + quick links -->
            <div class="xl:col-span-2 space-y-5">

                <!-- Upcoming Exams -->
                <div class="bg-white rounded-2xl border border-slate-200">
                    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 bg-blue-50 rounded-lg flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <h2 class="text-sm font-bold text-slate-900">Upcoming Exams</h2>
                        </div>
                        <Link href="/admin/exams" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800">View all →</Link>
                    </div>
                    <div class="p-4">
                        <div v-if="!upcomingExams?.length" class="py-10 text-center">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-sm text-slate-400">No upcoming exams scheduled</p>
                        </div>
                        <div v-else class="space-y-2">
                            <div v-for="exam in upcomingExams" :key="exam.id"
                                class="flex items-center gap-3 p-3 bg-slate-50 hover:bg-indigo-50 rounded-xl transition-colors">
                                <div class="w-9 h-9 bg-white border border-slate-200 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-4 h-4 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-slate-900 truncate">{{ exam.title }}</p>
                                    <p class="text-xs text-slate-500">{{ exam.course }} · {{ exam.duration }}</p>
                                </div>
                                <div class="text-right flex-shrink-0">
                                    <p class="text-xs font-semibold text-slate-700">{{ exam.start_time }}</p>
                                    <span class="inline-block text-[10px] font-semibold bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full mt-0.5">Scheduled</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick navigation tiles -->
                <div class="grid grid-cols-3 gap-4">
                    <Link href="/admin/students"
                        class="bg-white rounded-2xl border border-slate-200 p-4 text-center hover:border-indigo-300 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 bg-indigo-100 group-hover:bg-indigo-200 rounded-xl flex items-center justify-center mx-auto mb-2 transition-colors">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-800">Students</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ stats?.total_students ?? 0 }} enrolled</p>
                    </Link>
                    <Link href="/admin/faculties"
                        class="bg-white rounded-2xl border border-slate-200 p-4 text-center hover:border-violet-300 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 bg-violet-100 group-hover:bg-violet-200 rounded-xl flex items-center justify-center mx-auto mb-2 transition-colors">
                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-800">Faculties</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">{{ stats?.total_departments ?? 0 }} depts</p>
                    </Link>
                    <Link href="/admin/analytics"
                        class="bg-white rounded-2xl border border-slate-200 p-4 text-center hover:border-emerald-300 hover:shadow-md transition-all group">
                        <div class="w-10 h-10 bg-emerald-100 group-hover:bg-emerald-200 rounded-xl flex items-center justify-center mx-auto mb-2 transition-colors">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <p class="text-sm font-bold text-slate-800">Analytics</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">OLAP reports</p>
                    </Link>
                </div>
            </div>

            <!-- Right: Activity Feed -->
            <div class="bg-white rounded-2xl border border-slate-200">
                <div class="px-5 py-4 border-b border-slate-100 flex items-center gap-2.5">
                    <div class="w-8 h-8 bg-slate-100 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-900">Recent Activity</h2>
                </div>
                <div class="p-4">
                    <div v-if="!recentActivity?.length" class="py-10 text-center text-slate-400">
                        <p class="text-sm">No activity yet.</p>
                    </div>
                    <div v-else class="space-y-0 max-h-[480px] overflow-y-auto">
                        <div v-for="log in recentActivity" :key="log.id" class="flex gap-3 group">
                            <div class="flex flex-col items-center pt-1.5">
                                <div :class="['w-2 h-2 rounded-full flex-shrink-0', eventDot[log.event] ?? 'bg-slate-400']"></div>
                                <div class="w-px flex-1 bg-slate-100 my-1"></div>
                            </div>
                            <div class="pb-3 flex-1 min-w-0">
                                <div class="flex items-baseline justify-between gap-1">
                                    <p class="text-xs font-semibold text-slate-800 truncate">{{ log.user }}</p>
                                    <p class="text-[10px] text-slate-400 whitespace-nowrap">{{ log.created_at }}</p>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5 leading-snug">{{ log.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
