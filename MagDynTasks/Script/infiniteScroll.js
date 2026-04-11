const pageSize = 20;
let totalRecords = 0;
const pageCache = new Map();
let allRows = []; // visible window of rows fed to Clusterize
let clusterize = undefined;
let scrollReached = false;
let totalPages = 0;

// ── Spacer state ──────────────────────────────────────────────────────────────
// Instead of removing rows and correcting scrollTop (which always jumps),
// we grow a spacer <tr> by the exact height of removed rows.
// scrollHeight stays constant → scrollbar thumb never moves.
let topSpacerHeight = 0;
let bottomSpacerHeight = 0;

// ── Helpers ───────────────────────────────────────────────────────────────────

const getFirstRenderedPage = () => {
  const first = $("tr[data-page]").first();
  return first.length ? parseInt(first.data("page")) : 0;
};

const getLastRenderedPage = () => {
  const last = $("tr[data-page]").last();
  return last.length ? parseInt(last.data("page")) : 0;
};

// Measure total pixel height of all rows belonging to a page.
// MUST be called before those rows leave the DOM.
const measurePageHeight = (pageNo) => {
  let h = 0;
  $(`tr[data-page="${pageNo}"]`).each(function () {
    h += $(this).outerHeight(true);
  });
  return h;
};

// Keep allRows[0] and allRows[last] in sync with the spacer heights.
const syncSpacers = () => {
  allRows[0] = `<tr id="top-spacer-row" style="height:${topSpacerHeight}px;border:none;"><td></td></tr>`;
  allRows[allRows.length - 1] =
    `<tr id="bottom-spacer-row" style="height:${bottomSpacerHeight}px;border:none;"><td></td></tr>`;
};

const nextPageToFetch = () => {
  const next = $("tr[data-page]").length === 0 ? 0 : getLastRenderedPage() + 1;
  totalPages = Math.ceil(totalRecords / pageSize);
  if (totalRecords > 0) {
    if (next >= Math.ceil(totalRecords / pageSize)) return -1;
  }
  return next;
};

const NoResult = () => {
  return `<div>
      <p class='text-sm'>No Result</p>
    </div>`;
};

const EndOfList = () => {
  return `<tr><td><div id="endOfList" class="p-1 text-xs text-gray-500 text-center">
                    <p>— You've reached the end —</p>
                </div></td></tr>`;
};

// ── Fetch ─────────────────────────────────────────────────────────────────────

const getFilters = () => {
  const Store = window.xoid;
  if (Object.keys(Store?.filter?.value?.value).length > 0) {
    return Store?.filter?.value?.value;
  } else {
    return {
      title: $("#filterTitle").val(),
      status: $("#filterStatus").val(),
      priority: $("#filterPriority").val(),
      uid: $("#filterSelectedUser").attr("data-selectedids"),
      sortByDate: $("#sortDate").attr("data-isSelected"),
      sortDate: $("#sortDate").attr("data-sortdate"),
      sortByPriority: $("#sortPriority").attr("data-isSelected"),
      sortPriority: $("#sortPriority").attr("data-sortpriority"),
    };
  }
};

async function fetchPage(pageIndex) {
  if (pageCache.has(pageIndex)) return pageCache.get(pageIndex);
  const user = window?.xoid?.user?.value?.value;

  const res = await $.ajax({
    url: "../Modal/fetchTable.php",
    method: "GET",
    complete: function (data) {
      if (!$("#loader").hasClass("hidden")) $("#loader").toggleClass("hidden");

      if (isFilterApplied) {
        showToast("Filter applied successfully!", "success");
        isFilterApplied = false;
        reloadTable(true);
      }

      if (data) {
        const recordCount = data?.responseJSON?.recordsFiltered;
        $("#recordsCount").text(`${recordCount} Records`);
      }
    },
    data: {
      start: pageIndex * pageSize,
      length: pageSize,
      loggedInUserId: user?.id,
      isAdmin: user?.isAdmin,
      filter: getFilters(),
    },
  });

  totalRecords = res.recordsFiltered;
  pageCache.set(pageIndex, res.data);
  return res.data;
}

// ── Row renderer ──────────────────────────────────────────────────────────────

const renderRow = (row, data, pageNo) => {
  const dummyHead = document.createElement("tbody");
  dummyHead.insertAdjacentHTML("beforeend", row);
  const td = $(dummyHead).find("td").first();
  td.attr("class", "p-0");
  const TR = $(dummyHead).find("tr").first();
  TR.addClass("swipe-row border-b border-gray-300 bg-[#1c1f24]");
  TR.attr("data-id", data[0]);
  TR.attr("data-task", data[1]);
  TR.attr("data-status", data[2]);
  TR.attr("data-title", data[3]);
  TR.attr("data-priority", data[4]);
  TR.attr("data-creator", data[5]);
  TR.attr("data-assignee", data[12]);

  TR.attr("data-cron", data[6]);
  TR.attr("data-department", data[7]);
  TR.attr("data-page", pageNo);
  return dummyHead.innerHTML;
};

