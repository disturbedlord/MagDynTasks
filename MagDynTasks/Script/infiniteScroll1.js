const pageSize = 20; // Records to fetch per backend Call
let pageToFetch = 0; // Track page being rendered
let totalRecords = 0; // Total records in DB
const pageCache = new Map(); // pageNo : pageData
let allRows = []; // Track currently rendered rows (GLOBAL STATE)
let clusterize = undefined;
let scrollReached = false;
let IsLastPage = false;

document.addEventListener("DOMContentLoaded", function () {
  clusterize = new Clusterize({
    scrollId: "scroll-container",
    contentId: "scroll-content",
    rows: [], // start empty
    no_data_text: "Loading...",
    callbacks: {
      clusterWillChange: function () {
        // optional: show loader
      },
    },
  });

  const scrollContainer = document.getElementById("scroll-container");

  scrollContainer.addEventListener("scroll", async () => {
    if (
      !scrollReached &&
      scrollContainer.scrollTop + scrollContainer.clientHeight >=
        scrollContainer.scrollHeight - 100
    ) {
      console.log("Bottom reached");
      if (nextPageToFetch() === -1) return;
      scrollReached = true;

      $("#bottomSpinner").toggleClass("hidden");

      await loadNextPage("down");
      $("#bottomSpinner").toggleClass("hidden");

      scrollReached = false;
    } else if (!scrollReached && scrollContainer.scrollTop <= 100) {
      const topRow = $("tr[data-page]").first().data("page") === 0;
      if (topRow) return;
      console.log("Top Reached");
      scrollReached = true;
      // TODO : Handle Scroll Top
      $("#topSpinner").toggleClass("hidden");
      await loadNextPage("up");
      $("#topSpinner").toggleClass("hidden");
      scrollReached = false;
    }
  });
});

const nextPageToFetch = () => {
  const nextPageToFetch =
    typeof $("tr[data-page]")?.last()?.data("page") === "number"
      ? parseInt($("tr[data-page]")?.last()?.data("page")) + 1
      : 0;
  if (totalRecords > 0) {
    const totalPages = totalRecords / pageSize;
    if (nextPageToFetch + 1 >= totalPages) IsLastPage = true;
    else IsLastPage = false;
    if (nextPageToFetch >= totalPages) return -1;
  }

  return nextPageToFetch;
};

