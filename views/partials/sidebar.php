<aside class="bg-[#F8F9FA] overflow-y-auto absolute top-0 bottom-0 left-0 hidden z-1 w-70 text-white flex-col lg:flex">

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

    <!-- Nav -->
    <nav class="flex-1 px-4 py-4 space-y-4 overflow-y-auto">
        <?php require __DIR__ . '/sidebar_nav.php';?>
    </nav>
    <!-- Logout -->
    <div class="px-4 py-4">
        <a href="<?= $logout ?>"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-400
                  hover:bg-red-600 hover:text-white transition-colors duration-200">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

</aside>

<!-- Alpine.js for dropdown -->
<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>