<aside class="overflow-y-auto fixed top-2 left-0 hidden w-90 min-h-screen bg-gray-900 text-white  flex-col lg:flex">
    <!-- Nav -->
    <nav class="flex-1 px-4 py-4 space-y-4 overflow-y-auto mt-15">
        <?php require __DIR__ . '/sidebar_nav.php';?>
    </nav>
    <!-- Logout -->
    <div class="px-4 py-4 border-t border-gray-700">
        <a href="<?= $logout ?>"
           class="flex items-center gap-3 px-4 py-2 rounded-lg text-red-400
                  hover:bg-red-600 hover:text-white transition-colors duration-200">
            🚪 Logout
        </a>
    </div>

</aside>

<!-- Alpine.js for dropdown -->
<script src="//cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>