<tr class="item">
    <td>

        <div class="action <?= $status !== "finished" ? "action-finished" : "action-pending" ?> text-base flex justify-start items-center p-2 font-bold 
            <?= $status !== "finished" ? "swipe-green" : "swipe-yellow" ?> flex flex-col
             h-full justify-center space-y-1 items-center">
            <i
                class="fa-solid text-lg <?= $status !== "finished" ? "fa-file-circle-check" : "fa-clock-rotate-left" ?>"></i>
            <p class=" text-center text-sm"><?= $status !== "finished" ? "Mark as Finished" : "Mark as Pending" ?>
            </p>
        </div>


        <div class="<?= $borderColor ?> border-l-[10px] flex flex-row">
            <div class="w-[100%] gap-4 flex items-center justify-between px-3 py-3 ">

                <div class="flex flex-col gap-1 w-full">
                    <div class="flex justify-between items-start">
                        <span class="text-black break-words [overflow-wrap:anywhere] font-semibold text-base  w-[65%]">
                            <?= htmlspecialchars($title) ?>
                        </span>
                        <span class="text-gray-400 text-xs">
                            <?= $time ?>
                        </span>
                    </div>



                    <div class="flex flex-row space-x-2">

                        <!-- status -->
                        <span
                            class="inline-flex items-center rounded-md px-2 py-1 text-xs font-medium inset-ring capitalize <?= $statusColor ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>
                        <!-- Due Date -->
                        <?php if ($dueDate): ?>
                            <span
                                class="inline-flex items-center rounded-md  px-2 py-1 text-xs <?= !$isPastDueDate ? "font-medium text-gray-600 bg-gray-100" : "font-bold text-red-700 bg-red-50" ?> inset-ring inset-ring-red-600/10">
                                <?= !$isPastDueDate ? $dueDate : "Over Due" ?>
                            </span>
                        <?php endif ?>
                    </div>

                    <?php require "TaskAssignment.php" ?>
                    <!-- if cron present it will show  -->
                    <?php
                    if ($cron != '') {
                        ?>
                        <span
                            class="w-fit items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 inset-ring inset-ring-gray-500/10">
                            <?= htmlspecialchars($cron) ?>
                        </span>
                    <?php } ?>
                </div>
            </div>

            <button name="editBtn" class="w-[20%] bg-gray-100 rounded-md m-2 flex justify-center items-center">
                <div class="ml-3">
                    <i class="fa-regular fa-pen-to-square text-gray-600 text-2xl"></i>
                </div>
            </button>
        </div>



        <div
            class="action action-right text-base flex justify-start items-center p-2 font-bold swipe-red flex flex-col h-full justify-center space-y-1 items-center">
            <i class="fa-solid fa-trash"></i>
            <p class="text-center text-sm">Delete Task</p>
        </div>
    </td>
</tr>