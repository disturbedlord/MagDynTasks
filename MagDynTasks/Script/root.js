// Anything which should be accessible to whole app exists here
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
        transform transition-all duration-300 opacity-0 translate-y-2 z-50
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