// ── Core load ─────────────────────────────────────────────────────────────────

async function loadNextPage(direction, options = {}) {
  const scrollEl = document.getElementById("scroll-container");
  // reset banner
  switch (direction) {
    // ── Scroll DOWN ───────────────────────────────────────────────────────────
    case "down": {
      const pageToFetch = nextPageToFetch();

      if (pageToFetch + 1 === totalPages && bottomSpacerHeight > 0) {
        bottomSpacerHeight = 0;
        syncSpacers();
      }
      if (pageToFetch === -1) {
        return;
      }
      const data = await fetchPage(pageToFetch);
      const newRows = data.map((row) => renderRow(row[8], row, pageToFetch));
      if (bottomSpacerHeight > 0) {
        const measureContainer = document.createElement("tbody");
        measureContainer.style.position = "absolute";
        measureContainer.style.visibility = "hidden";
        document.body.appendChild(measureContainer);

        measureContainer.innerHTML = newRows.join("");

        let realAddedHeight = 0;
        $(`tr[data-page="${pageToFetch}"]`).each(function () {
          realAddedHeight += $(this).outerHeight(true);
        });
        document.body.removeChild(measureContainer);

        bottomSpacerHeight -= realAddedHeight;
      }

      if (pageToFetch + 1 >= Math.ceil(totalRecords / pageSize)) {
        //   Last Page
        newRows.push(EndOfList());
      }
      // Insert before bottom spacer sentinel (last slot)
      allRows.splice(allRows.length - 1, 0, ...newRows);

      const pageToRemove = pageToFetch - 2;
      if (pageToRemove >= 0 && pageCache.has(pageToRemove)) {
        // ── Measure height BEFORE repaint ─────────────────────────────────
        const removedHeight = measurePageHeight(pageToRemove);

        // ── Drop rows from allRows ────────────────────────────────────────
        allRows = allRows.filter(
          (html) => !html.includes(`data-page="${pageToRemove}"`),
        );

        // ── Grow TOP spacer by exact removed height ───────────────────────
        // scrollHeight = topSpacer + realRows + bottomSpacer
        // topSpacer grows by removedHeight → scrollHeight unchanged
        // → scrollbar thumb stays put, zero correction needed
        topSpacerHeight += removedHeight;
        syncSpacers();
      }

      clusterize.update(allRows);
      break;
    }

    // ── Scroll UP ─────────────────────────────────────────────────────────────
    case "up": {
      const scrollEl = document.getElementById("scroll-container");

      const topPage = getFirstRenderedPage();
      const pageToPrepend = topPage - 1;
      if (pageToPrepend < 0) {
        return;
      }

      if (pageToPrepend === 0) {
        if (topSpacerHeight > 0) {
          topSpacerHeight = 0;
          syncSpacers();
        }
      }

      const data = pageCache.has(pageToPrepend)
        ? pageCache.get(pageToPrepend)
        : await fetchPage(pageToPrepend);

      const newRows = data.map((row) => renderRow(row[8], row, pageToPrepend));

      // ── Measure OFF-DOM ─────────────────────────────────────────
      const measureContainer = document.createElement("tbody");
      measureContainer.style.position = "absolute";
      measureContainer.style.visibility = "hidden";
      document.body.appendChild(measureContainer);

      measureContainer.innerHTML = newRows.join("");

      let realAddedHeight = 0;
      $(`tr[data-page="${pageToPrepend}"]`).each(function () {
        realAddedHeight += $(this).outerHeight(true);
      });
      document.body.removeChild(measureContainer);

      // ── Insert rows at top ──────────────────────────────────────
      allRows.splice(1, 0, ...newRows);

      // ── Remove bottom page ──────────────────────────────────────
      const pageToRemove = pageToPrepend + 2;

      if (pageToRemove < totalPages && pageCache.has(pageToRemove)) {
        const removedHeight = measurePageHeight(pageToRemove);

        allRows = allRows.filter(
          (html) => !html.includes(`data-page="${pageToRemove}"`),
        );

        bottomSpacerHeight += removedHeight;
      }

      // ── Adjust top spacer ───────────────────────────────────────
      topSpacerHeight = Math.max(0, topSpacerHeight - realAddedHeight);
      syncSpacers();

      // ── Save scroll position ────────────────────────────────────
      const prevScrollTop = scrollEl.scrollTop;

      clusterize.update(allRows);
      scrollEl.scrollTop = prevScrollTop;

      break;
    }
    case "reload": {
      pageCache.clear();
      let tempRows = [];
      const pagesToRender = options?.pagesToRender;
      for (let i = 0; i < pagesToRender?.length; i++) {
        const data = await fetchPage(pagesToRender[i]);

        const newRows = data.map((row) =>
          renderRow(row[8], row, pagesToRender[i]),
        );
        tempRows = [...tempRows, ...newRows];
      }

      allRows = [
        `<tr id="top-spacer-row" style="height:0px;border:none;"><td></td></tr>`,
        `<tr id="bottom-spacer-row" style="height:0px;border:none;"><td></td></tr>`,
      ];

      allRows.splice(1, 0, ...tempRows);
      clusterize.update(allRows);
      topSpacerHeight = 0;
      bottomSpacerHeight = 0;
      syncSpacers();
      reloadTable();
      break;
    }
  }

  if (allRows.length === 2) {
    //   No Records, only top and bottom spacer
    allRows.splice(1, 0, NoResult());
    Repaint();
  }

  attachRowCallback();
  return true;
}

