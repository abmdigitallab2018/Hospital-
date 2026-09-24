<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppButton from '@/Components/UI/AppButton.vue';
import { WifiOff, RotateCw, ArrowLeft } from 'lucide-vue-next';

const retry = () => {
    window.location.reload();
};
</script>

<template>
    <div class="min-h-screen bg-[#0F172A] text-white flex items-center justify-center p-6 font-sans antialiased">
        <Head title="Offline - ABM Digital Lab" />

        <div class="max-w-md w-full text-center space-y-6">
            <div class="w-16 h-16 mx-auto rounded-3xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center border border-indigo-500/20 shadow-lg shadow-indigo-500/10">
                <WifiOff class="w-8 h-8" />
            </div>

            <div class="space-y-2">
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">You're Currently Offline</h1>
                <p class="text-sm text-slate-400 leading-relaxed">
                    It looks like your device is disconnected from the internet. Cached portfolio pages remain accessible via the Service Worker.
                </p>
            </div>

            <div class="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                <AppButton
                    variant="primary"
                    size="md"
                    @click="retry"
                >
                    <RotateCw class="w-4 h-4 mr-1.5" />
                    <span>Retry Connection</span>
                </AppButton>

                <AppButton
                    href="/"
                    variant="secondary"
                    size="md"
                >
                    <ArrowLeft class="w-4 h-4 mr-1.5" />
                    <span>Return to Home</span>
                </AppButton>
            </div>
        </div>
    </div>
</template>
