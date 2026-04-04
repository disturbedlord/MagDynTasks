<!-- OVERLAY -->
<div id="overlay" class="fixed inset-0 bg-black/40 hidden z-50"></div>

<!-- SIDEBAR -->
<div id="sidebar"
    class="fixed top-0 left-0 h-svh w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-50 flex flex-col">

    <!-- Header -->
    <div class="p-4 border-b flex justify-between items-start">
        <div class="flex flex-col">
            <span class="font-bold text-lg">Hi,
                <?= $user_name ?>
            </span>
            <?php if ($is_admin == true): ?>
                <div><span
                        class="inline-flex items-center rounded-md bg-blue-400/10 px-2 py-1 text-xs font-medium text-blue-400 inset-ring inset-ring-blue-400/30">Admin</span>
                </div>
            <?php endif; ?>
        </div>
        <button id="closeBtn" class="text-gray-500 text-xl">&times;</button>
    </div>

    <!-- Middle (optional content/menu items) -->
    <div class="flex-1 overflow-y-auto p-4">
        <!-- future menu items go here -->

        <button type="button" onclick="window.location.href='HomePage.php'"
            class="w-full text-black bg-neutral-primary border border-brand hover:bg-brand hover:text-white focus:ring-4 focus:ring-brand-subtle  leading-5 rounded-lg text-base px-3 py-1.5 focus:outline-none">Home
            Page</button>

    </div>

    <!-- Bottom (Logout pinned) -->
    <div class="p-4 border-t">
        <button id="logoutBtn"
            class="w-full px-4 py-2 bg-red-600 text-white rounded-sm font-semibold hover:bg-red-700 transition duration-150">
            Logout
        </button>
    </div>

</div>