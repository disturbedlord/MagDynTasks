// flag to check if filter is applied
let isFilterApplied = false;

$("#back_button").click(() => {
  window.open("./inventory_task.php", "_self");
});

const scrollToTop = () => {
  setTimeout(() => {
    document
      .querySelector("#scroll-container")
      .scrollTo({ top: 0, behavior: "instant" });
  }, 50);
};

const reloadTable = (isFilterReload) => {
  scrollToTop();
};

function escHandler(e) {
  if (e.key === "Escape") cleanup(false);
}
document.addEventListener("keydown", escHandler);

document
  .querySelector("#scroll-content")
  .addEventListener("click", function (e) {
    const btn = e.target.closest('button[name="editBtn"]');
    if (!btn) return;

    const row = btn.closest("tr");
    if (!row) return;

    if (!AllowAction(row)) {
      showToast("Cannot perform this action", "error");
      return;
    }

    PopulateTasks(row);
  });

document.addEventListener(
  "DOMContentLoaded",
  function () {
    const menuBtn = document.getElementById("menuBtn");
    const closeBtn = document.getElementById("closeBtn");
    const sidebar = document.getElementById("sidebar");
    const overlay = document.getElementById("overlay");

    function openMenu() {
      sidebar.classList.remove("-translate-x-full");
      overlay.classList.remove("hidden");
      document.body.classList.add("overflow-hidden");
      sidebar.classList.add("open");
    }

    function closeMenu() {
      sidebar.classList.add("-translate-x-full");
      overlay.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
      sidebar.classList.remove("open");
    }

    menuBtn.addEventListener("click", openMenu);
    closeBtn.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);
  },
  false,
);

$("#exportCSV").click(async () => {
  // Build payload
  $("#loader").toggleClass("hidden");
  $("#loaderText").text("Exporting Data");

  const user = window?.xoid?.user?.value?.value;

  $.ajax({
    url: "../Modal/fetchTable.php",
    type: "GET",
    data: {
      loggedInUserId: user?.id,
      isAdmin: user?.isAdmin,
      export: true,
      filter: getFilters(),
    },
    success: function (response) {
      $("#loader").toggleClass("hidden");

      const rows = response.data;

      if (!rows || rows.length === 0) {
        showToast("No data to export.", "error");
        return;
      }

      // CSV Headers
      const headers = [
        "Task Id",
        "Status",
        "Task",
        "Date",
        "Priority",
        "Created By",
        "Assigned To",
      ];

      // Build CSV rows — skip index 8 (HTML)
      const csvRows = rows.map(function (row) {
        return [
          row[0], // Task ID
          row[2], // Status
          row[3], // Task
          row[9], // Date
          row[4], // Priority
          row[11], // Created By
          row[13].join(), // Assigned To
        ].map(function (cell) {
          // Escape cell: wrap in quotes, escape inner quotes
          const val = String(cell ?? "").replace(/"/g, '""');
          return `"${val}"`;
        });
      });

      // Combine headers + rows
      const csvContent = [
        headers.join(","),
        ...csvRows.map((r) => r.join(",")),
      ].join("\n");

      // Trigger download
      const blob = new Blob([csvContent], { type: "text/csv;charset=utf-8;" });
      const url = URL.createObjectURL(blob);
      const link = document.createElement("a");

      link.setAttribute("href", url);
      link.setAttribute(
        "download",
        `Task(${String(new Date().getDate()).padStart(2, "0")}-${String(new Date().getMonth() + 1).padStart(2, "0")}-${new Date().getFullYear()}).csv`,
      );
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
      URL.revokeObjectURL(url);

      showToast("Export Completed", "success");
    },
    error: function (xhr, status, error) {
      console.error("Export failed:", error);
    },
  });
});
