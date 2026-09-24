<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { User, Lock, Eye, EyeOff, LogIn } from 'lucide-vue-next';

defineProps({
    canResetPassword: {
        type: Boolean,
        default: true,
    },
    status: {
        type: String,
        default: null,
    },
});

const showPassword = ref(false);

const form = useForm({
    email: '', // Serves as both email or username
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Sign In - Admin Portal" />

        <div class="mb-6 text-center">
            <h1 class="text-2xl font-bold tracking-tight text-white">Welcome Back</h1>
            <p class="mt-1.5 text-xs text-slate-400">
                Sign in with your <span class="text-indigo-400 font-medium">email or username</span> to access the management panel
            </p>
        </div>

        <!-- Status message -->
        <div v-if="status" class="mb-5 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 p-3.5 text-xs font-medium text-emerald-400">
            {{ status }}
        </div>

        <!-- General / Throttle error -->
        <div v-if="form.errors.email && !form.errors.password" class="mb-5 rounded-2xl bg-rose-500/10 border border-rose-500/30 p-3.5 text-xs text-rose-400 flex items-start gap-2">
            <span class="inline-block mt-0.5 w-1.5 h-1.5 rounded-full bg-rose-400 flex-shrink-0"></span>
            <span>{{ form.errors.email }}</span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <!-- Email / Username Input -->
            <div>
                <label for="email" class="block text-xs font-semibold text-slate-300 mb-1.5">
                    Email or Username
                </label>
                <div class="relative rounded-2xl shadow-xs">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <User class="w-4 h-4" />
                    </div>
                    <input
                        id="email"
                        v-model="form.email"
                        type="text"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="Enter your email or username"
                        class="block w-full pl-10 pr-4 py-2.5 bg-slate-950/60 border rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-colors"
                        :class="form.errors.email ? 'border-rose-500/70 focus:border-rose-500 focus:ring-rose-500/30' : 'border-slate-800 hover:border-slate-700'"
                    />
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <div class="flex items-center justify-between mb-1.5">
                    <label for="password" class="block text-xs font-semibold text-slate-300">
                        Password
                    </label>
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-[11px] font-medium text-indigo-400 hover:text-indigo-300 transition-colors"
                    >
                        Forgot password?
                    </Link>
                </div>
                <div class="relative rounded-2xl shadow-xs">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <Lock class="w-4 h-4" />
                    </div>
                    <input
                        id="password"
                        v-model="form.password"
                        :type="showPassword ? 'text' : 'password'"
                        required
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="block w-full pl-10 pr-11 py-2.5 bg-slate-950/60 border rounded-xl text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/40 focus:border-indigo-500 transition-colors"
                        :class="form.errors.password ? 'border-rose-500/70 focus:border-rose-500 focus:ring-rose-500/30' : 'border-slate-800 hover:border-slate-700'"
                    />
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-200 focus:outline-none transition-colors"
                        :aria-label="showPassword ? 'Hide password' : 'Show password'"
                    >
                        <EyeOff v-if="showPassword" class="w-4 h-4" />
                        <Eye v-else class="w-4 h-4" />
                    </button>
                </div>
                <p v-if="form.errors.password" class="mt-1.5 text-xs text-rose-400">{{ form.errors.password }}</p>
            </div>

            <!-- Remember Me -->
            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center cursor-pointer select-none">
                    <input
                        type="checkbox"
                        v-model="form.remember"
                        class="w-4 h-4 rounded border-slate-700 bg-slate-950/80 text-indigo-600 focus:ring-indigo-500/50 focus:ring-offset-0 focus:ring-offset-slate-900"
                    />
                    <span class="ms-2 text-xs text-slate-300">Remember my session</span>
                </label>
            </div>

            <!-- Submit Button -->
            <div class="pt-2">
                <button
                    type="submit"
                    :disabled="form.processing"
                    class="w-full py-2.5 px-4 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 active:scale-[0.99] transition-all duration-150 shadow-lg shadow-indigo-600/30 flex items-center justify-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed cursor-pointer"
                >
                    <svg
                        v-if="form.processing"
                        class="animate-spin h-4 w-4 text-white"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <LogIn v-else class="w-4 h-4" />
                    <span>{{ form.processing ? 'Signing In...' : 'Sign In to Panel' }}</span>
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
