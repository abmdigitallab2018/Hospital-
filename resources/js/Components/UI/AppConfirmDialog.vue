<script setup>
import AppModal from './AppModal.vue';
import AppButton from './AppButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false
    },
    title: {
        type: String,
        default: 'Confirm Deletion'
    },
    message: {
        type: String,
        default: 'Are you sure you want to proceed? This action cannot be undone.'
    },
    confirmText: {
        type: String,
        default: 'Delete'
    },
    cancelText: {
        type: String,
        default: 'Cancel'
    },
    variant: {
        type: String,
        default: 'danger'
    },
    loading: {
        type: Boolean,
        default: false
    }
});

const emit = defineEmits(['confirm', 'cancel', 'update:show']);

const onConfirm = () => {
    emit('confirm');
};

const onCancel = () => {
    emit('cancel');
    emit('update:show', false);
};
</script>

<template>
    <AppModal
        :show="show"
        maxWidth="md"
        @close="onCancel"
    >
        <div class="flex items-start gap-4">
            <div
                :class="[
                    'w-10 h-10 rounded-2xl flex items-center justify-center flex-shrink-0',
                    variant === 'danger' ? 'bg-red-50 text-red-600' : 'bg-amber-50 text-amber-600'
                ]"
            >
                <svg v-if="variant === 'danger'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
                <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>

            <div class="flex-1">
                <h3 class="text-base font-bold text-slate-900 tracking-tight">
                    {{ title }}
                </h3>
                <p class="mt-1.5 text-xs sm:text-sm text-slate-600 leading-relaxed">
                    {{ message }}
                </p>
            </div>
        </div>

        <template #footer>
            <AppButton
                variant="secondary"
                size="sm"
                @click="onCancel"
                :disabled="loading"
            >
                {{ cancelText }}
            </AppButton>
            <AppButton
                :variant="variant"
                size="sm"
                @click="onConfirm"
                :loading="loading"
            >
                {{ confirmText }}
            </AppButton>
        </template>
    </AppModal>
</template>
