// flag to check if filter is applied
let isFilterApplied = false;

let table = $("#mobile_view_table").DataTable({
  serverSide: false,
  paging: false, // 👈 let server return all rows at once
  searching: false,
  ordering: false,
  info: false,
  // 👇 Remove scroller entirely, use plain CSS scroll
  scrollY: "calc(100vh - 64px)",
  scrollCollapse: true,

  ajax: {
    url: "../Modal/fetchTable.php",
    beforeSend: function () {},
    complete: function () {
      if (isFilterApplied) {
        showToast("Filter applied successfully!", "success");
        isFilterApplied = false;
        reloadTable();
      }
    },
    data: function (d) {
      d.export = false;
      d.loggedInUserId = window?.APP?.user?.id;
      d.isAdmin = window?.APP?.user?.isAdmin;
      d.filter = {
        title: $("#filterTitle").val(),
        status: $("#filterStatus").val(),
        priority: $("#filterPriority").val(),
        uid: $("#filterSelectedUser").attr("data-selectedids"),
        sortByDate: $("#sortDate").attr("data-isSelected"),
        sortDate: $("#sortDate").attr("data-sortdate"),
        sortByPriority: $("#sortPriority").attr("data-isSelected"),
        sortPriority: $("#sortPriority").attr("data-sortpriority"),
      };
    },
  },

  layout: {
    topStart: null,
    topEnd: null,
    bottomStart: null,
    bottomEnd: null,
  },

  columns: [
    { data: 0 },
    { data: 1 },
    { data: 2 },
    { data: 3 },
    { data: 4 },
    { data: 5 },
    { data: 6 },
    { data: 7 },
    { data: 8 },
  ],

  columnDefs: [
    { targets: 0, visible: false },
    { targets: 1, visible: false },
    { targets: 2, visible: false },
    { targets: 3, visible: false },
    { targets: 4, visible: false },
    { targets: 5, visible: false },
    { targets: 6, visible: false },
    { targets: 7, visible: false },
    { targets: 8, className: "p-0" },
  ],

  createdRow: function (row, data) {
    $(row).addClass("swipe-row border-b border-gray-300 bg-[#1c1f24]");
    $(row).attr("data-id", data[0]);
    $(row).attr("data-description", data[1]);
    $(row).attr("data-status", data[2]);
    $(row).attr("data-title", data[3]);
    $(row).attr("data-priority", data[4]);
    $(row).attr("data-user", data[5]);
    $(row).attr("data-cron", data[6]);
    $(row).attr("data-department", data[7]);
  },

  // 👇 Clean drawCallback — no height manipulation
  drawCallback: function () {
    cronParser();
    attachSwipe();
    const modal = document.getElementById("filterModal");
    const overlay = document.getElementById("filterOverlay");

    modal.classList.add("translate-y-full");
    overlay.classList.add("hidden");
    document.body.classList.remove("overflow-hidden");
  },
});

$("#back_button").click(() => {
  window.open("./inventory_task.php", "_self");
});

// Reload table when dropdown changes
$("#user_select").on("change", function () {
  reloadTable();
});

const reloadTable = () => {
  table.ajax.reload(null, false);

  setTimeout(() => {
    document
      .querySelector(".dt-scroll-body")
      .scrollTo({ top: 0, behavior: "instant" });
  }, 50);
};

function attachSwipe() {
  document.querySelectorAll(".swipe-row").forEach((row) => {
    // read status from dataset
    const isDone = row.dataset.status === "finished";

    let startX = 0;
    let currentX = 0;
    let threshold = 100;

    row.addEventListener("touchstart", (e) => {
      startX = e.touches[0].clientX;
      row.style.transition = "";
    });

    row.addEventListener("touchmove", (e) => {
      currentX = e.touches[0].clientX - startX;
      if (isDone && currentX > 0) return;

      const activationThreshold = 20; // 👈 small dead zone

      // 👇 ignore tiny movements
      if (Math.abs(currentX) < activationThreshold) {
        return;
      }

      row.style.transform = `translateX(${currentX}px)`;

      if (currentX > 0) {
        row.classList.add("swipe-green");
        row.classList.remove("swipe-red");
      } else {
        row.classList.add("swipe-red");
        row.classList.remove("swipe-green");
      }
    });

    row.addEventListener("touchend", async () => {
      if (isDone && currentX > 0) return;

      row.classList.remove("swipe-green");
      row.classList.remove("swipe-red");

      let task = row.dataset.title;

      if (currentX < -threshold) {
        const confirmed = await showConfirm({
          title: "Delete Task",
          message: `Delete task "${task}" ?`,
        });
        if (confirmed) {
          fetch("../Modal/events.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `action=delete&id=${row.dataset.id}`,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data.status === "success") {
                row.remove(); // remove only after success
                reloadTable();
                showToast("Task deleted successfully!", "success");
              } else {
                showToast(data.message || "Delete failed", "error");
              }
            });
        } else {
          // ❌ NO clicked
          console.log("User cancelled");
        }
      } else if (currentX > threshold) {
        const confirmed = await showConfirm({
          title: "Mark Finished",
          message: `Mark "${task}" as finished?`,
        });
        if (confirmed) {
          fetch("../Modal/events.php", {
            method: "POST",
            headers: {
              "Content-Type": "application/x-www-form-urlencoded",
            },
            body: `action=markFinished&id=${row.dataset.id}`,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data.status === "success") {
                reloadTable();
                showToast("Task marked finished!", "success");
              } else {
                showToast(data.message || "Task couldn't be updated", "error");
              }
            });
        } else {
          // ❌ NO clicked
          console.log("User cancelled");
        }
      }

      row.style.transform = "translateX(0)";
    });
  });
}