async function fetchPage(pageIndex) {
  if (pageCache.has(pageIndex)) {
    return pageCache.get(pageIndex);
  }

  const res = await $.ajax({
    url: "../Modal/fetchTable.php",
    method: "GET",
    beforeSend: function () {
      // $("#loader").toggleClass("hidden");
      // $("#loaderText").text("Fetching Latest Data");
    },
    complete: function (data) {
      if (!$("#loader").hasClass("hidden")) {
        $("#loader").toggleClass("hidden");
      }

      if (isFilterApplied) {
        showToast("Filter applied successfully!", "success");
        isFilterApplied = false;
        reloadTable(true);
      }

      if (data) {
        const recordCount = data?.responseJSON?.recordsTotal;
        const recordsCountDiv = $("#recordsCount");
        recordsCountDiv.text(`${recordCount} Records`);
      }
    },
    data: {
      start: pageIndex * pageSize,
      length: pageSize,
      export: false,
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
  });

  totalRecords = res.recordsFiltered;
  pageCache.set(pageIndex, res.data);

  return res.data;
}

const delay = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function loadNextPage(direction) {
  const scrollEl = document.getElementById("scroll-container");
  let prevScrollTop = scrollEl.scrollTop;
  console.log("current : ", prevScrollTop);
  await delay(400); // ⏳ 1 second delay
  switch (direction) {
    case "down": {
      pageToFetch = nextPageToFetch();
      if (pageToFetch === -1) return;

      const data = await fetchPage(pageToFetch);
      const rows = data.map((row) => {
        const node = renderRow(row[8], row, pageToFetch); // returns a string
        return node;
      });

      // Append new rows to the array
      allRows.push(...rows);

      // Remove old page (2 pages back)
      const oldPage = pageToFetch - 2;
      if (oldPage >= 0) {
        const { updatedRows, scrollPositionUpdate } = truncateOldPage(
          oldPage,
          allRows,
        );
        prevScrollTop = scrollEl.scrollTop;
        // update global state
        allRows = updatedRows;
        console.log("latest 2 : ", scrollEl.scrollTop);
        // Update Clusterize (Repaint UI)
        clusterize.update(allRows);
        console.log("ToRemove : ", scrollPositionUpdate);
        console.log("New : ", scrollEl.scrollTop - scrollPositionUpdate);
        // Adjust scrollTop to compensate for removed rows
        console.log("latest : ", scrollEl.scrollTop);
        scrollEl.scrollTop = prevScrollTop - scrollPositionUpdate;
      } else {
        // Just update normally
        clusterize.update(allRows);
      }

      break;
    }
    case "up": {
      // Find top page
      const topPage = $("tr[data-page]").first().data("page");

      const pageToPrepend = topPage - 1;
      if (pageToPrepend < 0) return true;

      const rowsToAdd = pageCache.get(pageToPrepend)?.map((row) => {
        const node = renderRow(row[8], row, pageToPrepend); // returns a string
        return node;
      });

      // Add to Existing list of rows
      allRows = [...rowsToAdd, ...allRows];

      const oldPageToRemove = pageToPrepend + 2;
      // scrollPositionUpdate is usless since we dont need to reset scroll
      // on scroll up as the the content is already out of view
      const { updatedRows } = truncateOldPage(oldPageToRemove, allRows);

      // update GLOBAL STATE
      allRows = updatedRows;
      // Repaint UI
      clusterize.update(allRows);

      // Track content added to reset scroll height
      let addedHeight = 0;
      $(`tr[data-page="${pageToPrepend}"]`).each(function () {
        // outerHeight(true) includes padding, border, and margin
        addedHeight += $(this).outerHeight(true);
      });
      // reset scrollBar for smooth transition
      scrollEl.scrollTop = prevScrollTop + addedHeight;
      break;
    }
  }

  attachRowCallback();
  return true;
}

const truncateOldPage = (pageToRemove, currentRows) => {
  // track how much height is removed
  let removedHeight = 0;

  const elementHeight = $(`tr[data-page="${pageToRemove}"]`)
    .last()
    .outerHeight(true);
  const elementCount = $(`tr[data-page="${pageToRemove}"]`).length;

  // removedHeight -= tHeight + bHeight;
  // Filter rows array
  const updatedRows = currentRows.filter(
    (rowHtml) => !rowHtml.includes(`data-page="${pageToRemove}"`),
  );

  return { updatedRows, scrollPositionUpdate: elementHeight * elementCount };
};

// init
loadNextPage("down");

$("#loadNext").on("click", () => {
  loadNextPage("down");
});
$("#loadPrev").on("click", () => {
  loadNextPage("up");
});

const Repaint = () => {
  //   Repaint
  clusterize.update(allRows);
  attachRowCallback();
};

// Update Specific Row and repaint
const UpdateSpecificRow = (pageNo, rowIndex, row) => {
  try {
    const UpdatedRowRender = renderRow(row[8], row, pageNo);
    const newRows = allRows;
    newRows[rowIndex] = UpdatedRowRender;
    //   Update Global State
    allRows = newRows;
    Repaint();

    return true;
  } catch (err) {
    throw err;
  }
};

const RemoveSpecificRow = (pageNo, rowIndex) => {
  try {
    //   Remove the index
    allRows.splice(rowIndex, 1);
    // repaint rows
    Repaint();
    return true;
  } catch (err) {
    throw err;
  }
};

const renderRow = (row, data, pageNo) => {
  const dummyHead = document.createElement("tbody");
  dummyHead.insertAdjacentHTML("beforeend", row);
  const td = $(dummyHead).find("td").first();
  td.attr("class", "p-0");
  const TR = $(dummyHead).find("tr").first();
  $(TR).addClass("swipe-row border-b border-gray-300 bg-[#1c1f24]");
  $(TR).attr("data-id", data[0]);
  $(TR).attr("data-description", data[1]);
  $(TR).attr("data-status", data[2]);
  $(TR).attr("data-title", data[3]);
  $(TR).attr("data-priority", data[4]);
  $(TR).attr("data-user", data[5]);
  $(TR).attr("data-cron", data[6]);
  $(TR).attr("data-department", data[7]);
  $(TR).attr("data-page", pageNo);
  return dummyHead.innerHTML;
};

const attachRowCallback = () => {
  cronParser();
  attachSwipe();
  const modal = document.getElementById("filterModal");
  const overlay = document.getElementById("filterOverlay");

  modal.classList.add("translate-y-full");
  overlay.classList.add("hidden");
  document.body.classList.remove("overflow-hidden");
};

const TableReload = () => {
  // Reset global State
  allRows = [];
  loadNextPage("down");
};
