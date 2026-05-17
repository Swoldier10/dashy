<div
    x-data
    class="dashy-toaster"
    aria-live="polite"
    aria-atomic="true"
    <?php echo e($attributes); ?>

>
    <template x-for="toast in $store.toaster.items" :key="toast.id">
        <div
            class="dashy-toast"
            :class="'dashy-toast--' + toast.variant"
            role="status"
            @click="$store.toaster.dismiss(toast.id)"
        >
            <span class="dashy-toast-icon" aria-hidden="true">
                <template x-if="toast.variant === 'success'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-[18px]">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                    </svg>
                </template>
                <template x-if="toast.variant === 'danger'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-[18px]">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7 4a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm-1-9a.75.75 0 0 0-.75.75v3.5a.75.75 0 0 0 1.5 0v-3.5A.75.75 0 0 0 10 5Z" clip-rule="evenodd" />
                    </svg>
                </template>
                <template x-if="toast.variant === 'warning'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-[18px]">
                        <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                    </svg>
                </template>
                <template x-if="toast.variant !== 'success' && toast.variant !== 'danger' && toast.variant !== 'warning'">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-[18px]">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-8-3.5a.75.75 0 0 0 0 1.5h.01a.75.75 0 0 0 0-1.5H10ZM10 9a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 9Z" clip-rule="evenodd" />
                    </svg>
                </template>
            </span>
            <p class="dashy-toast-text" x-text="toast.text"></p>
            <button
                type="button"
                class="dashy-toast-close"
                @click.stop="$store.toaster.dismiss(toast.id)"
                aria-label="<?php echo e(__('Dismiss')); ?>"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                    <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </button>
        </div>
    </template>
</div>
<?php /**PATH /var/www/html/resources/views/components/dashy/toaster.blade.php ENDPATH**/ ?>