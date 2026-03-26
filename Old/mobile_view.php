<?php
require "../Modal/user_account.php";
require "../Modal/events.php";
$allUsers = getAllUsers();
$records = $con->execute_query("SELECT * from user_account;")
    ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://code.jquery.com/jquery-3.3.1.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        integrity="sha384-tViUnnbYAV00FLIhhi3v/dWt3Jxw4gZQcNoSCxCIFNJVCx7/D55/wXsrNIRANwdD" crossorigin="anonymous">
    <script src="https://cdn.datatables.net/2.3.7/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.5.1/js/dataTables.rowReorder.js"></script>
    <script src="https://cdn.datatables.net/rowreorder/1.5.1/js/rowReorder.dataTables.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.8/js/responsive.dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.7/css/dataTables.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/rowreorder/1.5.1/css/rowReorder.dataTables.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.8/css/responsive.dataTables.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .swipe-row {
            touch-action: none;
        }

        tr.swipe-green {
            background-color: #bbf7d0 !important;
            /* green-200 */
        }

        tr.swipe-red {
            background-color: #fecaca !important;
            /* red-200 */
        }

        table tr td {
            font-size: 12px !important;
            font-family: verdana;
            text-align: start !important;
        }

        table tr td:nth-child(3) {
            text-align: center !important;
        }

        table tr th {
            text-align: start !important;
        }

        .dt-paging-button {
            padding: 3px 8px !important;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="p-2">
        <select id="user_select"
            class="block w-full sm:w-64 px-4 py-2 border border-gray-300 rounded-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
            <option value="-1" selected>All</option>
            <?php while ($row = $allUsers->fetch_assoc()): ?>
                <option value="<?= $row['uid'] ?>">
                    <?= $row['first_name'] ?>
                </option>
            <?php endwhile; ?>
        </select>
        <div class="flex flex-row justify-center pt-4 pb-2">
            <div class="inline-flex border border-gray-300 rounded-md overflow-hidden">
                <button id="filterAll" data-status="all"
                    class="text-xs px-4 py-2 bg-blue-500 text-white focus:outline-none">
                    All
                </button>
                <button data-status="pending" class="text-xs px-4 py-2 bg-white text-gray-700 focus:outline-none">
                    Pending
                </button>
                <button data-status="finished" class="text-xs px-4 py-2 bg-white text-gray-700 focus:outline-none">
                    Finished
                </button>
            </div>
        </div>
        <div class="flex flex-row justify-center">
            <!-- Trigger button -->
            <button id="openModalBtn" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 text-sm">
                Add Task 📋
            </button>
        </div>
    </div>
    <div>

        <table id="mobile_view_table">
            <thead style="font-size: 13px !important;">
                <tr>
                    <th>ID</th> <!-- REQUIRED -->
                    <th>Date</th>
                    <th>Task</th>
                    <th>Pri</th>
                    <th>Status</th>
                    <th>options</th>
                </tr>
            </thead>
            <!-- Table Data -->

            </tbody>

        </table>


    </div>
    <script>

        let table = $('#mobile_view_table').DataTable({
            createdRow: function (row, data, dataIndex) {
                $(row).addClass('table-row relative swipe-row');

                $(row).attr('data-id', data[0]);     // ID
                $(row).attr('data-task', data[2]);   // ✅ TASK DESCRIPTION
            },
            drawCallback: function () {
                attachSwipe(); // re-bind every time table redraws
            },
            layout: {
                topStart: null,
                topEnd: 'paging',
                bottomStart: 'info',
                bottomEnd: null,
            },

            responsive: true,
            scroller: true,
            scrollX: true,
            scrollY: '40vh',
            serverSide: true,
            paging: true,
            ordering: true,
            order: [],
            pageLength: 10,
            columnDefs: [
                {
                    targets: 0,
                    visible: false,
                    searchable: false
                }
            ],
            columns: [
                { data: 0 }, // ID (hidden)
                { data: 1 }, // Date
                { data: 2 }, // Task
                { data: 3 }, // Priority
                { data: 4 }, // Status
                { data: 5 }  // Options
            ],
            ajax: {
                url: './inventory_task_ssp_mobile_view.php',
                data: function (d) {
                    $uid = $('#user_select').val();
                    if ($uid != null && $uid != '') {
                        d.uid = $uid;
                    }
                },
                initComplete: function () {
                    let api = this.api();
                    api.columns().every(function () {
                        let column = this;
                        let title = $(column.footer()).text();
                        let input = $(
                            '<input type="text" class="footer-search" placeholder="🔍 ' +
                            title +
                            '" id="' +
                            title +
                            '"/>'
                        )
                            .appendTo($(column.footer()).empty())
                            .css({
                                width: "90%",
                                padding: "3px",
                                "box-sizing": "border-box",
                                "font-size": "13px",
                            });
                        var state = api.state.loaded();
                        if (state && state.columns[index].search.search) {
                            input.val(state.columns[index].search.search);
                            column.search(state.columns[index].search.search);
                        }

                        input.on("keyup change", function (e) {
                            if (column.search() !== this.value) {
                                column.search(this.value).draw();
                                localStorage.setItem(storageKey, this.value);
                            }
                        });
                    });
                },
            }
        });

        $('#back_button').click(() => {
            window.open("./inventory_task.php", "_self");
        });

        // Reload table when dropdown changes
        $('#user_select').on('change', function () {
            reloadTable();
        });

        const reloadTable = () => {
            table.ajax.reload();
        }

        $(document).ready(function () {


            var table = $('#mobile_view_table').DataTable();

            // Button click event
            $('div.inline-flex button').on('click', function () {
                var status = $(this).data('status');
                // Remove active styling from all buttons
                $('div.inline-flex button').removeClass('bg-blue-500 text-white')
                    .addClass('bg-white text-gray-700');

                // Add active styling to clicked button
                $(this).removeClass('bg-white text-gray-700')
                    .addClass('bg-blue-500 text-white');
                if (status !== "all") {
                    // Apply filter on the Status column (index 2)
                    table.column(3).search(status).draw();
                } else {
                    table.column(3).search("").draw();

                }

            });


        });

        function attachSwipe() {
            document.querySelectorAll('.swipe-row').forEach(row => {

                let startX = 0;
                let currentX = 0;
                let threshold = 80;

                row.addEventListener('touchstart', e => {
                    startX = e.touches[0].clientX;
                    row.style.transition = '';
                });

                row.addEventListener('touchmove', e => {
                    currentX = e.touches[0].clientX - startX;

                    // move entire row
                    row.style.transform = `translateX(${currentX}px)`;

                    if (currentX > 0) {
                        row.classList.add('swipe-green');
                        row.classList.remove('swipe-red');
                    } else {
                        row.classList.add('swipe-red');
                        row.classList.remove('swipe-green');
                    }
                });


                row.addEventListener('touchend', () => {
                    row.classList.remove("swipe-green")
                    row.classList.remove("swipe-red")

                    let task = row.dataset.task;

                    if (currentX < -threshold) {

                        let confirmed = confirm("Delete task '" + task + "' ?");

                        if (confirmed) {


                            fetch('./Modal/events.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `action=delete&id=${row.dataset.id}`
                            })
                                .then(res => res.json())
                                .then(data => {
                                    if (data.status === 'success') {
                                        row.remove(); // remove only after success
                                        reloadTable();
                                    }
                                });
                        } else {
                            // ❌ NO clicked
                            console.log("User cancelled");
                        }
                    }
                    else if (currentX > threshold) {

                        let confirmed = confirm("Mark task '" + task + "' as finished ?");

                        if (confirmed) {
                            fetch('./Modal/events.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded'
                                },
                                body: `action=markFinished&id=${row.dataset.id}`
                            })
                                .then(res => res.json())
                                .then(data => {
                                    reloadTable();
                                });

                        } else {
                            // ❌ NO clicked
                            console.log("User cancelled");
                        }
                    }

                    row.style.transform = 'translateX(0)';
                });
            });
        }
    </script>
    <?php require "../View/TaskModal.php";
    ?>
</body>

</html>