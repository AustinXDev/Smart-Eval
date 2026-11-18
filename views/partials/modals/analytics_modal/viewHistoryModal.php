<!-- Evaluation history (report_analytics/modules/binding.js, shared/table-config.js) -->
<div id="viewHistoryModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="viewHistoryTitle">
  <div class="se-modal__panel se-modal__panel--xl">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-clock-rotate-left"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="viewHistoryTitle" class="se-modal__title">Evaluation History</h2>
        <p class="se-modal__subtitle">Browse past evaluation periods and scores.</p>
      </div>
      <button id="closeModal" type="button" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <div class="se-modal__body">
      <div class="overflow-x-auto rounded-xl border border-slate-200">
        <table id="tbl-history" class="w-full whitespace-nowrap" style="width:100%">
          <thead>
            <tr>
              <th>Academic Year</th>
              <th>Semester</th>
              <th>Mean Score</th>
              <th class="text-center">Action</th>
            </tr>
          </thead>
          <tbody id="historyBody">
            <!-- Populated via JS -->
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>
