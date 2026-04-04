<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$is_admin = isset($_SESSION["is_admin"]) && $_SESSION["is_admin"];
$user_id = $_SESSION["user_id"] == '' ? null : $_SESSION["user_id"];
$user_name = $_SESSION["user_name"] == '' ? null : $_SESSION["user_name"];
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
            <div class="flex flex-row space-x-2">
                <button id="exportCSV"
                    class="w-12 h-12 flex justify-center items-center bg-gray-200 text-black p-3 rounded-md">

                    <i class="bi bi-filetype-csv text-2xl"></i>
                </button>
                <button id="openFilter"
                    class="w-12 h-12 flex justify-center items-center bg-gray-200 text-black p-3 rounded-md">
                    <i class="bi bi-funnel text-2xl"></i>
                </button>
            </div>
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

    <div class="fixed bottom-4 right-4 space-y-2 flex flex-col  items-end justify-end">
        <button id="openModalBtn"
            class=" bg-blue-600 hover:bg-blue-600 text-white font-semibold px-3 py-3 rounded-full shadow-lg z-30 flex items-center space-x-2">
            <i class="fas fa-plus"></i>
        </button>
        <div class=" flex flex-row space-x-1 items-center p-1 bg-gray-500 text-white  rounded shadow-2xl ">
            <i class="fa fa-table text-xs" aria-hidden="true"></i>
            <div id="recordsCount" class="text-xs ">0 Records</div>
        </div>
        <!-- Floating Add Task Button -->

    </div>
    <?php require "../View/SideNavigation.php" ?>

    <?php require "../View/TaskModal.php";
    require "../View/BottomSheet.php";
    require "../View/ConfirmationModal.php";
    require "../Common/Loader.php";
    ?>
    <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-2"></div>
    <script src="../Script/cronParser.js"></script>

    <script src="../Script/HomePage.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

</body>

</html>