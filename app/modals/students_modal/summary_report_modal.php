<div id="csvSummaryModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50">
  <div class="bg-white rounded-lg p-6 w-full max-w-lg">
    <h2 class="text-lg font-semibold mb-4">CSV Upload Summary</h2>
    <div id="csvSummaryContent" class="text-left text-sm text-gray-800">
      <!-- JS will populate total, success, failed, and errors -->
    </div>
    <div class="flex justify-end mt-4">
      <button data-close-modal="csvSummaryModal"
              class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 transition">
        Close
      </button>
    </div>
  </div>
</div>