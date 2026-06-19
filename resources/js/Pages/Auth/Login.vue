<script setup>
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({ canResetPassword: Boolean, status: String });

const form = useForm({ email: '', password: '', remember: false });
const showPassword = ref(false);

const features = [
    'Automated exam scheduling & proctoring',
    'OLAP analytics and performance trends',
    'SQL ranking with window functions',
    'Real-time grading & result management',
    'PDF result cards with QR verification',
];

const submit = () => { form.post(route('login'), { onFinish: () => form.reset('password') }); };
</script>

<template>
    <Head title="Sign In" />

    <div class="min-h-screen flex font-body">

        <!-- ── Left branding panel ────────────────────────────────────── -->
        <div class="hidden lg:flex lg:w-[45%] flex-col justify-between p-12 relative overflow-hidden"
             style="background: linear-gradient(145deg, #8B0E1C 0%, #BC2739 45%, #D4404F 100%);">

            <!-- Decorative blobs -->
            <div class="absolute -top-24 -left-24 w-80 h-80 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -bottom-24 -right-24 w-80 h-80 bg-white/5 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-white/3 rounded-full blur-3xl pointer-events-none"></div>

            <!-- Logo -->
            <div class="relative flex items-center gap-3 z-10">
                <div class="w-10 h-10 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-2xl font-black text-white tracking-tight font-heading">UNIXAM</span>
            </div>

            <!-- Hero -->
            <div class="relative z-10">
                <h1 class="text-5xl font-black text-white leading-tight mb-5 font-heading">
                    Your exams,<br />
                    <span class="text-white/70">smarter.</span>
                </h1>
                <p class="text-white/70 text-base leading-relaxed mb-10 max-w-sm">
                    A modern examination platform built for universities. Conduct, manage, and analyse exams at scale.
                </p>
                <div class="space-y-3">
                    <div v-for="feat in features" :key="feat" class="flex items-center gap-3">
                        <div class="w-5 h-5 rounded-full bg-white/25 flex items-center justify-center flex-shrink-0">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-white/80 text-sm">{{ feat }}</span>
                    </div>
                </div>
            </div>

            <p class="relative z-10 text-white/40 text-xs">© 2026 UNIXAM. All rights reserved.</p>
        </div>

        <!-- ── Right login panel ──────────────────────────────────────── -->
        <div class="flex-1 flex flex-col items-center justify-center px-6 py-12 bg-slate-950">

            <!-- Mobile logo -->
            <div class="lg:hidden flex items-center gap-3 mb-10">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                     style="background:#BC2739;">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <span class="text-2xl font-black text-white font-heading">UNIXAM</span>
            </div>

            <div class="w-full max-w-sm">
                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-white mb-2 font-heading">Welcome back</h2>
                    <p class="text-slate-400 text-sm">Sign in to your account to continue</p>
                </div>

                <div v-if="status" class="mb-5 p-4 bg-emerald-500/10 border border-emerald-500/30 rounded-xl text-emerald-400 text-sm">
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Email address</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                                </svg>
                            </div>
                            <input id="email" v-model="form.email" type="email" required autofocus
                                autocomplete="username" placeholder="you@university.edu"
                                class="w-full h-[50px] pl-11 pr-4 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:border-transparent transition-all"
                                :class="form.errors.email ? 'border-red-500 focus:ring-red-500' : 'focus:ring-primary-500'"
                                style="--tw-ring-color: #BC2739;"
                            />
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.email" />
                    </div>

                    <!-- Password -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="password" class="block text-sm font-medium text-slate-300">Password</label>
                            <Link v-if="canResetPassword" :href="route('password.request')"
                                class="text-xs hover:opacity-80 transition-opacity"
                                style="color:#BC2739;">Forgot password?</Link>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </div>
                            <input id="password" v-model="form.password"
                                :type="showPassword ? 'text' : 'password'"
                                required autocomplete="current-password" placeholder="••••••••"
                                class="w-full h-[50px] pl-11 pr-12 bg-slate-900 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:border-transparent transition-all"
                                :class="form.errors.password ? 'border-red-500 focus:ring-red-500' : 'focus:ring-primary-500'"
                            />
                            <button type="button" @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-500 hover:text-slate-300 transition-colors">
                                <svg v-if="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <InputError class="mt-1.5" :message="form.errors.password" />
                    </div>

                    <!-- Remember me -->
                    <div class="flex items-center gap-3">
                        <button type="button" @click="form.remember = !form.remember"
                            :class="['relative w-10 h-5 rounded-full transition-colors duration-200 focus:outline-none', form.remember ? 'bg-primary-600' : 'bg-slate-700']"
                            :style="form.remember ? 'background:#BC2739' : ''">
                            <span :class="['absolute top-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200', form.remember ? 'translate-x-5' : 'translate-x-0.5']"></span>
                        </button>
                        <span class="text-sm text-slate-400 select-none">Remember me for 30 days</span>
                    </div>

                    <!-- Submit -->
                    <button type="submit" :disabled="form.processing"
                        class="w-full h-[50px] font-semibold rounded-xl transition-all duration-200 flex items-center justify-center gap-2 text-sm shadow-lg disabled:opacity-60 disabled:cursor-not-allowed text-white"
                        style="background:#BC2739; box-shadow: 0 4px 24px rgba(188,39,57,0.30);"
                        onmouseover="this.style.background='#a01f2e'"
                        onmouseout="this.style.background='#BC2739'"
                    >
                        <svg v-if="form.processing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                        {{ form.processing ? 'Signing in…' : 'Sign in' }}
                    </button>
                </form>

                <!-- Demo credentials -->
                <div class="mt-8 p-4 bg-slate-900 border border-slate-800 rounded-xl">
                    <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Demo Credentials</p>
                    <div class="space-y-1 text-xs text-slate-500">
                        <p><span class="text-slate-400">Admin:</span> admin@uniexam.local / password</p>
                        <p><span class="text-slate-400">Teacher:</span> teacher@uniexam.local / password</p>
                        <p><span class="text-slate-400">Student:</span> ahmed@student.uniexam.local / password</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
