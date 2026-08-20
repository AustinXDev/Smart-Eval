<!-- CSV Upload Summary Modal -->
<div
  id="csvSummaryModal"
  class="fixed inset-0 z-[50] hidden flex items-center justify-center bg-slate-950/50 px-3 py-5 backdrop-blur-sm sm:px-5"
  role="dialog"
  aria-modal="true"
  aria-labelledby="csvSummaryTitle"
>

  <div
    class="flex max-h-[90vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
  >

    <!-- ================================================= -->
    <!-- HEADER -->
    <!-- ================================================= -->
    <div
      class="relative border-b border-slate-200 bg-white px-6 py-5"
    >

      <!-- Accent -->
      <div
        class="absolute left-0 top-0 h-full w-1 bg-violet-600"
      ></div>

      <div class="flex items-center justify-between gap-4">

        <div class="flex items-center gap-4">

          <!-- Icon -->
          <div
            class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
          >
            <i class="fas fa-chart-pie text-lg"></i>
          </div>

          <!-- Title -->
          <div>

            <h2
              id="csvSummaryTitle"
              class="text-lg font-semibold tracking-tight text-slate-900"
            >
              CSV Upload Summary
            </h2>

            <p class="mt-0.5 text-sm text-slate-500">
              Import results and processing details
            </p>

          </div>

        </div>


        <!-- Close -->
        <button
          type="button"
          data-close-modal="csvSummaryModal"
          aria-label="Close summary"
          class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg text-slate-400 transition hover:bg-slate-100 hover:text-slate-700 focus:outline-none focus:ring-4 focus:ring-slate-200"
        >
          <i class="fas fa-times text-sm"></i>
        </button>

      </div>

    </div>


    <!-- ================================================= -->
    <!-- CONTENT -->
    <!-- ================================================= -->
    <div class="flex-1 overflow-y-auto">

      <div class="space-y-5 p-5 sm:p-6">


        <!-- Summary Content -->
        <div
          id="csvSummaryContent"
          class="text-left text-sm text-slate-800"
        >

          <!--
            JS will populate:

            Total
            Successful
            Failed
            Errors
          -->

        </div>


        <!-- Optional Status Message -->
        <div
          class="flex items-start gap-3 rounded-xl border border-blue-100 bg-blue-50/60 px-4 py-3"
        >

          <div
            class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
          >
            <i class="fas fa-info text-xs"></i>
          </div>

          <div>

            <p class="text-xs font-semibold text-blue-800">
              Import completed
            </p>

            <p class="mt-0.5 text-xs leading-5 text-blue-700">
              Review the results above to verify that all student records
              were processed correctly.
            </p>

          </div>

        </div>

      </div>

    </div>


    <!-- ================================================= -->
    <!-- FOOTER -->
    <!-- ================================================= -->
    <div
      class="border-t border-slate-200 bg-white px-5 py-4 sm:px-6"
    >

      <div class="flex justify-end">

        <button
          type="button"
          data-close-modal="csvSummaryModal"
          class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/20 active:scale-[0.98]"
        >

          <i class="fas fa-check text-xs"></i>

          Done

        </button>

      </div>

    </div>

  </div>

</div>