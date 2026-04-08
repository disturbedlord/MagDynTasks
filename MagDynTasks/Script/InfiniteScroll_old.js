document.addEventListener("DOMContentLoaded", function () {
  const pageSize = 20;
  let currentPage = 0;
  let totalRecords = 0;
  const pageCache = new Map();

  const clusterize = new Clusterize({
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

  async function fetchPage(pageIndex) {
    if (pageCache.has(pageIndex)) {
      return pageCache.get(pageIndex);
    }

    const res = await $.ajax({
      url: "../Modal/fetchTable.php",
      method: "GET",
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

  let allRows = []; // Track currently rendered rows

  async function loadNextPage(dir) {
    const scrollEl = document.getElementById("scroll-container");
    const prevScrollTop = scrollEl.scrollTop;
    if (dir === 0) {
      const data = await fetchPage(currentPage);
      const rows = data.map((row) => {
        const result = row[8]; // row in html
        const rootDiv = `<div data-page='${currentPage}'>${result}</div>`;
        // rootDiv.attr("data-page", currentPage);
        return rootDiv;
      });

      // Append new rows to the array
      allRows.push(...rows);

      // Reference to scroll element

      // Remove old page (2 pages back)
      const oldPage = currentPage - 2;
      if (oldPage >= 0) {
        let removedHeight = 0;

        // Wrap TR in table/tbody for proper measurement
        $(`div[data-page="${oldPage}"]`).each(function () {
          // outerHeight(true) includes padding, border, and margin
          removedHeight += $(this).outerHeight(true);
        });

        // Filter rows array
        allRows = allRows.filter(
          (rowHtml) => !rowHtml.includes(`data-page='${oldPage}'`),
        );

        // Update Clusterize
        clusterize.update(allRows);

        // Adjust scrollTop to compensate for removed rows
        scrollEl.scrollTop = prevScrollTop - removedHeight;
      } else {
        // Just update normally
        clusterize.update(allRows);
      }

      currentPage++;
    } else {
      // Scroll Up
      const topPage = $("div[data-page]").first().data("page");
      //  $(`tr[data-page="${topPage}"]`).each(function () {
      //         // outerHeight(true) includes padding, border, and margin
      //         removedHeight += $(this).outerHeight(true);
      //     });

      const oldRows = pageCache.get(topPage - 1).map((row) => {
        const result = row[8]; // row in html
        const rootDiv = `<div data-page='${topPage - 1}'>${result}</div>`;
        // rootDiv.attr("data-page", currentPage);
        return rootDiv;
      });

      allRows = [...oldRows, ...allRows];
      clusterize.update(allRows);
      let addedHeight = 0;
      $(`div[data-page="${topPage - 1}"]`).each(function () {
        // outerHeight(true) includes padding, border, and margin
        addedHeight += $(this).outerHeight(true);
      });
      scrollEl.scrollTop = prevScrollTop + addedHeight;
    }
  }
  loadNextPage(0);

  $("#loadNext").on("click", () => {
    if (currentPage * pageSize < totalRecords) {
      loadNextPage(0);
    }
  });
  $("#loadPrev").on("click", () => {
    if (currentPage * pageSize < totalRecords) {
      loadNextPage(1);
    }
  });
});
