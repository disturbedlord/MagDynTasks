<!-- Full Screen Loader -->
<div id="loader" class="hidden fixed inset-0 bg-white/90 bg-opacity-70  flex flex-col items-center justify-center z-50">
    <!-- Default Tailwind spinner -->

    <div class="inline-block h-8 w-8 animate-spin rounded-full border-4 border-solid border-current border-e-transparent align-[-0.125em] text-black motion-reduce:animate-[spin_1.5s_linear_infinite] dark:text-white"
        role="status">
        <span
            class="!absolute !-m-px !h-px !w-px !overflow-hidden !whitespace-nowrap !border-0 !p-0 ![clip:rect(0,0,0,0)]">Loading...</span>
    </div>
    <div class="p-2 text-base " id="loaderText"></div>
</div>