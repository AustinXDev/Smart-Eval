<div id="uploadLoading" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-100 hidden">

  <div class="bg-white/90 backdrop-blur-lg rounded-xl shadow-2xl px-8 py-6 flex flex-col items-center gap-4">

    <!-- Gradient Spinner -->
    <div class="relative w-14 h-14">

      <!-- Glow -->
      <div class="absolute inset-0 rounded-full bg-purple-500 blur-xl opacity-30"></div>

      <!-- Spinner -->
      <div class="w-full h-full border-4 border-purple-500 border-t-transparent rounded-full animate-spin"></div>
    </div>

    <!-- Text -->
    <div class="text-center">
      <p class="text-gray-800 font-semibold">Uploading CSV</p>

      <!-- Animated dots -->
      <p class="text-gray-500 text-sm flex justify-center gap-1 mt-1">
        <span class="dot">.</span>
        <span class="dot">.</span>
        <span class="dot">.</span>
      </p>
    </div>

  </div>
</div>