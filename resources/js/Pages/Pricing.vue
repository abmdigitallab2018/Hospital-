<script setup>
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    CreditCard,
    CheckCircle2,
    XCircle,
    ArrowRight,
    ArrowLeft,
    Sparkles,
    ShieldCheck,
    Download,
    FileText,
    HelpCircle,
    ChevronDown,
    Zap,
    Cpu,
    ExternalLink,
    Lock
} from 'lucide-vue-next';

const props = defineProps({
    plans: {
        type: Array,
        default: () => [],
    },
    siteSetting: {
        type: Object,
        default: () => null,
    },
    canLogin: Boolean,
    canRegister: Boolean,
});

const siteTitle = computed(() => props.siteSetting?.site_title || 'Bhavesh.dev');
const siteSubtitle = computed(() => props.siteSetting?.site_subtitle || 'Software Architect');
const logoText = computed(() => props.siteSetting?.logo_text || 'BM');
const logoImage = computed(() => {
    const img = props.siteSetting?.logo_image;
    if (!img) return null;
    if (img.startsWith('http://') || img.startsWith('https://') || img.startsWith('/')) {
        return img;
    }
    return `/storage/${img}`;
});

// FAQ Accordion State
const activeFaq = ref(0);
const toggleFaq = (idx) => {
    activeFaq.value = activeFaq.value === idx ? null : idx;
};

const faqs = [
    {
        q: 'Can these packages be tailored to our specific technical requirements?',
        a: 'Yes, absolutely. While these packages serve as common baseline deliverables for MVPs and full-stack platforms, every solution is architected around your exact domain model, third-party integrations, and scale goals.'
    },
    {
        q: 'How does the payment and milestone schedule work?',
        a: 'For project-based contracts, we typically split billing across clear milestones: 30% kickoff deposit, 40% intermediate sprint milestone demonstration, and 30% final deployment and handover.'
    },
    {
        q: 'Do you transfer full intellectual property and source code ownership?',
        a: '100%. Upon project completion and final milestone sign-off, full intellectual property, repository access, and infrastructure credentials are unconditionally transferred to your organization.'
    },
    {
        q: 'What kind of support is included post-launch?',
        a: 'Every package includes dedicated bug-fixing and infrastructure monitoring post-launch (from 1 month up to 6 months depending on the tier). Extended monthly retainer contracts are also available.'
    },
    {
        q: 'Do you work under mutual non-disclosure agreements (NDAs)?',
        a: 'Yes. Before reviewing your proprietary specifications or repositories, we are glad to execute standard mutual NDAs to protect your confidential information.'
    }
];
</script>

