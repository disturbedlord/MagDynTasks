<div class="grid sm:w-64 grid-cols-3 items-center gap-y-1">
    <div>
        <span
            class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 inset-ring inset-ring-indigo-700/10">
            <?= htmlspecialchars($name) ?>

        </span>
    </div>
    <div class="h-[2px] w-[95%] bg-gray-200"></div>
    <div class="relative">


        <div class="absolute -left-[31px] top-[11px] h-[2px] w-7 bg-gray-200"></div>
        <div class="absolute -left-[8px] top-[7px] w-0 h-0 
              border-t-[5px] border-b-[5px] border-l-[8px]
              border-t-transparent border-b-transparent border-l-gray-200">
        </div>

        <span
            class="ml-2 inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 inset-ring inset-ring-indigo-700/10">
            <?= str_replace(' ', '', htmlspecialchars($assignees[0])) ?>
        </span>
    </div>
    <?php foreach (array_slice($assignees, 1) as $assignee1): ?>
        <div class=""></div>
        <div class=""></div>
        <div class="relative">
            <div class="absolute -left-[31px] -top-[16px] h-7 w-7 border-l-2 border-b-2"></div>
            <div class="absolute -left-[8px] top-[6px] w-0 h-0 
              border-t-[5px] border-b-[5px] border-l-[8px]
              border-t-transparent border-b-transparent border-l-gray-200">
            </div>
            <span
                class="ml-2 break-all inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 inset-ring inset-ring-indigo-700/10">
                <?= str_replace(' ', '', $assignee1) ?>
            </span>
        </div>
    <?php endforeach ?>


</div>

<script>
    // this wont work, since js doesnt run on server of php, write the same logic in php
    const firstNode = (user) => {
        return `<div class="relative">


            <div class="absolute -left-[31px] top-[11px] h-[2px] w-7 bg-gray-200"></div>
            <div class="absolute -left-[8px] top-[7px] w-0 h-0 
              border-t-[5px] border-b-[5px] border-l-[8px]
              border-t-transparent border-b-transparent border-l-gray-200">
            </div>

            <span
                class="ml-2 inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 inset-ring inset-ring-indigo-700/10">
                ${user}
            </span>
        </div>`;
    }


    function RenderUserUI() {
        const firstNodeElement = $("#firstNode");
        console.log(firstNode());
        firstNodeElement.html(firstNode("avi"));

    }

    RenderUserUI();
</script>