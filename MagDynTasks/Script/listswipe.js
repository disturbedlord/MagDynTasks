(function () {
  $.fn.listSwipe = function (options) {
    // Default setting if setting not given during initialization
    // Initialized in infiniteScroll.js
    var settings = $.extend(
      {
        itemSelector: ">", //The item in the list that has the side actions
        itemActionWidth: 80, //In pixels
        leftAction: true, //Whether there is an action on the left
        rightAction: true, //Whether there is an action on the right
        snapThreshold: 0.5, //Percent threshold for snapping to position on touch end
        snapDuration: 200, //Snap animation duration
        closeOnOpen: true, //Close other item actions if a new one is moved
        maxYDelta: 40, //Number of pixels in the Y-axis before preventing swiping
        initialXDelta: 25, //Number of pixels in the X-axis before allowing swiping
      },
      options,
    );

    return this.each(function () {
      var $list = $(this);

      $list
        .on("touchstart", settings.itemSelector, function (e) {
          if (e.touches.length > 1) return; // ignore multi-touch

          var $item = $(this);

          $item.stop();

          if (settings.closeOnOpen) {
            $list.find(settings.itemSelector).not($item).animate(
              {
                left: "0px",
              },
              settings.snapDuration,
            );
          }

          var touch = getTouchPosition(e);
          var rawStartLeft = $item.css("left");

          var data = {
            touchStart: touch,
            startLeft: rawStartLeft === "auto" ? 0 : parseInt(rawStartLeft),
            initialXDeltaReached: false,
            maxYDeltaReached: false,
          };

          $item.data("listSwipe", data);
        })
        .on("touchmove", settings.itemSelector, function (e) {
          var $item = $(this);

          var data = $item.data("listSwipe");
          var touch = getTouchPosition(e);

          if (data.maxYDeltaReached) {
            return;
          }

          var touchDelta = getTouchDelta(touch, data, settings);

          if (
            !data.maxYDeltaReached &&
            Math.abs(touchDelta.yDelta) > settings.maxYDelta
          ) {
            data.maxYDeltaReached = true;
            $item.animate(
              {
                left: "0px",
              },
              settings.snapDuration,
            );
          } else if (
            !data.initialXDeltaReached &&
            Math.abs(touchDelta.xDelta) > settings.initialXDelta
          ) {
            data.initialXDeltaReached = true;
            $item.css("left", touchDelta.xDelta + "px");
          } else if (data.initialXDeltaReached) {
            $item.css("left", touchDelta.xDelta + "px");
          }

          $item.data("listSwipe", data);
        })
        .on("touchend", settings.itemSelector, function (e) {
          var $item = $(this);

          const canSwipe = AllowAction($item);

          var data = $item.data("listSwipe");
          var touch = getTouchPosition(e);

          if (data.maxYDeltaReached) {
            return;
          }

          var touchDelta = getTouchDelta(touch, data, settings);

          var xThreshold =
            Math.abs(touchDelta.xDelta) / settings.itemActionWidth;
          if (xThreshold >= settings.snapThreshold) {
            if (touchDelta.xDelta < 0) {
              if (!canSwipe) {
                showToast("Permission denied", "error");
              } else
                // Swipe Right
                DeleteRow($item[0]);
            } else {
              if (!canSwipe) {
                showToast("Permission denied", "error");
              } else
                // Swipe left
                UpdateRowStatus($item[0]);
            }
          }
          touchDelta.xDelta = 0;

          $item.animate(
            {
              left: touchDelta.xDelta + "px",
            },
            settings.snapDuration,
          );
        });
    });
  };

  function getTouchPosition(event) {
    return {
      x: event.changedTouches[0].clientX,
      y: event.changedTouches[0].clientY,
    };
  }

  function getTouchDelta(touch, data, settings) {
    var xDelta = touch.x - data.touchStart.x + data.startLeft;
    var yDelta = touch.y - data.touchStart.y;

    if (!settings.rightAction && xDelta < 0) {
      xDelta = 0;
    }

    if (!settings.leftAction && xDelta > 0) {
      xDelta = 0;
    }

    return {
      xDelta: xDelta,
      yDelta: yDelta,
    };
  }
})();

const DeleteRow = async (row) => {
  let task = row.dataset.title;
  if (task === "" || task === undefined) return;
  const confirmed = await showConfirm({
    title: "Delete Task",
    message: `Delete task "${task}" ?`,
  });
  if (confirmed) {
    $("#loader").toggleClass("hidden");
    $("#loaderText").text("Deleting Record");
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
          const rowIndex = $(row).prevAll("tr").length - 1;

          if (RemoveSpecificRow(rowIndex)) {
            showToast("Task deleted successfully!", "success");
          }
        } else {
          showToast(data.message || "Delete failed", "error");
        }
        $("#loader").toggleClass("hidden");
      });
  } else {
    // ❌ NO clicked
    console.log("User cancelled");
  }
};

const UpdateRowStatus = async (row) => {
  let task = row.dataset.title;
  if (task === "" || task === undefined) return;
  const status = { finished: "pending", pending: "finished" }; //currentStatus -> nextStatus
  const nextRowActivity = status[row.dataset.status];
  const confirmed = await showConfirm({
    title: `Mark ${nextRowActivity === "finished" ? "Finished" : "Pending"}`,
    message: `Mark "${task}" as ${nextRowActivity === "finished" ? "finished" : "pending"}?`,
  });
  if (confirmed) {
    $("#loader").toggleClass("hidden");
    $("#loaderText").text("Updating Record");
    fetch("../Modal/events.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/x-www-form-urlencoded",
      },
      body: `action=${nextRowActivity}&id=${row.dataset.id}`,
    })
      .then((res) => res.json())
      .then((data) => {
        if (data.status === "success") {
          const pageNo = parseInt(row.getAttribute("data-page"));
          const rowIndex = $(row).prevAll("tr").length - 1;

          if (UpdateSpecificRow(pageNo, rowIndex, data.data)) {
            showToast(
              "Task marked " +
                (nextRowActivity === "finished" ? "finished!" : "pending!"),
              "success",
            );
            reloadView();
          }
        } else {
          showToast(data.message || "Task couldn't be updated", "error");
        }
        $("#loader").toggleClass("hidden");
      })
      .catch((err) => {
        console.error("API Call Error : ", err);
      });
  } else {
    // ❌ NO clicked
    console.log("User cancelled");
  }
};

const AllowAction = (row) => {
  if (window?.xoid?.user?.value?.value?.isAdmin === 1) return true;

  const assignedTo = $(row).data("creator");
  return assignedTo === window?.xoid?.user?.value?.value?.id ? true : false;
};