<template>
    <div class="min-h-screen bg-[#F8FAFC] text-slate-800 font-sans antialiased selection:bg-indigo-500 selection:text-white">
        <Head title="Transparent Pricing & Investment Plans" />

        <!-- Sticky Header Navigation -->
        <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-md border-b border-slate-200/80 transition-all duration-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16 sm:h-20">
                    <!-- Brand -->
                    <Link :href="'/'" class="flex items-center space-x-3 group">
                        <div v-if="logoImage" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl overflow-hidden shadow-sm flex items-center justify-center bg-white border border-slate-200 group-hover:scale-105 transition-transform">
                            <img :src="logoImage" :alt="siteTitle" class="w-full h-full object-contain" />
                        </div>
                        <div v-else class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-violet-600 text-white flex items-center justify-center font-extrabold text-sm tracking-wider shadow-sm shadow-indigo-500/25 group-hover:scale-105 transition-transform duration-200">
                            {{ logoText }}
                        </div>
                        <div class="text-left leading-none">
                            <span class="block font-bold text-slate-900 text-base sm:text-lg tracking-tight group-hover:text-indigo-600 transition-colors">
                                {{ siteTitle }}
                            </span>
                            <span class="block text-[11px] font-medium text-slate-400 mt-0.5">
                                {{ siteSubtitle }}
                            </span>
                        </div>
                    </Link>

                    <!-- Nav Links -->
                    <nav class="hidden md:flex items-center space-x-1 lg:space-x-2">
                        <Link :href="'/'" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors">
                            Home
                        </Link>
                        <Link :href="'/#about'" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors">
                            About
                        </Link>
                        <Link :href="'/#services'" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors">
                            Services
                        </Link>
                        <Link :href="'/#portfolio'" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors">
                            Selected Work
                        </Link>
                        <Link :href="'/pricing'" class="px-3.5 py-2 text-sm font-semibold text-indigo-600 bg-indigo-50 rounded-xl transition-colors">
                            Pricing
                        </Link>
                        <Link :href="'/#posts'" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors">
                            Daily Posts
                        </Link>
                        <Link :href="'/#contact'" class="px-3.5 py-2 text-sm font-medium text-slate-600 hover:text-indigo-600 rounded-xl hover:bg-slate-100/70 transition-colors">
                            Contact
                        </Link>
                    </nav>

                    <!-- Back to Home or Admin Login -->
                    <div class="flex items-center space-x-3">
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 rounded-xl transition-colors shadow-2xs"
                        >
                            <Lock class="w-3.5 h-3.5" />
                            <span>Sign In</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <section class="relative bg-[#0F172A] text-white pt-20 pb-24 lg:pt-28 lg:pb-32 overflow-hidden">
            <!-- Background Glow & Grid Overlay -->
            <div class="absolute inset-0 bg-[radial-gradient(#1e293b_1px,transparent_1px)] [background-size:24px_24px] opacity-25 pointer-events-none"></div>
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[700px] h-[350px] bg-gradient-to-tr from-indigo-600/25 to-violet-600/20 blur-3xl rounded-full pointer-events-none"></div>

            <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center space-y-6">
                <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-indigo-500/10 border border-indigo-400/25 text-indigo-300 text-xs font-semibold backdrop-blur-md">
                    <Sparkles class="w-3.5 h-3.5 text-indigo-400" />
                    <span>Transparent & Predictable Investment</span>
                </div>

                <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight">
                    Software Architected to Scale, <br class="hidden sm:inline" />
                    <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-400 via-violet-300 to-sky-400">
                        Priced to Deliver ROI
                    </span>
                </h1>

                <p class="max-w-2xl mx-auto text-base sm:text-lg text-slate-300 leading-relaxed font-normal">
                    From agile startup MVPs to distributed enterprise microservices. Fixed-scope deliverable tiers with zero surprise overhead.
                </p>
            </div>
        </section>

        <!-- Pricing Cards Section -->
        <section class="relative -mt-16 sm:-mt-20 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 z-10 pb-20">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
                <div
                    v-for="plan in plans"
                    :key="plan.id"
                    :class="[
                        'rounded-3xl flex flex-col justify-between transition-all duration-300 relative',
                        plan.is_featured
                            ? 'bg-white border-2 border-indigo-600 shadow-2xl shadow-indigo-600/15 ring-4 ring-indigo-600/10 lg:-translate-y-2'
                            : 'bg-white/90 backdrop-blur-sm border border-slate-200/90 shadow-lg hover:shadow-xl hover:border-slate-300'
                    ]"
                >
                    <!-- Top Popular Ribbon -->
                    <div
                        v-if="plan.badge"
                        class="absolute -top-3.5 left-1/2 -translate-x-1/2 px-4 py-1 rounded-full text-xs font-bold tracking-wide uppercase shadow-md flex items-center gap-1.5"
                        :class="plan.is_featured ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white' : 'bg-slate-900 text-white'"
                    >
                        <Sparkles class="w-3 h-3 text-amber-300" />
                        <span>{{ plan.badge }}</span>
                    </div>

                    <div class="p-8 sm:p-10 space-y-6 flex-1">
                        <!-- Plan Header -->
                        <div>
                            <h3 class="text-xl font-bold text-slate-900 tracking-tight">
                                {{ plan.name }}
                            </h3>
                            <p v-if="plan.tagline" class="text-xs text-slate-500 mt-1 min-h-[32px]">
                                {{ plan.tagline }}
                            </p>
                        </div>

                        <!-- Price Tag -->
                        <div class="pt-2 border-t border-slate-100">
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-4xl sm:text-5xl font-black text-slate-900 tracking-tight">
                                    {{ plan.formatted_price }}
                                </span>
                                <span class="text-xs font-semibold text-slate-500 capitalize">
                                    / {{ plan.billing_period }}
                                </span>
                            </div>
                            <p v-if="plan.description" class="text-xs text-slate-500 mt-2 leading-relaxed">
                                {{ plan.description }}
                            </p>
                        </div>

                        <!-- Included Features -->
                        <div class="space-y-3 pt-4 border-t border-slate-100">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">
                                Included Deliverables
                            </span>
                            <ul class="space-y-2.5">
                                <li
                                    v-for="(feature, fIdx) in plan.features"
                                    :key="fIdx"
                                    class="flex items-start gap-2.5 text-xs text-slate-700 font-medium"
                                >
                                    <CheckCircle2 class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" />
                                    <span>{{ feature }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Excluded Features (If Any) -->
                        <div v-if="plan.not_included && plan.not_included.length" class="space-y-2 pt-2">
                            <ul class="space-y-2">
                                <li
                                    v-for="(item, eIdx) in plan.not_included"
                                    :key="eIdx"
                                    class="flex items-start gap-2.5 text-xs text-slate-400"
                                >
                                    <XCircle class="w-4 h-4 text-slate-300 flex-shrink-0 mt-0.5" />
                                    <span>{{ item }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Bottom Card CTA & Attachments -->
                    <div class="p-8 sm:p-10 pt-0 space-y-4">
                        <a
                            :href="plan.cta_url || '/#contact'"
                            :class="[
                                'w-full py-3 px-5 rounded-2xl text-center text-xs font-bold transition-all duration-200 flex items-center justify-center gap-2 shadow-xs cursor-pointer',
                                plan.is_featured
                                    ? 'bg-gradient-to-r from-indigo-600 via-indigo-500 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white shadow-indigo-500/25 hover:shadow-indigo-500/40 hover:scale-[1.01]'
                                    : 'bg-slate-900 hover:bg-slate-800 text-white'
                            ]"
                        >
                            <span>{{ plan.cta_text || 'Select Package' }}</span>
                            <ArrowRight class="w-3.5 h-3.5" />
                        </a>

                        <div class="text-center">
                            <span class="text-[11px] text-slate-400 font-medium">100% IP ownership & NDA protected</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Value Guarantees Section -->
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 border-t border-slate-200">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">The Engineering Standard</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">Every Plan Includes Our Core Quality Guarantees</h2>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <ShieldCheck class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Security & Reliability First</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Industry-standard authentication, CSRF/XSS defenses, rate-limiting, and defensive database queries out of the box.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <Zap class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Modern High-Speed Stack</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Built with cutting-edge Laravel, Inertia, Vue 3, Vite, and Tailwind for instant page renders and smooth UX.
                    </p>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/90 shadow-2xs space-y-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                        <Cpu class="w-5 h-5" />
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Clean, Maintainable Code</h3>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Strict separation of concerns, single-responsibility services, and comprehensive documentation for easy handover.
                    </p>
                </div>
            </div>
        </section>

        <!-- FAQs Section -->
        <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center mb-10">
                <span class="text-xs font-bold uppercase tracking-wider text-indigo-600">Frequently Asked Questions</span>
                <h2 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2">Common Questions & Answers</h2>
            </div>

            <div class="space-y-4">
                <div
                    v-for="(faq, fIdx) in faqs"
                    :key="fIdx"
                    class="bg-white border border-slate-200/90 rounded-2xl overflow-hidden transition-shadow shadow-2xs"
                >
                    <button
                        type="button"
                        @click="toggleFaq(fIdx)"
                        class="w-full p-5 text-left flex items-center justify-between gap-4 font-semibold text-sm text-slate-800 hover:text-indigo-600 transition-colors cursor-pointer"
                    >
                        <span>{{ faq.q }}</span>
                        <ChevronDown
                            class="w-4 h-4 text-slate-400 transition-transform duration-200 flex-shrink-0"
                            :class="activeFaq === fIdx ? 'rotate-180 text-indigo-600' : ''"
                        />
                    </button>
                    <div
                        v-show="activeFaq === fIdx"
                        class="px-5 pb-5 text-xs text-slate-600 leading-relaxed border-t border-slate-100 pt-3"
                    >
                        {{ faq.a }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Custom Scope Contact Banner -->
        <section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 pb-24">
            <div class="bg-gradient-to-r from-slate-900 via-slate-900 to-indigo-950 text-white rounded-3xl p-8 sm:p-12 text-center sm:text-left sm:flex items-center justify-between gap-8 border border-slate-800 shadow-xl relative overflow-hidden">
                <div class="space-y-2 max-w-xl relative z-10">
                    <h3 class="text-2xl sm:text-3xl font-bold tracking-tight">Need a custom roadmap or architectural consulting?</h3>
                    <p class="text-xs sm:text-sm text-slate-300">
                        Let's discuss your product milestones, timeline, and tech stack requirements. We provide complimentary technical estimations.
                    </p>
                </div>
                <div class="pt-6 sm:pt-0 flex-shrink-0 relative z-10">
                    <Link
                        :href="'/#contact'"
                        class="inline-flex items-center gap-2 px-6 py-3.5 rounded-2xl bg-indigo-600 hover:bg-indigo-500 font-bold text-xs text-white shadow-lg shadow-indigo-600/30 transition-all hover:scale-105"
                    >
                        <span>Schedule Technical Call</span>
                        <ArrowRight class="w-4 h-4" />
                    </Link>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="bg-white border-t border-slate-200 py-8">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-xs text-slate-500">
                <p>&copy; {{ new Date().getFullYear() }} {{ siteTitle }}. All rights reserved.</p>
            </div>
        </footer>
    </div>
</template>
