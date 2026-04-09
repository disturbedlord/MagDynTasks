let sessionChecker;

function startSessionCheck() {
  sessionChecker = setInterval(() => {
    fetch("../Session_Management/check_session.php")
      .then((res) => res.json())
      .then((data) => {
        if (data.expired) {
          clearInterval(sessionChecker);

          showToast("Session expired. Redirecting...", "info");

          setTimeout(() => {
            window.location.href = "login.php?reason=timeout";
          }, 1500);
        }
      })
      .catch(() => {
        // optional: handle network error
      });
  }, 30000); // every 30 sec
}

startSessionCheck();

// Update User Last Activity
let lastPing = 0;
const PING_INTERVAL = 60 * 1000; // Ping every 1 min and update lastactivity

function sendActivityPing() {
  const now = Date.now();

  if (now - lastPing < PING_INTERVAL) return; // throttle

  lastPing = now;

  fetch("../Session_Management/update_activity.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ active: true }),
  });
}

// Attach events
["mousemove", "click", "keydown", "scroll"].forEach((event) => {
  console.log(event);
  window.addEventListener("mousemove", sendActivityPing);
  window.addEventListener("touchstart", sendActivityPing);
  window.addEventListener("touchmove", sendActivityPing);

  const scrollEl = document.getElementById("scroll-container");

  scrollEl.addEventListener("scroll", sendActivityPing);
});
