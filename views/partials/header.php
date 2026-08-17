<?php 
require_once __DIR__ . '/../../app/helpers/session.php';
$admin = getAdmin();
?>

<header class="absolute top-0 left-0  right-0 z-50 h-16 flex items-center px-5 lg:px-10 lg:left-70 bg-white" 
style="
  box-shadow: 
  -12px 0px 32px -8px rgba(108, 92, 231, 0.08), 
  -4px 0px 12px -4px rgba(0, 0, 0, 0.04);
">
  
    <!-- Hamburger (mobile) & Logo -->
    <div class="flex items-center gap-3 flex-1">
    
    <!-- Hamburger Button (mobile only) -->
    <button id="hamburger-btn" class="lg:hidden text-[#2D3748] focus:outline-none" aria-label="Toggle menu">
      <svg id="hamburger-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
      </svg>
      <svg id="close-icon" xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
      </svg>
    </button>

    <div class="flex items-center gap-2 lg:hidden">

      <!-- Logo & Title -->
      <img src="<?= BASE_URL ?>assets/images/aite-logo.png" alt="AITE Logo" width="38">
      <h1 class="text-[#2D3748] drop-shadow-sm text-sm md:text-base font-bold tracking-wider">Smart-Eval</h1>

    </div>

    <div class="hidden lg:flex flex-col leading-tight border-l-2 border-[#6C5CE7] pl-2">
      <h2 class="text-[15px] font-semibold text-[#2D3748]">
        <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
      </h2>
      <p class="text-xs text-gray-400">
        <?= date('l, F j, Y') ?>
      </p>
    </div>

  </div>

  <!-- Role & Avatar -->
  <div class="flex items-center gap-3">
    <div class="hidden lg:flex flex-col items-end">
      <p class="text-[#2D3748] text-sm font-semibold leading-tight">
        <?= htmlspecialchars($admin['username'] ?? $student['full_name'] ?? 'Unknown') ?>
      </p>
      <p class="text-[#2D3748]/60 text-xs font-medium leading-tight">
        <?= htmlspecialchars(ucwords($role ?? '')) ?>
      </p>
    </div>

    <!-- Avatar -->
    <div class="relative">
      <div class="w-10 h-10 rounded-full bg-[#2D1B69] flex items-center justify-center
                  ring-2 ring-white shadow-sm hover:ring-[#6010ff]/30 transition-all duration-200 cursor-pointer">
        <i class="fas fa-user text-white text-sm"></i>
      </div>
      <!-- Online status dot -->
      <span class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-white rounded-full"></span>
    </div>
  </div>

</header>

<!-- Mobile Drawer Overlay -->
<div id="mobile-overlay" class="fixed inset-0 bg-black/50 z-40 hidden lg:hidden"></div>

<!-- Mobile Sidebar Drawer -->
<div id="mobile-drawer" class="fixed top-0 left-0 h-full w-75 z-50 transform -translate-x-full transition-transform duration-300 ease-in-out bg-[#F8F9FA] lg:hidden">
  
  <!-- Drawer Header matches main header gradient -->
  <header class="h-18 z-50 bg-white/90 backdrop-blur-md
               border-b border-gray-100 shadow-[0_1px_3px_rgba(0,0,0,0.04)]">
      <div class="h-full flex items-center px-6 lg:px-8">

          <!-- Branding -->
          <div class="flex items-center gap-3">
          <img src="<?= BASE_URL ?>/assets/images/aite-logo.png" alt="AITE Logo" class="w-10 h-10 object-contain">

          <div class="flex flex-col leading-tight border-l border-gray-200 pl-3">
              <span class="text-[17px] font-semibold text-[#2D1B69] tracking-tight" style="font-family: 'Poppins', sans-serif;">
              Smart-Eval
              </span>
              <span class="text-[11px] font-medium text-gray-400 tracking-wide">
              Teacher Evaluation System
              </span>
          </div>
          </div>

      </div>
  </header>
  
  <div id="mobile-nav" class="flex-1 px-4 py-4 space-y-4 overflow-y-auto">

    <?php require __DIR__ . '/sidebar_nav.php';?>

    <div class="py-4">
        <a href="<?= $logout ?>"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-400
                  hover:bg-red-600 hover:text-white transition-colors duration-200">
            <i class="fas fa-sign-out-alt"></i> Logout
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

  overlay.addEventListener('click', closeDrawer);
</script>