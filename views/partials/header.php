<?php
require_once __DIR__ . '/../../app/helpers/session.php';
$admin = getAdmin();
?>

<!-- =========================================================
     TOP HEADER
========================================================= -->
<header
  class="fixed top-0 left-0 right-0 lg:left-70 z-40
         h-16 bg-white/95 backdrop-blur-md
         border-b border-slate-200
         shadow-[0_1px_8px_rgba(15,23,42,0.04)]"
>
  <div class="h-full flex items-center justify-between px-4 sm:px-6 lg:px-8">

    <!-- LEFT SIDE -->
    <div class="flex items-center gap-3 min-w-0">

      <!-- Mobile Menu Button -->
      <button
        id="hamburger-btn"
        type="button"
        class="lg:hidden flex h-9 w-9 items-center justify-center
               rounded-lg border border-slate-200
               bg-white text-slate-600
               shadow-sm
               transition-all duration-200
               hover:bg-violet-50
               hover:border-violet-200
               hover:text-violet-600
               active:scale-95
               focus:outline-none focus:ring-4 focus:ring-violet-500/10"
        aria-label="Toggle menu"
      >

        <!-- Hamburger -->
        <svg
          id="hamburger-icon"
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M4 6h16M4 12h16M4 18h16"
          />
        </svg>

        <!-- Close -->
        <svg
          id="close-icon"
          xmlns="http://www.w3.org/2000/svg"
          class="w-5 h-5 hidden"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M6 18L18 6M6 6l12 12"
          />
        </svg>

      </button>


      <!-- MOBILE BRANDING -->
      <div class="flex items-center gap-2.5 lg:hidden min-w-0">

        <div
          class="flex h-9 w-9 items-center justify-center
                 rounded-lg bg-violet-50
                 border border-violet-100"
        >
          <img
            src="<?= BASE_URL ?>assets/images/aite-logo.png"
            alt="AITE Logo"
            class="h-7 w-7 object-contain"
          >
        </div>

        <div class="flex flex-col leading-tight min-w-0">
          <h1
            class="text-sm font-bold tracking-tight text-slate-800 truncate"
          >
            Smart-Eval
          </h1>

          <span class="text-[9px] font-medium text-slate-400 tracking-wide">
            TEACHER EVALUATION SYSTEM
          </span>
        </div>

      </div>


      <!-- DESKTOP PAGE INFORMATION -->
      <div class="hidden lg:flex items-center gap-3">

        <!-- Page Icon -->
        <div
          class="flex h-9 w-9 items-center justify-center
                 rounded-lg bg-violet-50
                 text-violet-600"
        >
          <i class="fas fa-layer-group text-sm"></i>
        </div>

        <!-- Page Title -->
        <div class="flex flex-col leading-tight">

          <h2 class="text-sm font-semibold text-slate-800">
            <?= htmlspecialchars($pageTitle ?? 'Dashboard') ?>
          </h2>

          <div class="flex items-center gap-1.5 mt-0.5">

            <i class="far fa-calendar text-[9px] text-slate-400"></i>

            <p class="text-[11px] font-medium text-slate-400">
              <?= date('l, F j, Y') ?>
            </p>

          </div>

        </div>

      </div>

    </div>


    <!-- =====================================================
         USER PROFILE
    ====================================================== -->
    <div class="flex items-center gap-3">

      <!-- User Information -->
      <div class="hidden sm:flex flex-col items-end leading-tight">

        <p class="text-sm font-semibold text-slate-800">
          <?= htmlspecialchars(
              $admin['username'] ?? $student['full_name'] ?? 'Unknown'
          ) ?>
        </p>

        <div class="flex items-center gap-1.5 mt-0.5">

          <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>

          <p class="text-[11px] font-medium text-slate-400">
            <?= htmlspecialchars(ucwords($role ?? 'Administrator')) ?>
          </p>

        </div>

      </div>


      <!-- Avatar -->
      <div
        class="relative flex h-10 w-10 items-center justify-center
               rounded-xl
               bg-gradient-to-br from-violet-600 to-indigo-600
               shadow-sm shadow-violet-500/20
               ring-2 ring-white
               transition-all duration-200
               hover:-translate-y-0.5
               hover:shadow-md hover:shadow-violet-500/20"
      >

        <i class="fas fa-user text-white text-sm"></i>

        <!-- Online Indicator -->
        <span
          class="absolute -right-0.5 -bottom-0.5
                 h-3 w-3 rounded-full
                 bg-emerald-500
                 border-2 border-white"
        ></span>

      </div>

    </div>

  </div>
</header>


<!-- =========================================================
     MOBILE OVERLAY
========================================================= -->
<div
  id="mobile-overlay"
  class="fixed inset-0 z-40 hidden
         bg-slate-950/40 backdrop-blur-[2px]
         lg:hidden"
