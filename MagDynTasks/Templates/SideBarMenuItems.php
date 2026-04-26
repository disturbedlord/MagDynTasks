<!-- Header -->
<div class="border rounded-md overflow-hidden">
  <!-- Header (1. Tasks) -->
  <button onclick="toggleSection('TaskSubItem')" class="w-full flex justify-between items-center p-2 bg-white">
    <div class="text-left">
      <h2 class="text-sm text-gray-600 ">Tasks</h2>
    </div>

    <i id="arrow-TaskSubItem" class="bi bi-chevron-down text-xs transition-transform duration-200"></i>
  </button>

  <!-- SUB ITEM (1.1 View Task) -->
  <div id="TaskSubItem" class="hidden pb-1 ml-2">
    <!-- For All (n - 1) elements -->
    <!-- <div class="flex border-l-2 ml-4  flex-row items-center py-2 text-sm text-gray-600  cursor-pointer">

                    <div class="w-4.5 h-0.5 bg-[#e5e7eb]"></div>
                    <span class="pl-2"> View Task
                    </span>
                </div> -->
    <!-- For Last Element -->
    <div id="ViewTaskSubItem" href="/controller/a.php"
      class="flex relative ml-4 flex-row items-center py-2 text-sm text-gray-600 cursor-pointer">
      <div class="w-5 h-5 absolute -top-0 border-l-2 border-b-2"></div>
      <div class="w-4 h-px"></div>

      <a class="pl-3 " href="HomePage.php"> View Task </a>
    </div>
  </div>
</div>

<script>

  // Store All Mapping Here
  // Menu Item -> URL
  const MenuMapping = {

    "HomePage": "ViewTaskSubItem"

  }

  function toggleSection(id) {
    const section = document.getElementById(id);
    const arrow = document.getElementById(`arrow-${id}`);
    $(`#${id}`).find("span").removeClass("underline");

    const url = window.location.href; // "http://localhost/.../HomePage.php"
    const path = new URL(url).pathname; // "/magDynTasks/MagDynTasks/Controller/HomePage.php"
    const controller = path.split('/').pop().replace('.php', '');

    $(`#${id} #${MenuMapping[controller]}`).find("a").addClass("underline font-semibold ");

    section.classList.toggle('hidden');
    arrow.classList.toggle('rotate-90');
  }
</script>