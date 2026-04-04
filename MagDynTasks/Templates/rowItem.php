<div class="<?= $borderColor ?> border-l-[10px] flex flex-row">
    <div class="w-[80%] gap-4 flex items-center justify-between px-3 py-3">

        <div class="flex flex-col gap-1 w-full">
            <div class="flex justify-between items-start">
                <span class="text-black font-semibold text-base text-wrap w-[70%]">
                    <?= htmlspecialchars($title) ?>
                </span>
                <span class="text-gray-400 text-xs">
                    <?= $time ?>
                </span>
            </div>

            <div class="text-gray-700 text-xs truncate text-wrap">
                <?= htmlspecialchars($truncatedDescription) ?>
            </div>

            <div class="flex flex-row justify-between">
                <div>
                    <!-- creator -->
                    <span
                        class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 inset-ring inset-ring-indigo-700/10">
                        <?= htmlspecialchars($name) ?>
                    </span>
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-400 inline-block" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 5l7 7-7 7M5 12h14" />
                    </svg>
                    <!-- assignee -->
                    <span
                        class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 inset-ring inset-ring-indigo-700/10">
                        <?= htmlspecialchars($assginee) ?>
                    </span>
                </div>
                <!-- status -->
                <span
                    class="ml-4 inline-flex items-center rounded-md px-2 py-1 text-xs font-medium inset-ring capitalize <?= $statusColor ?>">
                    <?= htmlspecialchars($status) ?>
                </span>
            </div>

            <!-- if cron present it will show  -->
            <?php
            if ($cron != '') {
                ?>
                <span
                    class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 inset-ring inset-ring-gray-500/10">
                    <?= htmlspecialchars($cron) ?>
                </span>
            <?php } ?>
        </div>
    </div>

    <div class="w-[20%] bg-gray-100 rounded-md m-2 flex justify-center items-center">
        <button name="editBtn" class="ml-3">
            <i class="fa-regular fa-pen-to-square text-gray-600 text-2xl"></i>
        </button>
    </div>
</div>