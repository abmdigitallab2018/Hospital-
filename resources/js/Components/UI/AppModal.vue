<script setup>
import { watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: ''
    },
    maxWidth: {
        type: String,
        default: 'md',
        validator: (v) => ['sm', 'md', 'lg', 'xl', '2xl'].includes(v)
    },
    closeable: {
        type: Boolean,
        default: true
    }
});

const emit = defineEmits(['close', 'update:show']);

const close = () => {
    if (props.closeable) {
        emit('close');
        emit('update:show', false);
    }
};

const closeOnEscape = (e) => {
    if (e.key === 'Escape' && props.show) {
        close();
    }
};

onMounted(() => document.addEventListener('keydown', closeOnEscape));
onUnmounted(() => document.removeEventListener('keydown', closeOnEscape));

watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
    } else {
        document.body.style.overflow = '';
    }
});

const maxWidthClass = {
    sm: 'sm:max-w-sm',
    md: 'sm:max-w-md',
    lg: 'sm:max-w-lg',
    xl: 'sm:max-w-xl',
    '2xl': 'sm:max-w-2xl',
}[props.maxWidth];
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0"
            enter-to-class="opacity-100"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="show"
                class="fixed inset-0 z-50 overflow-y-auto px-4 py-6 sm:px-0 flex items-center justify-center"
            >
                <!-- Backdrop -->
                <div
                    class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"
                    @click="close"
                ></div>

                <!-- Modal Window -->
                <div
                    :class="[
                        'relative bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden w-full transition-all transform z-10',
                        maxWidthClass
                    ]"
                >
                    <!-- Modal Header -->
                    <div
                        v-if="title || $slots.header || closeable"
                        class="px-6 py-4 border-b border-slate-100 flex items-center justify-between"
                    >
                        <slot name="header">
                            <h3 class="text-base font-bold text-slate-900 tracking-tight">
                                {{ title }}
                            </h3>
                        </slot>

                        <button
                            v-if="closeable"
                            type="button"
                            @click="close"
                            class="text-slate-400 hover:text-slate-600 rounded-lg p-1 transition"
                            aria-label="Close modal"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="px-6 py-5">
                        <slot />
                    </div>

                    <!-- Modal Footer -->
                    <div
                        v-if="$slots.footer"
                        class="px-6 py-3.5 bg-slate-50 border-t border-slate-100 flex items-center justify-end gap-3"
                    >
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
