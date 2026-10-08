<!-- Upload CSV Modal -->
<div id="uploadCSVModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">

  <div class="bg-gray-100 rounded-xl shadow-2xl w-full max-w-2xl mx-2 overflow-hidden">

    <!-- Header -->
    <div class="bg-[#0F172A] px-6 py-4 border-b-2 border-purple-500">
      <h2 class="text-white text-lg font-semibold">Upload CSV File</h2>
      <p class="text-gray-400 text-xs">Import multiple students using a CSV file</p>
    </div>

    <!-- Form -->
    <form id="uploadCsvForm" class="p-6 flex flex-col gap-6" method="POST" enctype="multipart/form-data">

      <!-- Upload Area -->
      <div class="bg-white rounded-lg shadow p-6">

        <div id="dropZone"
          class="w-full border-2 border-dashed border-gray-300 rounded-lg h-48 flex flex-col justify-center items-center text-center gap-3 cursor-pointer hover:border-purple-500 transition">

          <i class="fas fa-cloud-upload-alt text-4xl text-purple-400"></i>

          <p class="text-gray-700 font-medium">Drag & Drop your CSV file here</p>
          <p class="text-gray-400 text-sm">or click to browse</p>

          <label for="csvInput"
            class="mt-2 bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition cursor-pointer">
            Choose File
            <input type="file" id="csvInput" name="csv" accept=".csv" class="hidden" required>
          </label>

          <!-- File Name Preview -->
          <p id="fileName" class="text-sm text-green-600 mt-2 hidden"></p>
        </div>

      </div>

      <!-- Instructions -->
      <div class="bg-white rounded-lg shadow p-5 text-center">

        <h3 class="text-gray-800 font-semibold mb-2">CSV Upload Instructions</h3>

        <p class="text-gray-600 mb-3">
          Allowed format:
          <span class="text-purple-600 font-semibold">.CSV only</span>
        </p>

        <!-- Buttons -->
        <div class="flex flex-wrap justify-center gap-3 mb-3">

          <a href="../../app/Controllers/students/download_csv.php"
            class="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 transition flex items-center gap-2">
            <i class="fas fa-download"></i>
            Template
          </a>

          <a href="../../app/Controllers/students/download_legend.php?department=<?php echo urlencode($department); ?>"
            class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition flex items-center gap-2">
            <i class="fas fa-book"></i>
            Program Guide
          </a>

        </div>

        <p class="text-gray-600 text-sm">
          Ensure your CSV follows the required format. Use the template and match program IDs correctly.
        </p>

        <p class="text-xs text-gray-500 mt-2">
          Example: <span class="font-mono">1 = BSIT</span>, <span class="font-mono">2 = BSOA</span>
        </p>

      </div>

      <!-- Actions -->
      <div class="flex justify-end gap-3 border-t pt-4">

        <button type="button" data-close-modal="uploadCSVModal"
          class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-md transition">
          Cancel
        </button>

        <button type="submit"
          class="px-5 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md shadow flex items-center gap-2 transition">
          <i class="fas fa-upload"></i>
          Upload CSV
        </button>

      </div>

    </form>
  </div>
</div>