<div id="confirmModal" class="fixed inset-0 hidden items-center justify-center bg-black/50 z-50">

    <div class="bg-white text-black rounded-xl w-[90%] max-w-sm p-3 shadow-xl">

        <!-- Title -->
        <h2 id="confirmTitle" class="text-lg font-semibold mb-2">
            Confirm Action
        </h2>

        <!-- Message -->
        <p id="confirmMessage" class="text-sm text-black mb-5">
            Are you sure?
        </p>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
            <button id="confirmNo" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-500 text-sm">
                No
            </button>

            <button id="confirmYes" class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-red-500 text-sm">
                Yes
            </button>
        </div>

    </div>
</div>

<script>
    function showConfirm({ title = "Confirm", message = "Are you sure?" }) {
        return new Promise((resolve) => {
            const modal = document.getElementById("confirmModal");
            const titleEl = document.getElementById("confirmTitle");
            const messageEl = document.getElementById("confirmMessage");
            const yesBtn = document.getElementById("confirmYes");
            const noBtn = document.getElementById("confirmNo");

            titleEl.textContent = title;
            messageEl.textContent = message;

            modal.classList.remove("hidden");
            modal.classList.add("flex");

            function cleanup(result) {
                modal.classList.add("hidden");
                modal.classList.remove("flex");

                yesBtn.removeEventListener("click", yesHandler);
                noBtn.removeEventListener("click", noHandler);

                resolve(result);
            }

            function yesHandler() {
                cleanup(true);
            }

            function noHandler() {
                cleanup(false);
            }

            yesBtn.addEventListener("click", yesHandler);
            noBtn.addEventListener("click", noHandler);
        });
    }
</script>