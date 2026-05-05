const inputField = document.querySelector("#searchBar");

// The callback function
function handleInput() {
  const searchQuery = $("#searchBar").val();
  $("#loader").toggleClass("hidden");

  loadNextPage("down");
}

// Attach the listener
const sendBtn = $("#sendBtn");
$(document).on("click", "#sendBtn", function () {
  handleInput();
});
