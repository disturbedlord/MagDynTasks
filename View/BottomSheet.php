<!-- OVERLAY -->
<div id="filterOverlay" class="fixed inset-0 bg-black/40 hidden z-40"></div>

<!-- BOTTOM SHEET -->
<div id="filterModal"
    class="fixed h-[90%] bottom-0 left-0 w-full bg-white rounded-t-2xl shadow-lg transform translate-y-full transition-transform duration-300 z-40">

    <!-- Handle -->
    <div class="w-12 h-1.5 bg-gray-300 rounded-full mx-auto my-2"></div>

    <!-- Header -->
    <div class="px-4 py-2 border-b flex justify-between items-center">
        <span class="font-semibold text-lg">Filter</span>
        <button id="closeFilter" class="text-gray-500 text-xl">&times;</button>
    </div>

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
                                    <button id="dropdown-button"
                                        class="inline-flex justify-between items-center w-full px-3 py-2 text-sm  bg-white border border-gray-300 rounded-md  focus:outline-none">

                                        <span id="filterSelectedUser" class="text-gray-700">Select Users</span>

                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 ml-2" viewBox="0 0 20 20"
                                            fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M6.293 9.293a1 1 0 011.414 0L10 11.586l2.293-2.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </button>

                                    <!-- DROPDOWN -->
                                    <div id="dropdown-menu"
                                        class="hidden absolute right-0 mt-2 w-full bg-white rounded-md  ring-1 ring-black/10 z-50">

                                        <!-- Search -->
                                        <div class="p-2 border-b">
                                            <input id="search-input"
                                                class="w-full px-3 py-2 text-sm border border-gray-300 rounded-md focus:outline-none"
                                                type="text" placeholder="Search users">
                                        </div>

                                        <!-- LIST -->
                                        <ul id="dropdown-list" class="max-h-60 overflow-y-auto p-2 space-y-1 text-sm">

                                            <?php foreach ($allUsers as $row): ?>
                                                <li>
                                                    <label
                                                        class="flex items-center gap-2 px-2 py-1 hover:bg-gray-100 rounded cursor-pointer">
                                                        <input type="checkbox"
                                                            class="user-checkbox w-4 h-4 border-2 border-gray-600 rounded-sm bg-white checked:bg-gray-800 checked:border-gray-800 focus:ring-0"
                                                            value="<?= $row['uid'] ?>" data-name="<?= $row['first_name'] ?>">
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
                        <select id="filterStatus"
                            class="px-3 py-2 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">Status</option>

                            <option value="0">Pending</option>
                            <option value="1">Finished</option>

                        </select>
                        <!-- Priority -->
                        <select id="filterPriority"
                            class="px-3 py-2 border rounded-lg text-sm bg-white focus:outline-none focus:ring-2 focus:ring-gray-300">
                            <option value="">Priority (1–10)</option>
                            <?php for ($i = 1; $i <= 10; $i++): ?>
                                <option value="<?= $i ?>">Priority <?= $i ?></option>
                            <?php endfor; ?>
                        </select>



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

<script>


    document.addEventListener(
        "DOMContentLoaded",
        function () {



            const sortDateBtn = document.getElementById("sortDate");
            const sortPriorityBtn = document.getElementById("sortPriority");

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
            });

            window.closeFilter = function () {
                modal.classList.add('translate-y-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }

            document.getElementById('closeFilter').addEventListener('click', closeFilter);
            overlay.addEventListener('click', closeFilter);

            const dropdownBtn = document.getElementById('dropdown-button');
            const dropdownMenu = document.getElementById('dropdown-menu');
            const selectedText = document.getElementById('filterSelectedUser');
            const checkboxes = document.querySelectorAll('.user-checkbox');

            // Toggle dropdown
            dropdownBtn.addEventListener('click', () => {
                dropdownMenu.classList.toggle('hidden');
            });

            // Close when clicking outside
            document.addEventListener('click', (e) => {
                if (!dropdownBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                    dropdownMenu.classList.add('hidden');
                }
            });

            // Update selected text
            function updateSelected() {
                const selected = [...checkboxes]
                    .filter(cb => cb.checked)
                    .map(cb => cb.dataset.name);

                const selectedIds = [...checkboxes]
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);

                if (selected.length === 0) {
                    selectedText.innerText = "Select Users";
                } else {
                    selectedText.innerText = selected.join(', ');
                }
                let existingSelectedIds = selectedText.getAttribute("data-selectedids") ?? "";
                existingSelectedIds = selectedIds;
                selectedText.setAttribute("data-selectedids", existingSelectedIds);
            }

            // Listen to checkbox changes
            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateSelected);
            });

            document.getElementById('search-input').addEventListener('input', function () {
                const val = this.value.toLowerCase();

                document.querySelectorAll('#dropdown-list li').forEach(li => {
                    li.style.display = li.innerText.toLowerCase().includes(val)
                        ? ''
                        : 'none';
                });
            });
        },
        false,
    );
</script>