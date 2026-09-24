<script setup>
import { ref, watch, onMounted } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const visible = ref(false);
const message = ref('');
const type = ref('success');
let timer = null;

const showToast = (msg, toastType = 'success', duration = 4000) => {
    message.value = msg;
    type.value = toastType;
    visible.value = true;

    clearTimeout(timer);
    timer = setTimeout(() => {
        visible.value = false;
    }, duration);
};

const close = () => {
    visible.value = false;
    clearTimeout(timer);
};

// Watch Inertia flash messages
watch(
    () => page.props.flash,
    (flash) => {
        if (!flash) return;
        if (flash.success || flash.contact_success) {
            showToast(flash.success || flash.contact_success, 'success');
        } else if (flash.error) {
            showToast(flash.error, 'error');
        } else if (flash.warning) {
            showToast(flash.warning, 'warning');
        } else if (flash.info) {
            showToast(flash.info, 'info');
        }
    },
    { deep: true, immediate: true }
);

defineExpose({
    show: showToast,
    close
});
</script>

<template>
    <Teleport to="body">
        <Transition
            enter-active-class="transform transition ease-out duration-300"
            enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-4"
            enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
            leave-active-class="transition ease-in duration-200"
            leave-from-class="opacity-100"
            leave-to-class="opacity-0"
        >
            <div
                v-if="visible"
                class="fixed top-4 right-4 z-50 max-w-sm w-[calc(100%-2rem)] sm:w-full bg-white rounded-2xl shadow-xl border border-slate-200/80 p-4 pointer-events-auto"
            >
                <div class="flex items-start gap-3">
                    <!-- Icon -->
                    <div
                        :class="[
                            'w-8 h-8 rounded-xl flex items-center justify-center flex-shrink-0',
                            type === 'success' ? 'bg-emerald-50 text-emerald-600' : '',
                            type === 'error' ? 'bg-red-50 text-red-600' : '',
                            type === 'warning' ? 'bg-amber-50 text-amber-600' : '',
                            type === 'info' ? 'bg-indigo-50 text-indigo-600' : '',
                        ]"
                    >
                        <svg v-if="type === 'success'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        <svg v-else-if="type === 'error'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        <svg v-else-if="type === 'warning'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>

                    <!-- Message -->
                    <div class="flex-1 pt-0.5">
                        <p class="text-xs font-bold capitalize text-slate-900">
                            {{ type }}
                        </p>
                        <p class="mt-0.5 text-xs text-slate-600 leading-relaxed">
                            {{ message }}
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button
                        type="button"
                        @click="close"
                        class="text-slate-400 hover:text-slate-600 p-1 rounded-lg transition"
                        aria-label="Close notification"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
