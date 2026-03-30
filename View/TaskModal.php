<!-- Modal backdrop -->
<div id="TaskModal" class="p-2 fixed inset-0 bg-black/80 flex items-center justify-center hidden z-50 overflow-auto">
    <!-- Modal container -->
    <div class="py-2 bg-white rounded-lg shadow-lg w-full max-w-md relative max-h-[90vh] flex flex-col">

        <!-- Close button -->
        <button id="closeTaskModal"
            class="absolute top-3 right-3 text-gray-500 hover:text-gray-700 text-xl font-bold">&times;</button>

        <!-- Modal title -->
        <div class="flex flex-row items-center py-2 px-6 justify-start">
            <h2 id="modalTitle" class="text-xl font-semibold">Add Task</h2>
        </div>
        <hr class="border-t border-gray-300">

        <!-- Scrollable content -->
        <div class="px-6 py-2 overflow-auto flex-1">
            <form id="TaskForm" class="space-y-4">
                <!-- Title -->
                <div>
                    <label for="title" class="block text-gray-700 font-medium mb-1">Title <span
                            class="text-red-500">*</span></label>
                    <input id="title" name="title" type="text" maxlength="250"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter task title" required>
                </div>

                <!-- Description -->
                <div>
                    <label for="description" class="block text-gray-700 font-medium mb-1">Description <span
                            class="text-red-500">*</span></label>
                    <textarea id="description" name="description" rows="3" maxlength="250"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Enter task description" required></textarea>
                </div>

                <!-- Cron Command -->
                <div>
                    <div class="flex flex-row space-x-2"><label for="cron"
                            class="block text-gray-700 font-medium mb-1">Cron Command</label>
                        <span id="cronValidator" style="display:none"
                            class="inline-flex items-center rounded-md bg-gray-400/10 px-2 py-1 text-xs font-medium text-gray-500 inset-ring inset-ring-gray-400/20"></span>
                    </div>

                    <input type="text" id="cron" name="cron"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Optional cron command">

                </div>


                <!-- Priority -->
                <div>
                    <label for="priority" class="block text-gray-700 font-medium mb-1">Priority (1-10)</label>
                    <select id="priority" name="priority"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <?php for ($i = 1; $i <= 10; $i++) {
                            echo "<option value=\"$i\">$i</option>";
                        } ?>
                    </select>
                </div>

                <!-- Department -->
                <div>
                    <label for="department" class="block text-gray-700 font-medium mb-1">Department <span
                            class="text-red-500">*</span></label>
                    <input id="department" name="department" type="text"
                        class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required>
                </div>

                <!-- User -->
                <div>
                    <label for="user" class="block text-gray-700 font-medium mb-1">User <span
                            class="text-red-500">*</span></label>
                    <?php if ($is_admin): ?>
                        <select id="user" name="user"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="" selected disabled>Select User</option>
                            <?php foreach ($allUsers as $row): ?>
                                <option value="<?= $row['uid'] ?>">
                                    <?= $row['first_name'] ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select id="user" name="user"
                            class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option selected disabled value="<?= $user_id ?>"><?= $user_name ?></option>
                        </select>
                    <?php endif; ?>

                </div>


                <!-- Hidden ID for edit mode -->
                <input type="hidden" id="taskId" name="id" value="">
            </form>
        </div>

        <!-- Buttons fixed at bottom -->
        <div class="flex flex-row justify-center space-x-2 px-6 py-3 border-t border-gray-200">
            <button type="button" id="closeTask"
                class="text-sm px-4 py-2 bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Close</button>
            <button type="submit" form="TaskForm"
                class="text-sm px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Task</button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {

        const modal = document.getElementById('TaskModal');
        const openBtn = document.getElementById('openModalBtn'); // your open button
        const form = document.getElementById('TaskForm');
        const cronField = document.getElementById("cron");
        const cronValidator = document.getElementById("cronValidator");

        cronField.addEventListener("input", (e) => {

            const currentCronValue = cronField.value;
            if (currentCronValue !== "") {
                cronValidator.style.display = "block";
            } else {
                cronValidator.style.display = "none";
            }
            const cronValidation = cronToShortText(currentCronValue);

            cronValidator.innerText = currentCronValue === "" ? "" : cronValidation;

        })

        const closeBtns = [document.getElementById('closeTaskModal'), document.getElementById('closeTask')];

        openBtn?.addEventListener('click', () => {
            modal.classList.remove('hidden');
            modal.dataset.mode = 'addTask';
            // incase of edit validate on modal load

        });
        closeBtns.forEach(btn => btn?.addEventListener('click', () => modal.classList.add('hidden')));

        // Close when clicking outside container
        modal.addEventListener('click', (e) => { if (e.target === modal) modal.classList.add('hidden'); });

        form.addEventListener('submit', (e) => {
            e.preventDefault(); // prevent page reload

            const description = document.getElementById('description').value.trim();
            const cron = document.getElementById('cron').value.trim();
            const priority = document.getElementById('priority').value;
            const user = document.getElementById('user').value;
            const title = document.getElementById('title').value;
            const caller = modal.dataset.mode;
            let isEditThenId = 0;
            if (caller === "editTask") {
                isEditThenId = modal.dataset.id;
            }
            const department = document.getElementById('department').value;

            if (!description || !user) {
                alert("User is required");
                return;
            }

            // send via fetch to your PHP handler
            fetch('../Modal/events.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `action=${caller}&description=${encodeURIComponent(description)}&cron=${encodeURIComponent(cron)}&priority=${priority}&user=${user}&title=${encodeURIComponent(title)}&department=${department}${caller === "editTask" ? `&id=${isEditThenId}` : ''}`
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status === true) {
                        if (data.isEdit === true) {
                            showToast(data.message || "Task Updated successfully", "success");
                        }
                        else
                            showToast(data.message || "Task Created successfully", "success");

                        // optionally reset form
                        form.reset();

                        // close modal
                        modal.classList.add('hidden');

                        // reload table if using DataTables
                        if (typeof table !== 'undefined') {
                            table.ajax.reload(null, false);
                        }
                    } else {
                        alert("Failed to add task: " + (data.message || 'Unknown error'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert("Error adding task");
                });
        });
    });
</script>