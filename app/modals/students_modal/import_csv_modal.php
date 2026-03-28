<!-- Add Teacher Modal -->
<div id="uploadCSVModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-2xl mx-2 sm:mx-0">
    
    <!-- Header -->
    <div class="bg-[#1A1A2E] -mx-6 px-6 py-4">
      <h2 class="text-lg font-semibold text-white m-0 ml-5">Upload CSV File</h2>
    </div>

    <!-- Form -->
    <form id="uploadCsvForm" class="flex flex-col gap-3 p-6 pb-0" method="POST">

      <div>
        <div class="w-full border border-gray-200 flex justify-center items-center h-50 rounded-sm border-dashed">
          <div class="w-full flex justify-center text-center">
            <div class="flex flex-col justify-center items-center gap-4 text-center">
              <i class="fas fa-file-upload text-4xl text-[#A494B7]"></i>
              <h1>Choose your CSV File</h1>

              <label for="csvInput" class="bg-blue-500 text-white px-4 py-2 rounded-lg cursor-pointer hover:bg-blue-600 transition">
                Choose CSV File
                <input type="file" class="hidden" id="csvInput" name="csv" accept=".csv" required>
              </label>
            </div>
          </div>
        </div>
      </div>

      <div class="w-full max-w-2xl mx-auto text-center mt-8 p-4 border border-gray-200 rounded-lg bg-gray-50">
        <!-- Heading -->
        <h2 class="text-lg font-semibold mb-2 text-gray-800">CSV Upload Instructions</h2>

        <!-- Info about allowed format -->
        <p class="mb-3 text-gray-700">
          Allowed format: <span class="text-[#7C3AED] font-semibold">CSV</span>
        </p>

        <!-- Download links -->
        <div class="mb-3 flex flex-wrap justify-center gap-4">
          <a href="../../app/handlers/students/download_csv.php"
            class="bg-[#7C3AED] text-white px-4 py-2 rounded-lg hover:bg-[#5B21B6] transition">
            Download CSV Template
          </a>
          <a href="../../app/handlers/students/download_legend.php?department=<?php echo urlencode($department); ?>"
            class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg hover:bg-gray-300 transition">
            Download Program Reference
          </a>
        </div>

        <!-- Additional instructions -->
        <p class="text-gray-700 mt-3">
          Make sure your CSV file follows the required format. Use the template above and refer to the program guide to ensure correct IDs.
        </p>

        <!-- Optional note -->
        <p class="text-sm text-gray-500 mt-2">
          Example: Use <span class="font-mono">1</span> for BSIT, <span class="font-mono">2</span> for BSE, etc.
        </p>
      </div>
      
      <div class="flex justify-center gap-3 mt-4 w-full border-t-1 border-gray-300 py-4">
        <button type="submit" class="w-30 px-4 py-2 bg-blue-600 text-white rounded cursor-pointer">Upload</button>
        <button type="button" data-close-modal="uploadCSVModal" class="px-4 py-2 bg-gray-300 rounded cursor-pointer" ">
          Cancel
        </button>
      </div>
    </form>

  </div>
</div>