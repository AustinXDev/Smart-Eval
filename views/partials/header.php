<?php 
require_once __DIR__ . '/../../app/helpers/session.php';
$admin = getAdmin();
?>

<header class="fixed top-0 left-0 right-0 z-50 bg-[#16213E] h-16 flex items-center px-5 lg:px-10 shadow-md">
  
  <!-- Hamburger (mobile) & Logo -->
  <div class="flex items-center gap-3 flex-1">
    
    <!-- Hamburger Button (mobile only) -->
    <button id="hamburger-btn" class="lg:hidden text-white focus:outline-none" aria-label="Toggle menu">
      <svg id="hamburger-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
      <svg id="close-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>

    <!-- Logo & Title -->
    <img src="../../public/assets/images/aite-logo.png" alt="AITE Logo" width="38">
    <h1 class="text-white text-sm md:text-base font-semibold tracking-wide">Smart-Eval System</h1>
  </div>

  <!-- Role & Avatar -->
  <div class="flex items-center gap-3">
    <div class="hidden lg:flex flex-col items-end">
      <p class="text-white text-sm font-medium"><?= htmlspecialchars($admin['username']) ?></p>
      <p class="text-blue-400 text-xs"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $admin['role']))) ?></p>
    </div>
    <img src="../../public/assets/icons/profile.png" alt="Profile" width="35" class="rounded-full">
  </div>

</header>

<!-- Mobile Drawer Overlay -->
<div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

<!-- Mobile Sidebar Drawer -->
<div id="mobile-drawer" class="fixed top-0 left-0 h-full w-75 bg-gray-900 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out lg:hidden">
  
  <!-- Drawer Header -->
  <div class="flex items-center gap-3 px-6 py-5 border-b border-gray-700">
    <img src="../../public/assets/images/aite-logo.png" alt="AITE Logo" width="35">
    <span class="text-white font-semibold text-sm">Smart-Eval System</span>
  </div>
  
  <div id="mobile-nav" class="flex-1 px-4 py-4 space-y-4 overflow-y-auto">
    <?php require __DIR__ . '/sidebar_nav.php';?>

    <div class="px-4 py-4 border-t border-gray-700">
        <a href="<?= $logout ?>"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-400
                  hover:bg-red-600 hover:text-white transition-colors duration-200">
            🚪 Logout
        </a>
    </div>
  </div>
</div>


<script>
  const btn = document.getElementById('hamburger-btn');
  const drawer = document.getElementById('mobile-drawer');
  const overlay = document.getElementById('mobile-overlay');
  const hamburger = document.getElementById('hamburger-icon');
  const closeIcon = document.getElementById('close-icon');

  function openDrawer() {
    drawer.classList.remove('-translate-x-full');
    overlay.classList.remove('hidden');
    hamburger.classList.add('hidden');
    closeIcon.classList.remove('hidden');
  }

  function closeDrawer() {
    drawer.classList.add('-translate-x-full');
    overlay.classList.add('hidden');
    hamburger.classList.remove('hidden');
    closeIcon.classList.add('hidden');
  }

  btn.addEventListener('click', () => {
    drawer.classList.contains('-translate-x-full') ? openDrawer() : closeDrawer();
  });

  // Close when clicking overlay
  overlay.addEventListener('click', closeDrawer);
</script>