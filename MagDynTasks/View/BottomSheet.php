<!-- OVERLAY -->
<div id="filterOverlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

<!-- BOTTOM SHEET -->
<div id="filterModal"
    class="fixed h-[90%] bottom-0 left-0 w-full bg-white rounded-t-2xl shadow-lg transform translate-y-full transition-transform duration-300 z-40">

    <!-- Handle -->

    <!-- Header -->
    <div class="px-4 py-2 border-b flex justify-between items-center">
        <span class="font-semibold text-lg">Filter</span>
        <button id="closeFilter" class="text-gray-500 text-xl">&times;</button>
    </div>
    <div class="h-[90%] flex flex-col justify-between">
        <!-- Content -->
        <div class="p-4 max-h-[60vh]  bg-white relative">

            <div class="bg-white rounded-xl ">

                <div>
                    <div class="flex flex-col gap-2 mb-4">
                        <div class=" font-semibold ">Sort by</div>
                        <div class=" grid grid-cols-1 md:grid-cols-5 gap-3">
                            <!-- Sort Buttons -->

                            <div class=" flex flex-row justify-between space-x-2">
                                <button id="sortDate" data-sortdate=0 data-isSelected="1"
                                    class="w-1/2 border rounded-md items-center justify-between flex flex-row py-2 px-3">
                                    <p class="text-sm text-gray-700">Date</p>
                                    <i id="dateSortIcon" class="text-2xl bi bi-sort-down-alt "></i>
                                </button>
                                <button id="sortPriority" data-sortpriority=0 data-isSelected="0"
                                    class="w-1/2 border rounded-md items-center justify-between flex flex-row py-2 px-3 ">
                                    <p class="text-sm text-gray-700">Priority</p>
                                    <i id="prioritySortIcon" class="text-2xl bi bi-sort-numeric-down"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-col gap-2">
                        <div class=" font-semibold">Search by</div>
                        <div class=" grid grid-cols-1 md:grid-cols-5 gap-3">
                            <?php if ($is_admin): ?>
                                <!-- Multi User Select -->

                                <div class="flex-1 w-full">
                                    <div class="relative">

                                        <!-- BUTTON -->
                                        <button id="dropdown-button-userSelection"
                                            class="inline-flex justify-between items-center w-full px-3 py-2 text-sm  bg-white border border-gray-300 rounded-md  focus:outline-none">

                                            <span id="filterSelectedUser" class="text-gray-700">Select Assignees</span>
                                            <div class="flex flex-row space-x-2">
                                                <span id="clearUserBtn"
                                                    class="bg-neutral-secondary-medium border border-default-medium text-heading text-xs font-medium px-1.5 py-0.5 rounded">Clear</span>


                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2"
                                                    viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                        </button>

                                        <!-- DROPDOWN -->
                                        <div id="dropdown-menu-userSelection"
                                            class="hidden absolute right-0 mt-2 w-full bg-white rounded-md  ring-1 ring-black/10 z-50">

                                            <!-- Search -->
                                            <div class="p-2 border-b">
                                                <input id="search-input-userSelection"
                                                    class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
                                                    type="text" placeholder="Search users">
                                            </div>

                                            <!-- LIST -->
                                            <ul id="dropdown-list-userSelection"
                                                class="max-h-60 overflow-y-auto p-2 space-y-1 text-sm">

                                                <?php foreach ($allUsers as $row): ?>
                                                    <li>
                                                        <label
                                                            class="flex items-center gap-2 px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                                            <input type="checkbox" data-type="userSelection"
                                                                class="user-checkbox w-4 h-4 border-2 border-gray-600 rounded-sm bg-white checked:bg-gray-800 checked:border-gray-800 focus:ring-0"
                                                                value="<?= $row['uid'] ?>"
                                                                data-name="<?= $row['first_name'] ?>">
                                                            <span>
                                                                <?= $row['first_name'] ?>
                                                            </span>
                                                        </label>
                                                    </li>
                                                <?php endforeach; ?>

                                            </ul>
                                        </div>


                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Title -->
                            <input type="text" id="filterTitle" placeholder="Search title..."
                                class="px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-gray-300">

                            <!-- Status -->
                            <h3 class="font-semibold text-heading">Status</h3>
                            <ul
                                class="items-center w-full text-sm  text-heading bg-neutral-primary-soft border border-default rounded-lg flex flex-row">
                                <li class="w-full border-b border-default sm:border-b-0 sm:border-r">
                                    <div class="flex flex-row items-center ps-3">
                                        <input id="horizontal-list-radio-license" type="radio" value="0"
                                            name="list-radio"
                                            class="w-4 h-4 text-neutral-primary border-default-medium bg-neutral-secondary-medium rounded-full checked:border-brand focus:ring-2 focus:outline-none focus:ring-brand-subtle border border-default appearance-none">
                                        <label for="horizontal-list-radio-license"
                                            class="w-full py-2 select-none ms-2 text-sm text-gray-700 text-heading">Pending</label>
                                    </div>
                                </li>
                                <li class="w-full border-l border-b border-default sm:border-b-0 sm:border-r">
                                    <div class="flex items-center ps-3">
                                        <input id="horizontal-list-radio-id" type="radio" value="1" name="list-radio"
                                            class="w-4 h-4 text-neutral-primary border-default-medium bg-neutral-secondary-medium rounded-full checked:border-brand focus:ring-2 focus:outline-none focus:ring-brand-subtle border border-default appearance-none">
                                        <label for="horizontal-list-radio-id"
                                            class="w-full py-2 select-none ms-2 text-sm text-gray-700 text-heading">Finished</label>
                                    </div>
                                </li>
                            </ul>


                            <!-- Priority -->
                            <div class="flex-1 w-full">
                                <div class="relative">

                                    <!-- BUTTON -->
                                    <button id="dropdown-button-prioritySelection"
                                        class="inline-flex justify-between items-center w-full px-3 py-2 text-sm  bg-white border border-gray-300 rounded-md  focus:outline-none">

                                        <span id="filterSelectedPriority" class="text-gray-700">Select
                                            Priority</span>
                                        <div class="flex flex-row space-x-2">
                                            <span id="clearPriorityBtn"
                                                class="bg-neutral-secondary-medium border border-default-medium text-heading text-xs font-medium px-1.5 py-0.5 rounded">Clear</span>


                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2"
                                                viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    </button>

                                    <!-- DROPDOWN -->
                                    <div id="dropdown-menu-prioritySelection"
                                        class="hidden absolute right-0 mt-2 w-full bg-white rounded-md  ring-1 ring-black/10 z-50">

                                        <!-- LIST -->
                                        <ul id="dropdown-list-prioritySelection"
                                            class="max-h-60 overflow-y-auto p-2 space-y-1 text-sm">

                                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                                <li>
                                                    <label
                                                        class="flex items-center gap-2 px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                                        <input type="checkbox" data-type="prioritySelection"
                                                            class="priority-checkbox w-4 h-4 border-2 border-gray-600 rounded-sm bg-white checked:bg-gray-800 checked:border-gray-800 focus:ring-0"
                                                            value="<?= $i ?>" data-name="<?= $i ?>">
                                                        <span>
                                                            <?= "Priority " . $i ?>
                                                        </span>
                                                    </label>
                                                </li>
                                            <?php endfor; ?>

                                        </ul>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Footer -->
        <div class="p-4 border-t flex gap-2">
            <button id="resetFilter" class="w-1/2 px-4 py-2 bg-gray-200 rounded-md">
                Reset
            </button>

            <button id="applyFilter" class="w-1/2 px-4 py-2 bg-blue-600 text-white rounded-md">
                Apply
            </button>
        </div>
    </div>