></div>


<!-- =========================================================
     MOBILE DRAWER
========================================================= -->
<aside
  id="mobile-drawer"
  class="fixed top-0 left-0 bottom-0 z-50
         w-[280px] max-w-[85vw]
         -translate-x-full
         bg-white
         border-r border-slate-200
         shadow-2xl
         transition-transform duration-300 ease-out
         lg:hidden"
>

  <!-- DRAWER HEADER -->
  <div
    class="h-16 px-5
           flex items-center
           border-b border-slate-200
           bg-white"
  >

    <div class="flex items-center gap-3">

      <!-- Logo -->
      <div
        class="flex h-10 w-10 items-center justify-center
               rounded-xl
               bg-violet-50
               border border-violet-100"
      >
        <img
          src="<?= BASE_URL ?>assets/images/aite-logo.png"
          alt="AITE Logo"
          class="h-8 w-8 object-contain"
        >
      </div>


      <!-- Brand -->
      <div class="flex flex-col leading-tight">

        <span
          class="text-[16px] font-bold tracking-tight text-slate-800"
        >
          Smart-Eval
        </span>

        <span
          class="text-[9px] font-medium
                 text-violet-600
                 tracking-wider"
        >
          TEACHER EVALUATION
        </span>

      </div>

    </div>

  </div>


  <!-- USER PROFILE CARD -->
  <div class="px-4 pt-4">

    <div
      class="flex items-center gap-3
             rounded-xl
             border border-slate-200
             bg-slate-50
             px-3 py-3"
    >

      <!-- Avatar -->
      <div
        class="relative flex h-10 w-10 shrink-0
               items-center justify-center
               rounded-lg
               bg-gradient-to-br
               from-violet-600 to-indigo-600
               shadow-sm"
      >

        <i class="fas fa-user text-white text-sm"></i>

        <span
          class="absolute -right-1 -bottom-1
                 h-3 w-3 rounded-full
                 bg-emerald-500
                 border-2 border-slate-50"
        ></span>

      </div>


      <!-- User Info -->
      <div class="min-w-0">

        <p
          class="truncate text-sm
                 font-semibold text-slate-800"
        >
          <?= htmlspecialchars(
              $admin['username'] ?? $student['full_name'] ?? 'Unknown'
          ) ?>
        </p>

        <p class="text-[11px] font-medium text-slate-400">
          <?= htmlspecialchars(ucwords($role ?? 'Administrator')) ?>
        </p>

      </div>

    </div>

  </div>


  <!-- =======================================================
       MOBILE NAVIGATION
  ======================================================== -->
  <div
    id="mobile-nav"
    class="flex flex-col
           h-[calc(100%-132px)]
           px-4 py-4
           overflow-y-auto
           scrollbar-thin
           scrollbar-thumb-slate-200
           scrollbar-track-transparent"
  >

    <!-- Navigation -->
    <div class="flex-1 space-y-1">

      <?php require __DIR__ . '/sidebar_nav.php'; ?>

    </div>


    <!-- Divider -->
    <div class="border-t border-slate-200 my-4"></div>


    <!-- Logout -->
    <div>

      <a
        id="logoutBtn"
        class="group flex items-center gap-3
               rounded-xl
               px-4 py-2.5
               text-sm font-medium
               text-red-500
               transition-all duration-200
               hover:bg-red-50
               hover:text-red-600"
      >

        <span
          class="flex h-8 w-8 items-center justify-center
                 rounded-lg
                 bg-red-50
                 text-red-500
                 transition-colors
                 group-hover:bg-red-100"
        >
          <i class="fas fa-sign-out-alt text-xs"></i>
        </span>

        <span>Logout</span>

      </a>

    </div>

  </div>

</aside>


<!-- =========================================================
     MOBILE DRAWER SCRIPT
========================================================= -->
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

    document.body.classList.add('overflow-hidden');
  }


  function closeDrawer() {

    drawer.classList.add('-translate-x-full');

    overlay.classList.add('hidden');

    hamburger.classList.remove('hidden');

    closeIcon.classList.add('hidden');

    document.body.classList.remove('overflow-hidden');
  }


  if (btn) {

    btn.addEventListener('click', () => {

      if (drawer.classList.contains('-translate-x-full')) {
        openDrawer();
      } else {
        closeDrawer();
      }

    });

  }


  if (overlay) {
    overlay.addEventListener('click', closeDrawer);
  }


  // Close drawer when clicking navigation links
  document.querySelectorAll('#mobile-nav a').forEach(link => {

    link.addEventListener('click', () => {

      closeDrawer();

    });

  });


  // Close drawer with Escape
  document.addEventListener('keydown', (event) => {

    if (event.key === 'Escape') {
      closeDrawer();
    }

  });

</script>