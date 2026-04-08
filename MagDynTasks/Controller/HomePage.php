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

            <div id="scroll-container" style="height: calc(100vh - 64px); overflow-x:hidden;">
                <!-- Top Spinner -->
                <div id="topSpinner" class="hidden flex flex-row justify-center items-center space-x-1 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="spinner-2 w-5 h-5 shrink-0 animate-spin"
                        viewBox="0 0 256 256">
                        <path
                            d="M128 63.04c-5.104 0-9.28-4.176-9.28-9.28V16.64c0-5.104 4.176-9.28 9.28-9.28s9.28 4.176 9.28 9.28v37.12c0 5.104-4.176 9.28-9.28 9.28zm52.548 21.692c-2.32 0-4.756-.928-6.612-2.668-3.596-3.596-3.596-9.512 0-13.108l26.216-26.216c3.596-3.596 9.512-3.596 13.108 0s3.596 9.512 0 13.108l-26.216 26.216c-1.856 1.856-4.176 2.668-6.496 2.668zm58.812 52.548h-37.12c-5.104 0-9.28-4.176-9.28-9.28s4.176-9.28 9.28-9.28h37.12c5.104 0 9.28 4.176 9.28 9.28s-4.176 9.28-9.28 9.28zm-32.596 78.764c-2.32 0-4.756-.928-6.612-2.668l-26.216-26.216c-3.596-3.596-3.596-9.512 0-13.108s9.512-3.596 13.108 0l26.216 26.216c3.596 3.596 3.596 9.512 0 13.108-1.74 1.74-4.176 2.668-6.496 2.668zM128 248.64c-5.104 0-9.28-4.176-9.28-9.28v-37.12c0-5.104 4.176-9.28 9.28-9.28s9.28 4.176 9.28 9.28v37.12c0 5.104-4.176 9.28-9.28 9.28zm-78.764-32.596c-2.32 0-4.756-.928-6.612-2.668-3.596-3.596-3.596-9.512 0-13.108l26.216-26.216c3.596-3.596 9.512-3.596 13.108 0s3.596 9.512 0 13.108l-26.216 26.216c-1.74 1.74-4.06 2.668-6.496 2.668zm4.524-78.764H16.64c-5.104 0-9.28-4.176-9.28-9.28s4.176-9.28 9.28-9.28h37.12c5.104 0 9.28 4.176 9.28 9.28s-4.176 9.28-9.28 9.28zm21.692-52.548c-2.32 0-4.756-.928-6.612-2.668l-26.1-26.216c-3.596-3.596-3.596-9.512 0-13.108s9.512-3.596 13.108 0l26.216 26.216c3.596 3.596 3.596 9.512 0 13.108-1.856 1.856-4.176 2.668-6.612 2.668z"
                            data-original="#000000" />
                    </svg>
                    <div class="text-sm">Loading more data</div>
                </div>

                <table class="table ">
                    <tbody class="item-list" id="scroll-content"></tbody>
                </table>

                <!-- Bottom Spinner -->
                <div id="bottomSpinner" class="hidden flex flex-row justify-center items-center space-x-1 py-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="spinner-2 w-5 h-5 shrink-0 animate-spin"
                        viewBox="0 0 256 256">
                        <path
                            d="M128 63.04c-5.104 0-9.28-4.176-9.28-9.28V16.64c0-5.104 4.176-9.28 9.28-9.28s9.28 4.176 9.28 9.28v37.12c0 5.104-4.176 9.28-9.28 9.28zm52.548 21.692c-2.32 0-4.756-.928-6.612-2.668-3.596-3.596-3.596-9.512 0-13.108l26.216-26.216c3.596-3.596 9.512-3.596 13.108 0s3.596 9.512 0 13.108l-26.216 26.216c-1.856 1.856-4.176 2.668-6.496 2.668zm58.812 52.548h-37.12c-5.104 0-9.28-4.176-9.28-9.28s4.176-9.28 9.28-9.28h37.12c5.104 0 9.28 4.176 9.28 9.28s-4.176 9.28-9.28 9.28zm-32.596 78.764c-2.32 0-4.756-.928-6.612-2.668l-26.216-26.216c-3.596-3.596-3.596-9.512 0-13.108s9.512-3.596 13.108 0l26.216 26.216c3.596 3.596 3.596 9.512 0 13.108-1.74 1.74-4.176 2.668-6.496 2.668zM128 248.64c-5.104 0-9.28-4.176-9.28-9.28v-37.12c0-5.104 4.176-9.28 9.28-9.28s9.28 4.176 9.28 9.28v37.12c0 5.104-4.176 9.28-9.28 9.28zm-78.764-32.596c-2.32 0-4.756-.928-6.612-2.668-3.596-3.596-3.596-9.512 0-13.108l26.216-26.216c3.596-3.596 9.512-3.596 13.108 0s3.596 9.512 0 13.108l-26.216 26.216c-1.74 1.74-4.06 2.668-6.496 2.668zm4.524-78.764H16.64c-5.104 0-9.28-4.176-9.28-9.28s4.176-9.28 9.28-9.28h37.12c5.104 0 9.28 4.176 9.28 9.28s-4.176 9.28-9.28 9.28zm21.692-52.548c-2.32 0-4.756-.928-6.612-2.668l-26.1-26.216c-3.596-3.596-3.596-9.512 0-13.108s9.512-3.596 13.108 0l26.216 26.216c3.596 3.596 3.596 9.512 0 13.108-1.856 1.856-4.176 2.668-6.612 2.668z"
                            data-original="#000000" />
                    </svg>
                    <div class="text-sm">Loading more data</div>
                </div>
            </div>

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


    <div id="toast-container" class="fixed top-5 right-5 z-50 space-y-2"></div>
    <script src="../Script/cronParser.js"></script>
    <script src="../Script/listswipe.js"></script>
    <script src="../Script/InfiniteScroll.js"></script>
    <script src="../Script/HomePage.js"></script>
    <?php require "../View/TaskModal.php";
    require "../View/BottomSheet.php";
    require "../View/ConfirmationModal.php";
    require "../Common/Loader.php";
    ?>
    <script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>

</body>

</html>