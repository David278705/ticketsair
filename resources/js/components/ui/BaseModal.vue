<template>
    <TransitionRoot :show="open" as="template">
        <Dialog as="div" class="relative z-[70]" @close="onClose">
            <TransitionChild
                as="template"
                enter="ease-out duration-200"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="ease-in duration-150"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4">
                    <TransitionChild
                        as="template"
                        enter="ease-out duration-200"
                        enter-from="opacity-0 translate-y-2 scale-95"
                        enter-to="opacity-100 translate-y-0 scale-100"
                        leave="ease-in duration-150"
                        leave-from="opacity-100 translate-y-0 scale-100"
                        leave-to="opacity-0 translate-y-2 scale-95"
                    >
                        <DialogPanel
                            class="w-full max-w-3xl rounded-2xl bg-white p-6 shadow-xl"
                        >
                            <div class="flex items-center justify-between mb-3">
                                <DialogTitle class="text-lg font-semibold">
                                    <slot name="title">{{ title }}</slot>
                                </DialogTitle>
                                <button
                                    class="size-9 grid place-items-center rounded-lg hover:bg-slate-100"
                                    type="button"
                                    @click="onClose"
                                >
                                    ✕
                                </button>
                            </div>
                            <slot />
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>

<script setup>
import {
    Dialog,
    DialogPanel,
    DialogTitle,
    TransitionChild,
    TransitionRoot,
} from "@headlessui/vue";

const props = defineProps({
    open: { type: Boolean, default: false },
    title: { type: String, default: "" },
});
const emit = defineEmits(["update:open"]);
function onClose() {
    emit("update:open", false);
}
</script>