</div>

</div>

<script>

    const sortDateBtn = document.getElementById("sortDate");
    const sortPriorityBtn = document.getElementById("sortPriority");
    const checkboxes = document.querySelectorAll('.user-checkbox');
    const priorityCheckboxes = document.querySelectorAll('.priority-checkbox');
    const selectedText = document.getElementById('filterSelectedUser');
    const selectedTextPriority = document.getElementById('filterSelectedPriority');

    let StoreFilter;

    // Active Sortable Btns
    function activate(btnActive, btnInactive) {
        // Active button
        btnActive.classList.remove("opacity-50", "cursor-not-allowed");
        btnActive.classList.add("bg-gray-300");
        btnActive.setAttribute("data-isSelected", "1");
        // Inactive button
        btnInactive.classList.add("opacity-50", "cursor-not-allowed");
        btnInactive.classList.remove("bg-gray-300");
        btnInactive.setAttribute("data-isSelected", "0");
    }

    document.addEventListener(
        "DOMContentLoaded",
        function () {
            // State Management Filter from Global Store
            StoreFilter = window.xoid?.filter;
            // Reload Filter state from Global State


            // keep Date as Default Sorting
            activate(sortDateBtn, sortPriorityBtn);

            document.getElementById("sortDate").addEventListener("click", () => {
                activate(sortDateBtn, sortPriorityBtn);

                let currentSort = $("#sortDate").attr("data-sortDate");
                currentSort = currentSort === "0" ? "1" : "0"
                $("#sortDate").attr("data-sortDate", currentSort);


                document.getElementById("dateSortIcon").classList = currentSort === "0" ? "text-2xl bi bi-sort-down-alt" : "text-2xl bi bi-sort-up-alt"
                // Your sorting logic here
            });

            document.getElementById("sortPriority").addEventListener("click", () => {
                activate(sortPriorityBtn, sortDateBtn);

                let currentSort = $("#sortPriority").attr("data-sortPriority");
                currentSort = currentSort === "0" ? "1" : "0"
                $("#sortPriority").attr("data-sortPriority", currentSort)

                document.getElementById("prioritySortIcon").classList = currentSort === "0" ? "text-2xl bi bi-sort-numeric-down" : "text-2xl bi bi-sort-numeric-up"
                // Your sorting logic here
            });

            const modal = document.getElementById('filterModal');
            const overlay = document.getElementById('filterOverlay');

            document.getElementById('openFilter').addEventListener('click', () => {
                modal.classList.remove('translate-y-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden');
                SetFilter(clearFilter = false);

            });

            window.closeFilter = function () {
                modal.classList.add('translate-y-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            document.getElementById('closeFilter').addEventListener('click', closeFilter);
            overlay.addEventListener('click', closeFilter);

            const dropdownBtnUserSelection = document.getElementById('dropdown-button-userSelection');
            const dropdownMenuUserSelection = document.getElementById('dropdown-menu-userSelection');

            const dropdownBtnPrioritySelection = document.getElementById('dropdown-button-prioritySelection');
            const dropdownMenuPrioritySelection = document.getElementById('dropdown-menu-prioritySelection');

            // Toggle User Selection dropdown
            dropdownBtnUserSelection?.addEventListener('click', () => {
                dropdownMenuUserSelection.classList.toggle('hidden');
            });

            // Toggle Priority Selection dropdown
            dropdownBtnPrioritySelection?.addEventListener('click', () => {
                dropdownMenuPrioritySelection.classList.toggle('hidden');
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!dropdownBtnUserSelection?.contains(e.target) && !dropdownMenuUserSelection?.contains(e.target)) {
                    dropdownMenuUserSelection?.classList.add('hidden');
                }

                if (!dropdownBtnPrioritySelection?.contains(e.target) && !dropdownMenuPrioritySelection?.contains(e.target)) {
                    dropdownMenuPrioritySelection?.classList.add('hidden');
                }

            });



            // Listen to user checkbox changes
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelected);
            });

            // Listen to Priority checkbox changes
            priorityCheckboxes.forEach(cb => {
                cb.addEventListener('change', updateSelectedPriority);
            });

            document.getElementById('search-input-userSelection')?.addEventListener('input', function () {
                const val = this.value.toLowerCase();

                document.querySelectorAll('#dropdown-list-userSelection li')?.forEach(li => {
                    li.style.display = li.innerText.toLowerCase().includes(val)
                        ? ''
                        : 'none';
                });
            });

            let debounceTimer;
            $("#applyFilter").on("click", function () {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(() => TableReload(), 500);
                isFilterApplied = true;
                // Save Filter in Global State
                StoreFilter.setFilter({
                    title: $("#filterTitle").val() ?? '',
                    status: $("input[name='list-radio']:checked").val() ?? '',
                    priority: $("#filterSelectedPriority").attr("data-selectedpriorities") ?? '',
                    uid: $("#filterSelectedUser").attr("data-selectedids") ?? '',
                    sortByDate: $("#sortDate").attr("data-isSelected") ?? '',
                    sortDate: $("#sortDate").attr("data-sortdate") ?? '',
                    sortByPriority: $("#sortPriority").attr("data-isSelected") ?? '',
                    sortPriority: $("#sortPriority").attr("data-sortpriority") ?? ''
                });

                $('#loader').toggleClass('hidden');
                $('#loaderText').text("Applying Filter")
            });

            $("#resetFilter").on("click", function () {
                SetFilter(clearFilter = true);
                isFilterApplied = true;
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });

                // Clear all checkboxes
                priorityCheckboxes.forEach(cb => {
                    cb.checked = false;
                });


                // Clear Global State
                StoreFilter.setFilter({});

                reloadView();

            });

            $("#clearUserBtn").on("click", (e) => {
                $("#filterSelectedUser").val("");
                $("#filterSelectedUser").text("Select Assignees");
                $("#filterSelectedUser").attr("data-selectedids", "");
                checkboxes.forEach(cb => {
                    cb.checked = false;
                });


                e.stopPropagation(); // Prevent parent click
            })

            $("#clearPriorityBtn").on("click", (e) => {
                $("#filterSelectedPriority").val("");
                $("#filterSelectedPriority").text("Select Priority");
                $("#filterSelectedPriority").attr("data-selectedPriorities", "");

                // Clear all checkboxes
                priorityCheckboxes.forEach(cb => {
                    cb.checked = false;
                });
                e.stopPropagation(); // Prevent parent click
            })
        },
        false,
    );

    // Update selected text
    function updateSelected() {
        const selected = [...checkboxes]
            .filter(cb => cb.checked)
            .map(cb => cb.dataset.name);

        const selectedIds = [...checkboxes]
            .filter(cb => cb.checked)
            .map(cb => cb.value);
        if (selectedText) {
            if (selected.length === 0) {
                selectedText.innerText = "Select Assignees";
            } else {
                selectedText.innerText = selected.join(', ');
            }
            let existingSelectedIds = selectedText.getAttribute("data-selectedids") ?? "";
            existingSelectedIds = selectedIds;
            selectedText.setAttribute("data-selectedids", existingSelectedIds);
        }
    }

    // Update selected text
    function updateSelectedPriority() {


        const selectedIds = [...priorityCheckboxes]
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selectedIds.length === 0) {
            selectedTextPriority.innerText = "Select Priority";
        } else {
            selectedTextPriority.innerText = selectedIds.join(', ');
        }
        let existingSelectedIds = selectedTextPriority.getAttribute("data-selectedPriorities") ?? "";
        existingSelectedIds = selectedIds;
        selectedTextPriority.setAttribute("data-selectedPriorities", existingSelectedIds);
    }



    const SetFilter = (clearFilter = false) => {
        if (clearFilter) {
            $("#filterTitle").val("");
            $("#filterPriority").val("");
            $("#filterSelectedUser").val("");
            $("#filterSelectedUser").text("Select Assignees");
            $("#filterSelectedUser").attr("data-selectedids", "");

            $("#filterSelectedPriority").val("");
            $("#filterSelectedPriority").text("Select Priority");
            $("#filterSelectedPriority").attr("data-selectedPriorities", "");

            $("input[name='list-radio']").prop("checked", false);
            activate(sortDateBtn, sortPriorityBtn);
            $("#sortDate").attr("data-sortDate", 0);

            document.getElementById("dateSortIcon").classList = "text-2xl bi bi-sort-down-alt";
            $("#sortPriority").attr("data-sortPriority", 0)

            document.getElementById("prioritySortIcon").classList = "text-2xl bi bi-sort-numeric-down";
        } else {
            if (Object.keys(StoreFilter?.value?.value).length > 0) {
                // Set State from global State
                const { title, priority, uid, status, sortDate, sortByDate, sortPriority, sortByPriority } = StoreFilter?.value?.value;
                $("#filterTitle").val(title);
                $("#filterPriority").val(priority);
                $(`input[name='list-radio'][value='${status}']`).prop("checked", true);
                sortByDate === "1" ? activate(sortDateBtn, sortPriorityBtn) : activate(sortPriorityBtn, sortDateBtn);

                document.getElementById("dateSortIcon").classList = sortDate === "0" ? "text-2xl bi bi-sort-down-alt" : "text-2xl bi bi-sort-up-alt"
                $("#sortDate").attr("data-sortDate", sortDate);

                document.getElementById("prioritySortIcon").classList = sortPriority === "0" ? "text-2xl bi bi-sort-numeric-down" : "text-2xl bi bi-sort-numeric-up"
                $("#sortPriority").attr("data-sortPriority", sortPriority);

                // Populate Selected Users
                const userIdsToCheck = uid.split(",");

                document.querySelectorAll("input[data-type='userSelection']").forEach(cb => {
                    cb.checked = userIdsToCheck.includes(cb.value);
                });

                updateSelected();

                // Populate Selected Priorities
                const prioritiesToCheck = priority.split(",");

                document.querySelectorAll("input[data-type='prioritySelection']").forEach(cb => {
                    cb.checked = prioritiesToCheck.includes(cb.value);
                });

                updateSelectedPriority();
            }
        }
    }
</script>