// ── Init ──────────────────────────────────────────────────────────────────────

document.addEventListener("DOMContentLoaded", function () {
  // Seed with sentinel spacers only — real rows go between them
  allRows = [
    `<tr id="top-spacer-row" style="height:0px;border:none;"><td></td></tr>`,
    `<tr id="bottom-spacer-row" style="height:0px;border:none;"><td></td></tr>`,
  ];

  clusterize = new Clusterize({
    scrollId: "scroll-container",
    contentId: "scroll-content",
    rows: allRows,
    no_data_text: "Loading...",
  });

  const scrollContainer = document.getElementById("scroll-container");

  scrollContainer.addEventListener("scroll", async () => {
    const loaderVisible = !$("#loader").hasClass("hidden");
    if (scrollReached || loaderVisible) return;

    const { scrollTop, clientHeight, scrollHeight } =
      document.getElementById("scroll-container");
    const nearBottom =
      scrollTop + clientHeight >= scrollHeight - bottomSpacerHeight - 100;
    // ✅ Fix: Account for top spacer height
    const nearTop = scrollTop <= topSpacerHeight + 100;

    if (nearBottom) {
      if (nextPageToFetch() === -1) return;
      scrollReached = true;
      $("#bottomSpinner").toggleClass("hidden");
      await loadNextPage("down");
      $("#bottomSpinner").toggleClass("hidden");
      scrollReached = false;
    } else if (nearTop) {
      if (getFirstRenderedPage() === 0) return; // Already at first page
      scrollReached = true;
      $("#topSpinner").toggleClass("hidden");
      await loadNextPage("up");
      $("#topSpinner").toggleClass("hidden");
      scrollReached = false;
    }
  });
  $("#loader").toggleClass("hidden");
  $("#loaderText").text("Fetching Data");
  loadNextPage("down");
});

// ── Public API ────────────────────────────────────────────────────────────────

const Repaint = () => {
  if (totalRecords > 0) {
    $("#recordsCount").text(`${totalRecords} Records`);
  }
  clusterize.update(allRows);
  attachRowCallback();
};

const UpdateSpecificRow = (pageNo, rowIndex, row) => {
  try {
    rowIndex = rowIndex + 1; // at top there is a topSpacer which needs to be accounted for
    allRows[rowIndex] = renderRow(row[8], row, pageNo);
    Repaint();
    return true;
  } catch (err) {
    throw err;
  }
};

const RemoveSpecificRow = (rowIndex) => {
  try {
    rowIndex = rowIndex + 1;
    allRows.splice(rowIndex, 1);
    //   Decrease total Records count
    totalRecords--;
    Repaint();

    return true;
  } catch (err) {
    throw err;
  }
};

const attachRowCallback = () => {
  cronParser();
  //   attachSwipe();
  const modal = document.getElementById("filterModal");
  const overlay = document.getElementById("filterOverlay");
  modal.classList.add("translate-y-full");
  overlay.classList.add("hidden");
  document.body.classList.remove("overflow-hidden");
};

const TableReload = () => {
  allRows = [
    `<tr id="top-spacer-row" style="height:0px;border:none;"><td></td></tr>`,
    `<tr id="bottom-spacer-row" style="height:0px;border:none;"><td></td></tr>`,
  ];
  topSpacerHeight = 0;
  bottomSpacerHeight = 0;
  pageCache.clear();
  totalRecords = 0;
  clusterize.update(allRows);
  loadNextPage("down");
};

$("#loadNext").on("click", () => loadNextPage("down"));
$("#loadPrev").on("click", () => loadNextPage("up"));

const reloadView = () => {
  $("#loader").toggleClass("hidden");
  $("#loaderText").text("Fetching latest data");
  allRows.length = 0;
  loadNextPage("reload", { pagesToRender: [0] });
};

$("#scroll-content").listSwipe({
  // The item in the list that has the side actions
  itemSelector: ">",

  // The width of action button
  itemActionWidth: 80,

  // Whether there is an action on the left
  leftAction: true,

  // Whether there is an action on the right
  rightAction: true,

  // Percent threshold for snapping to position on touch end
  snapThreshold: 0.8,

  // Snap animation duration
  snapDuration: 200,

  // Close other item actions if a new one is moved
  closeOnOpen: true,

  // Number of pixels in the Y-axis before preventing swiping
  maxYDelta: 40,

  // Number of pixels in the X-axis before allowing swiping
  initialXDelta: 25,
});
