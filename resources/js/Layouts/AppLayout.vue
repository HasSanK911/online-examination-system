<template>
  <div class="min-h-screen flex bg-slate-100 font-body">

    <!-- ── Sidebar ──────────────────────────────────────────────────────── -->
    <aside :class="[
      'fixed inset-y-0 left-0 z-50 flex flex-col w-64 transition-transform duration-300',
      sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]" style="background: #0f172a;">

      <!-- Brand -->
      <div class="flex items-center gap-3 px-5 h-16 border-b border-white/5">
        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
             style="background:#BC2739;">
          <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
          </svg>
        </div>
        <div>
          <p class="text-base font-black text-white leading-none tracking-tight font-heading">UNIXAM</p>
          <p class="text-[10px] text-slate-500 mt-0.5 uppercase tracking-widest">Examination System</p>
        </div>
      </div>

      <!-- Role badge -->
      <div class="px-4 py-3 border-b border-white/5">
        <span :class="['inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold', roleBadge.cls]">
          <span class="w-1.5 h-1.5 rounded-full animate-pulse" :class="roleBadge.dot"></span>
          {{ roleBadge.label }}
        </span>
      </div>

      <!-- Nav -->
      <nav class="flex-1 overflow-y-auto scrollbar-hide px-3 py-3 space-y-0.5">
        <template v-for="item in navItems" :key="item.label ?? item.section">
          <p v-if="item.section"
             class="px-3 pt-5 pb-2 text-[10px] font-bold uppercase tracking-widest text-slate-500 first:pt-2">
            {{ item.section }}
          </p>
          <Link
            v-else
            :href="item.href"
            :class="[
              'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all duration-150 group',
              isActive(item.href)
                ? 'text-white'
                : 'text-slate-400 hover:text-white hover:bg-white/5'
            ]"
            :style="isActive(item.href) ? 'background: rgba(188,39,57,0.2);' : ''"
          >
            <span
              :class="[
                'w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 transition-colors',
                isActive(item.href) ? 'text-white' : 'text-slate-500 group-hover:text-slate-300'
              ]"
              :style="isActive(item.href) ? 'background:#BC2739;' : ''"
              v-html="item.icon"
            ></span>
            <span>{{ item.label }}</span>
            <span v-if="isActive(item.href)"
              class="ml-auto w-1.5 h-1.5 rounded-full"
              style="background:#BC2739;"></span>
          </Link>
        </template>
      </nav>

      <!-- User footer -->
      <div class="p-3 border-t border-white/5">
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/5 transition-colors">
          <div class="w-9 h-9 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
               style="background:linear-gradient(135deg,#BC2739,#e05a6b);">
            {{ $page.props.auth.user?.name?.charAt(0) ?? 'U' }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-sm font-semibold text-white truncate">{{ $page.props.auth.user?.name }}</p>
            <p class="text-[11px] text-slate-500 truncate">{{ $page.props.auth.user?.email }}</p>
          </div>
          <Link href="/logout" method="post" as="button"
            class="p-1.5 rounded-lg text-slate-500 hover:text-red-400 hover:bg-red-500/10 transition-colors" title="Sign out">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
          </Link>
        </div>
      </div>
    </aside>

    <!-- ── Main ─────────────────────────────────────────────────────────── -->
    <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">

      <!-- Top navbar -->
      <header class="sticky top-0 z-40 h-16 flex items-center justify-between px-6 bg-white border-b border-slate-200 shadow-sm">
        <button @click="sidebarOpen = !sidebarOpen"
          class="lg:hidden p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
          </svg>
        </button>

        <div class="hidden lg:block flex-1">
          <slot name="header" />
        </div>
        <div class="lg:hidden text-lg font-black font-heading" style="color:#BC2739;">UNIXAM</div>

        <div class="flex items-center gap-2">
          <button @click="toggleDark" class="p-2 rounded-xl text-slate-500 hover:bg-slate-100 transition-colors">
            <svg v-if="isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
          </button>
        </div>
      </header>

      <!-- Mobile page header -->
      <div class="lg:hidden px-6 pt-5">
        <slot name="header" />
      </div>

      <!-- Flash -->
      <div v-if="$page.props.flash?.success"
           class="mx-6 mt-4 p-3.5 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm flex items-center gap-2.5">
        <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium">{{ $page.props.flash.success }}</span>
      </div>
      <div v-if="$page.props.flash?.error"
           class="mx-6 mt-4 p-3.5 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm flex items-center gap-2.5">
        <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
        </svg>
        <span class="font-medium">{{ $page.props.flash.error }}</span>
      </div>

      <!-- Content -->
      <main class="flex-1 p-6">
        <slot />
      </main>

      <footer class="px-6 py-3 border-t border-slate-200 flex items-center justify-between">
        <p class="text-xs text-slate-400">© 2026 UNIXAM · University Online Examination System</p>
        <p class="text-xs text-slate-400">v1.0.0</p>
      </footer>
    </div>

    <!-- Mobile overlay -->
    <div v-if="sidebarOpen" @click="sidebarOpen = false"
      class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"/>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const page        = usePage();
const sidebarOpen = ref(false);
const isDark      = ref(false);

onMounted(() => {
  const saved = localStorage.getItem('theme');
  isDark.value = saved === 'dark';
  document.documentElement.classList.toggle('dark', isDark.value);
});

function toggleDark() {
  isDark.value = !isDark.value;
  document.documentElement.classList.toggle('dark', isDark.value);
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
}

function isActive(href) {
  const path = page.url.split('?')[0].replace(/\/$/, '') || '/';
  const target = href.replace(/\/$/, '') || '/';
  // Dashboards & top-level account pages match exactly so they don't stay lit on sub-routes
  if (/\/dashboard$/.test(target) || target === '/profile') {
    return path === target;
  }
  // Section links stay active on their own index and any nested route
  return path === target || path.startsWith(target + '/');
}

const roles = computed(() => page.props.auth?.user?.roles?.map(r => r.name) ?? []);

const roleBadge = computed(() => {
  if (roles.value.includes('super_admin'))     return { label: 'Super Admin',     cls: 'bg-red-500/20 text-red-300',     dot: 'bg-red-400'     };
  if (roles.value.includes('exam_controller')) return { label: 'Exam Controller', cls: 'bg-orange-500/20 text-orange-300',dot: 'bg-orange-400'  };
  if (roles.value.includes('teacher'))         return { label: 'Teacher',          cls: 'bg-emerald-500/20 text-emerald-300',dot: 'bg-emerald-400'};
  if (roles.value.includes('student'))         return { label: 'Student',          cls: 'bg-sky-500/20 text-sky-300',     dot: 'bg-sky-400'     };
  return { label: 'User', cls: 'bg-slate-700 text-slate-300', dot: 'bg-slate-500' };
});

const ic = (d) => `<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${d}"/></svg>`;

// Shared icons
const I = {
  dashboard:     'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
  faculties:     'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
  departments:   'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z',
  courses:       'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
  students:      'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
  exams:         'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4',
  analytics:     'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
  reports:       'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  audit:         'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
  banks:         'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
  results:       'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
  profile:       'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
};

// Super Admin — full system control
const superAdminNav = [
  { section: 'Overview' },
  { label: 'Dashboard',    href: '/admin/dashboard',    icon: ic(I.dashboard) },
  { section: 'Academic' },
  { label: 'Faculties',    href: '/admin/faculties',    icon: ic(I.faculties) },
  { label: 'Departments',  href: '/admin/departments',  icon: ic(I.departments) },
  { label: 'Courses',      href: '/admin/courses',      icon: ic(I.courses) },
  { label: 'Students',     href: '/admin/students',     icon: ic(I.students) },
  { section: 'Examinations' },
  { label: 'All Exams',    href: '/admin/exams',        icon: ic(I.exams) },
  { section: 'Insights' },
  { label: 'Analytics',    href: '/admin/analytics',    icon: ic(I.analytics) },
  { label: 'Reports',      href: '/admin/reports',      icon: ic(I.reports) },
  { label: 'Audit Logs',   href: '/admin/audit-logs',   icon: ic(I.audit) },
  { section: 'Account' },
  { label: 'My Profile',   href: '/profile',            icon: ic(I.profile) },
];

// Exam Controller — exam operations only (no academic/user management, no audit logs)
const examControllerNav = [
  { section: 'Overview' },
  { label: 'Dashboard',    href: '/admin/dashboard',    icon: ic(I.dashboard) },
  { section: 'Examinations' },
  { label: 'Exams',        href: '/admin/exams',        icon: ic(I.exams) },
  { section: 'Insights' },
  { label: 'Analytics',    href: '/admin/analytics',    icon: ic(I.analytics) },
  { label: 'Reports',      href: '/admin/reports',      icon: ic(I.reports) },
  { section: 'Account' },
  { label: 'My Profile',   href: '/profile',            icon: ic(I.profile) },
];

// Teacher — authoring & evaluation
const teacherNav = [
  { section: 'Overview' },
  { label: 'Dashboard',       href: '/teacher/dashboard',      icon: ic(I.dashboard) },
  { section: 'My Work' },
  { label: 'My Exams',        href: '/teacher/exams',          icon: ic(I.exams) },
  { label: 'Question Banks',  href: '/teacher/question-banks', icon: ic(I.banks) },
  { section: 'Account' },
  { label: 'My Profile',      href: '/profile',                icon: ic(I.profile) },
];

// Student — taking exams & viewing results
const studentNav = [
  { section: 'Overview' },
  { label: 'Dashboard',  href: '/student/dashboard', icon: ic(I.dashboard) },
  { section: 'My Studies' },
  { label: 'My Exams',   href: '/student/exams',     icon: ic(I.exams) },
  { label: 'My Results', href: '/student/results',   icon: ic(I.results) },
  { section: 'Account' },
  { label: 'My Profile', href: '/profile',           icon: ic(I.profile) },
];

const navItems = computed(() => {
  if (roles.value.includes('super_admin'))     return superAdminNav;
  if (roles.value.includes('exam_controller')) return examControllerNav;
  if (roles.value.includes('teacher'))         return teacherNav;
  if (roles.value.includes('student'))         return studentNav;
  return [];
});
</script>

<style scoped>
/* Hide the sidebar scrollbar while keeping it scrollable */
.scrollbar-hide {
  -ms-overflow-style: none;  /* IE & old Edge */
  scrollbar-width: none;     /* Firefox */
}
.scrollbar-hide::-webkit-scrollbar {
  display: none;             /* Chrome, Safari, Opera */
}
</style>
