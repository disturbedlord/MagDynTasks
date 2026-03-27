// flag to check if filter is applied
let isFilterApplied = false;

let table = $("#mobile_view_table").DataTable({
  serverSide: true,
  paging: true,
  searching: false,
  ordering: false,
  info: false,
  scroller: {
    displayBuffer: 100, // default is 10× visible rows
  },
  scrollCollapse: true,
  scrollY: "100vh", // 👈 important (scroll container)
  ajax: {
    url: "../Modal/fetchTable.php",
    beforeSend: function () {},
    complete: function () {
      if (isFilterApplied) {
        showToast("Filter applied successfully!", "success");
        isFilterApplied = !isFilterApplied;
      }
    },
    data: function (d) {
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
    bottomEnd: null, // 👈 removes pagination too
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
    {
      targets: 0,
      visible: false,
    },
    {
      targets: 8,
      className: "p-0",
    },
    {
      targets: 2, // 👈 hide task column
      visible: false,
    },
    {
      targets: 3, // 👈 hide task column
      visible: false,
    },
    {
      targets: 4, // 👈 hide task column
      visible: false,
    },
    {
      targets: 5, // 👈 hide task column
      visible: false,
    },
    {
      targets: 6, // 👈 hide task column
      visible: false,
    },
    {
      targets: 1, // 👈 hide task column
      visible: false,
    },
    {
      targets: 7, // 👈 hide task column
      visible: false,
    },
  ],

  createdRow: function (row, data) {
    $(row).addClass("swipe-row border-b border-gray-300 bg-[#1c1f24]");
    $(row).attr("data-id", data[0]);
    $(row).attr("data-description", data[1]);
    $(row).attr("data-status", data[2]);
    $(row).attr("data-title", data[3]); // Add title
    $(row).attr("data-priority", data[4]); // Add priority
    $(row).attr("data-user", data[5]); // Add user id
    $(row).attr("data-cron", data[6]); // Add cron
    $(row).attr("data-department", data[7]); // Add cron
  },

  drawCallback: function () {
    console.log("draw called");
    cronParser();
    const api = this.api();
    const rowCount = api.rows({ page: "current" }).count();
    const scrollBody = $(api.table().container()).find(".dt-scroll-body");
    if (scrollBody && rowCount < 6) {
      // few rows → remove scroll
      // Get Scroll Body height
      const table = document.getElementById("mobile_view_table");
      const height = table.offsetHeight;

      const dt_scrollBody = scrollBody[0];
      dt_scrollBody.style = `position: relative;height: ${height + 80}px;`;
    } else {
      const table = document.getElementById("mobile_view_table");
      const height = table.offsetHeight;
      const dt_scrollBody = scrollBody[0];
      dt_scrollBody.style = `position: relative; overflow: auto; height: ${height + 80}px;`;
    }

    attachSwipe(); // reuse your swipe logic
    // close filter modal
    window.closeFilter();
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
};

function attachSwipe() {
  document.querySelectorAll(".swipe-row").forEach((row) => {
    // read status from dataset
    const isDone = row.dataset.status === "finished";

    let startX = 0;
    let currentX = 0;
    let threshold = 80;

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
    console.log("User : ", row.dataset.user);
    // hidden id for editing
    if (!document.getElementById("taskId")) {
      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.id = "taskId";
      hidden.name = "id";
      modal.querySelector("form").appendChild(hidden);
    }
    document.getElementById("taskId").value = row.dataset.id;
    console.log("CRON CURRENT VALUE : ", document.getElementById("cron").value);
    const cronField = document.getElementById("cron");
    const cronValidator = document.getElementById("cronValidator");
    console.log("modal opened", cronField.value);
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
