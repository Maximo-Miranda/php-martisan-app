<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { CheckCircle2, X, XCircle, Info } from 'lucide-vue-next';

const page = usePage();

const flash = computed(() => page.props.flash as { success?: string; error?: string; info?: string });

const visible = ref(false);
const message = ref('');
const type = ref<'success' | 'error' | 'info'>('info');

watch(
    () => flash.value,
    (newFlash) => {
        if (newFlash.success) {
            message.value = newFlash.success;
            type.value = 'success';
            visible.value = true;
            autoHide();
        } else if (newFlash.error) {
            message.value = newFlash.error;
            type.value = 'error';
            visible.value = true;
            autoHide();
        } else if (newFlash.info) {
            message.value = newFlash.info;
            type.value = 'info';
            visible.value = true;
            autoHide();
        }
    },
    { deep: true, immediate: true }
);

function autoHide() {
    setTimeout(() => {
        visible.value = false;
    }, 5000);
}

function close() {
    visible.value = false;
}

const alertClasses = computed(() => {
    switch (type.value) {
        case 'success':
            return 'border-green-500 bg-green-50 text-green-900 dark:border-green-700 dark:bg-green-950 dark:text-green-100';
        case 'error':
            return 'border-red-500 bg-red-50 text-red-900 dark:border-red-700 dark:bg-red-950 dark:text-red-100';
        case 'info':
            return 'border-blue-500 bg-blue-50 text-blue-900 dark:border-blue-700 dark:bg-blue-950 dark:text-blue-100';
        default:
            return '';
    }
});

const iconComponent = computed(() => {
    switch (type.value) {
        case 'success':
            return CheckCircle2;
        case 'error':
            return XCircle;
        case 'info':
            return Info;
        default:
            return Info;
    }
});
</script>

<template>
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="transform opacity-0 translate-y-[-2rem]"
        enter-to-class="transform opacity-100 translate-y-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="transform opacity-100 translate-y-0"
        leave-to-class="transform opacity-0 translate-y-[-2rem]"
    >
        <div
            v-if="visible"
            class="fixed top-20 left-1/2 -translate-x-1/2 z-50 w-full max-w-md px-4"
        >
            <Alert
                :class="['relative shadow-lg border-2', alertClasses]"
            >
                <div class="flex items-start gap-3">
                    <component
                        :is="iconComponent"
                        class="h-5 w-5 mt-0.5 shrink-0"
                        :class="{
                            'text-green-600 dark:text-green-400': type === 'success',
                            'text-red-600 dark:text-red-400': type === 'error',
                            'text-blue-600 dark:text-blue-400': type === 'info'
                        }"
                    />
                    <AlertDescription class="text-sm font-medium flex-1 pr-6">
                        {{ message }}
                    </AlertDescription>
                    <button
                        @click="close"
                        class="absolute top-3 right-3 rounded-md p-1 hover:bg-black/10 dark:hover:bg-white/10 transition-colors"
                        type="button"
                        aria-label="Close notification"
                    >
                        <X class="h-4 w-4" />
                    </button>
                </div>
            </Alert>
        </div>
    </Transition>
</template>