function escHandler(e) {
  if (e.key === "Escape") cleanup(false);
}
document.addEventListener("keydown", escHandler);

document
  .querySelector("#mobile_view_table")
  .addEventListener("click", function (e) {
    const btn = e.target.closest('button[name="editBtn"]');
    if (!btn) return;

    const row = btn.closest("tr");
    if (!row) return;

    const modal = document.getElementById("TaskModal");
    modal.dataset.mode = "editTask";
    modal.dataset.id = row.dataset.id;

    modal.classList.remove("hidden");

    // prefill
    document.getElementById("description").value =
      row.dataset.description || "";
    document.getElementById("title").value = row.dataset.title || "";
    document.getElementById("priority").value = row.dataset.priority || "1";
    document.getElementById("user").value = row.dataset.user || "";
    document.getElementById("cron").value = row.dataset.cron || "";
    document.getElementById("department").value = row.dataset.department || "";
    // hidden id for editing
    if (!document.getElementById("taskId")) {
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.id = "taskId";
      hidden.name = "id";
      modal.querySelector("form").appendChild(hidden);
    }
    document.getElementById("taskId").value = row.dataset.id;
    const cronField = document.getElementById("cron");
    const cronValidator = document.getElementById("cronValidator");
    if (cronField.value !== "") {
      cronValidator.style.display = "block";
      cronValidator.innerText =
        cronField.value !== "" ? cronToShortText(cronField.value) : "";
    }
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
    }

    function closeMenu() {
      sidebar.classList.add("-translate-x-full");
      overlay.classList.add("hidden");
      document.body.classList.remove("overflow-hidden");
    }

    menuBtn.addEventListener("click", openMenu);
    closeBtn.addEventListener("click", closeMenu);
    overlay.addEventListener("click", closeMenu);

    const btn = document.getElementById("logoutBtn");

    if (btn) {
      btn.addEventListener("click", () => {
        fetch("../Modal/auth.php", {
          method: "POST",
          credentials: "same-origin",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `action=logout`,
        }).then(() => {
          window.location.href = "login.php";
        });
      });
    }
    let debounceTimer;
    $("#applyFilter").on("click", function () {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(() => table.ajax.reload(), 500);
      isFilterApplied = true;
    });

    $("#resetFilter").on("click", function () {
      $("#filterTitle").val("");
      $("#filterDescription").val("");
      $("#filterPriority").val("");
      $("#filterSelectedUser").val("");
      $("#filterSelectedUser").attr("data-selectedids", "");
      $("#filterStatus").val("");
      isFilterApplied = true;
      table.ajax.reload();
    });
  },
  false,
);

function showToast(message, type = "success") {
  const container = document.getElementById("toast-container");

  const styles = {
    success: {
      bg: "bg-green-600",
      icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                     <path d="M5 13l4 4L19 7"/>
                   </svg>`,
    },
    error: {
      bg: "bg-red-600",
      icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                     <path d="M6 18L18 6M6 6l12 12"/>
                   </svg>`,
    },
    info: {
      bg: "bg-blue-600",
      icon: `<svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                     <circle cx="12" cy="12" r="10"/>
                     <path d="M12 16v-4M12 8h.01"/>
                   </svg>`,
    },
  };

  const toast = document.createElement("div");
  toast.className = `
        flex items-center gap-3 px-4 py-3 rounded-lg shadow-lg text-white text-sm
        transform transition-all duration-300 opacity-0 translate-y-2
        ${styles[type].bg}
    `;

  toast.innerHTML = `
        <div>${styles[type].icon}</div>
        <span class="flex-1">${message}</span>
        <button class="text-white/80 hover:text-white text-lg leading-none">&times;</button>
    `;

  // close button
  toast.querySelector("button").onclick = () => toast.remove();

  container.appendChild(toast);

  // animate in
  setTimeout(() => {
    toast.classList.remove("opacity-0", "translate-y-2");
  }, 10);

  // auto remove
  setTimeout(() => {
    toast.classList.add("opacity-0", "translate-y-2");
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}

$("#exportCSV").click(async () => {
  // Build payload

  $.ajax({
    url: "../Modal/fetchTable.php",
    type: "GET",
    data: {
      export: true,
      loggedInUserId: window?.APP?.user?.id,
      isAdmin: window?.APP?.user?.isAdmin,
      filter: {
        title: $("#filterTitle").val(),
        status: $("#filterStatus").val(),
        priority: $("#filterPriority").val(),
        uid: $("#filterSelectedUser").attr("data-selectedids"),
        sortByDate: $("#sortDate").attr("data-isSelected"),
        sortDate: $("#sortDate").attr("data-sortdate"),
        sortByPriority: $("#sortPriority").attr("data-isSelected"),
        sortPriority: $("#sortPriority").attr("data-sortpriority"),
      },
    },
    success: function (response) {
      const rows = response.data;

      if (!rows || rows.length === 0) {
        showToast("No data to export.", "error");
        return;
      }

      // CSV Headers
      const headers = ["ID", "Title", "Description", "Status", "Department ID"];

      // Build CSV rows — skip index 8 (HTML)
      const csvRows = rows.map(function (row) {
        return [
          row[0], // ID
          row[3], // Title
          row[1], // Description (multiline — wrap in quotes)
          row[2], // Status
          row[7], // Department ID
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
      link.setAttribute("download", "export_" + Date.now() + ".csv");
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
