<aside class="overflow-y-auto fixed top-16 bottom-0 left-0 hidden w-90 text-white flex-col lg:flex" style="background: linear-gradient(180deg, #1E0F4E 0%, #2D1B69 60%, #1A0A3D 100%);">
    <!-- Nav -->
    <nav class="flex-1 px-4 py-4 space-y-4 overflow-y-auto">
        <?php require __DIR__ . '/sidebar_nav.php';?>
    </nav>
    <!-- Logout -->
    <div class="px-4 py-4 border-t border-gray-700">
        <a href="<?= $logout ?>"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-400
                  hover:bg-red-600 hover:text-white transition-colors duration-200">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </div>

</aside>

<!-- Alpine.js for dropdown -->
<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>