<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$is_admin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"];
$user_id = $_SESSION["user_id"] ?? null;
$user_name = $_SESSION["user_name"] ?? null;
?>



<?php

require __DIR__ . '/../Modal/events.php';
require __DIR__ . '/../Modal/user_account.php';
$allUsersResult = getAllUsers();
$allUsers = [];
while ($row = $allUsersResult->fetch_assoc()) {
    $allUsers[] = $row;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php require "../Common/header.php" ?>
    <link rel="stylesheet" href="../Stylesheet/mobile_view.css">
    <script>
        window.APP = {
            user: {
                id: <?= (int) $_SESSION['user_id'] ?>,
                name: <?= json_encode($_SESSION['user_name']) ?>,
                isAdmin: <?= $_SESSION['is_admin'] ?>
            }
        };
    </script>
</head>

<body class="">
    <div class=" h-16 fixed top-0 left-0 w-full bg-white z-40 shadow-sm">
        <div class="h-full flex flex-row items-center justify-between px-2">

            <!-- MENU BUTTON -->
            <button id="menuBtn" class="w-12 h-12 rounded-md bg-gray-200">
                <i class="bi bi-list text-2xl"></i>
            </button>
            <button id="openFilter"
                class="w-12 h-12 flex justify-center items-center bg-gray-200 text-black p-3 rounded-md">
                <i class="bi bi-funnel text-2xl"></i>
            </button>

        </div>
    </div>

    <!-- OVERLAY -->
    <div id="overlay" class="fixed inset-0 bg-black/40 hidden z-50"></div>

    <!-- SIDEBAR -->
    <div id="sidebar"
        class="fixed top-0 left-0 h-svh w-64 bg-white shadow-lg transform -translate-x-full transition-transform duration-300 z-50 flex flex-col">

        <!-- Header -->
        <div class="p-4 border-b flex justify-between items-start">
            <div class="flex flex-col">
                <span class="font-bold text-lg">Hi, <?= $user_name ?></span>
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
        </div>

        <!-- Bottom (Logout pinned) -->
        <div class="p-4 border-t">
            <button id="logoutBtn"
                class="w-full px-4 py-2 bg-red-600 text-white rounded-sm font-semibold hover:bg-red-700 transition duration-150">
                Logout
            </button>
        </div>

    </div>

    <div class="pt-16">
        <div class="overflow-hidden w-full">

            <table id="mobile_view_table" class="w-full">
                <thead class="hidden">
                    <tr>
                        <th>ID</th>
                        <th>Content</th>
                    </tr>
                </thead>
            </table>
            <!-- <div class="flex items-center my-6">
                <div class="flex-grow h-px bg-gray-200"></div>
                <span class="mx-4 text-sm text-gray-400">You’ve reached the end</span>
                <div class="flex-grow h-px bg-gray-200"></div>
            </div> -->
        </div>

    </div>
    <!-- Floating Add Task Button -->
    <button id="openModalBtn"
        class="fixed bottom-4 right-4 bg-blue-600 hover:bg-blue-600 text-white font-semibold px-4 py-3 rounded-md shadow-lg z-30 flex items-center space-x-2">
        <i class="fas fa-plus"></i>
        <span>Add Task</span>
    </button>
    <?php require "../View/TaskModal.php";
    require "../View/BottomSheet.php";
    require "../View/ConfirmationModal.php";
    ?>
    <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-2"></div>
    <script src="../Script/cronParser.js"></script>

    <script src="../Script/mobile_view.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

</body>

</html>