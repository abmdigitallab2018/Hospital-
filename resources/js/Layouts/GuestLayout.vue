<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowLeft } from 'lucide-vue-next';

const page = usePage();
const siteSetting = computed(() => page.props.siteSetting || {});

const siteTitle = computed(() => siteSetting.value?.site_title || 'Bhavesh.dev');
const siteSubtitle = computed(() => siteSetting.value?.site_subtitle || 'Admin Portal');
const logoText = computed(() => siteSetting.value?.logo_text || 'BM');
const logoImage = computed(() => {
    const img = siteSetting.value?.logo_image;
    if (!img) return null;
    if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('/')) {
        return img;
    }
    return `/storage/${img}`;
});
</script>

<template>
    <div class="min-h-screen bg-[#0F172A] text-slate-100 flex flex-col justify-center py-12 sm:px-6 lg:px-8 relative overflow-hidden font-sans selection:bg-indigo-500 selection:text-white">
        <!-- Ambient Background Glow & Dot Grid -->
        <div class="absolute inset-0 bg-[radial-gradient(#334155_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none"></div>
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[700px] h-[400px] bg-gradient-to-tr from-indigo-600/25 via-violet-600/20 to-pink-500/10 blur-3xl rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-40 right-10 w-[500px] h-[350px] bg-indigo-500/10 blur-3xl rounded-full pointer-events-none"></div>

        <!-- Top Header & Brand -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10 text-center mb-6">
            <Link :href="'/'" class="inline-flex items-center gap-3 group transition-transform duration-200 hover:scale-105 mb-4">
                <div v-if="logoImage" class="w-12 h-12 rounded-2xl overflow-hidden shadow-lg border border-slate-700/80 bg-slate-900 p-1">
                    <img :src="logoImage" :alt="siteTitle" class="w-full h-full object-contain" />
                </div>
                <div v-else class="w-12 h-12 rounded-2xl bg-gradient-to-br from-indigo-500 via-indigo-600 to-violet-600 text-white flex items-center justify-center font-extrabold text-lg tracking-wider shadow-lg shadow-indigo-500/30 border border-indigo-400/30">
                    {{ logoText }}
                </div>
                <div class="text-left">
                    <span class="block font-black text-xl tracking-tight text-white group-hover:text-indigo-400 transition-colors">
                        {{ siteTitle }}
                    </span>
                    <span class="block text-xs font-medium text-slate-400">
                        {{ siteSubtitle }}
                    </span>
                </div>
            </Link>
        </div>

        <!-- Main Card Container -->
        <div class="sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0 relative z-10">
            <div class="bg-slate-900/80 backdrop-blur-xl border border-slate-800/90 shadow-2xl rounded-3xl p-6 sm:p-8 relative">
                <slot />
            </div>

            <!-- Footer Return Link -->
            <div class="mt-6 text-center">
                <Link
                    :href="'/'"
                    class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-400 hover:text-indigo-400 transition-colors"
                >
                    <ArrowLeft class="w-3.5 h-3.5" />
                    <span>Return to Website</span>
                </Link>
            </div>
        </div>
    </div>
</template>
