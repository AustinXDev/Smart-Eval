<!-- CREATE QUESTION SET MODAL -->
<div id="createQuestionSetModal"
  class="fixed inset-0 bg-slate-950/50 backdrop-blur-sm flex items-center justify-center z-50 p-4 hidden">

  <!-- MODAL CONTAINER -->
  <div
    class="relative w-full max-w-lg overflow-hidden rounded-2xl
           border border-slate-200 bg-white shadow-2xl"
  >

    <!-- HEADER -->
    <div class="border-b border-slate-200 bg-white px-6 py-5">

      <div class="flex items-start justify-between gap-4">

        <!-- Title -->
        <div class="flex items-center gap-3">

          <div
            class="flex h-10 w-10 shrink-0 items-center justify-center
                   rounded-xl bg-violet-50 text-violet-600"
          >
            <i class="fas fa-layer-group text-sm"></i>
          </div>

          <div>
            <h2 class="text-base font-semibold tracking-tight text-slate-900">
              Create New Question Set
            </h2>

            <p class="mt-0.5 text-xs text-slate-400">
              Create a new set for your evaluation questions
            </p>
          </div>

        </div>

        <!-- Close -->
        <button
          type="button"
          data-close-modal="createQuestionSetModal"
          class="flex h-8 w-8 items-center justify-center
                 rounded-lg text-slate-400 transition
                 hover:bg-slate-100 hover:text-slate-700
                 focus:outline-none focus:ring-4 focus:ring-violet-500/10"
          aria-label="Close modal"
        >
          <i class="fas fa-times text-sm"></i>
        </button>

      </div>

    </div>


    <!-- BODY -->
    <div class="px-6 py-6">

      <form id="createQuestionSetForm" class="space-y-5">

        <!-- SET NAME -->
        <div class="space-y-2">

          <label
            for="create_set_name"
            class="block text-xs font-semibold uppercase
                   tracking-wide text-slate-500"
          >
            Set Name
            <span class="text-red-500">*</span>
          </label>

          <div class="relative">

            <!-- Input Icon -->
            <div
              class="pointer-events-none absolute inset-y-0 left-0
                     flex items-center pl-3.5 text-slate-400"
            >
              <i class="fas fa-file-alt text-sm"></i>
            </div>

            <input
              type="text"
              name="set_name"
              id="create_set_name"
              placeholder="Enter question set name"
              autocomplete="off"
              required
              class="h-11 w-full rounded-lg border border-slate-200
                     bg-white pl-10 pr-4 text-sm text-slate-700
                     placeholder:text-slate-400
                     outline-none transition
                     hover:border-slate-300
                     focus:border-violet-400
                     focus:ring-4 focus:ring-violet-500/10"
            >

          </div>

          <p class="text-xs text-slate-400">
            Give your question set a clear and recognizable name.
          </p>

        </div>


        <!-- INFORMATION NOTE -->
        <div
          class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3.5"
        >

          <div class="flex items-start gap-3">

            <div
              class="mt-0.5 flex h-7 w-7 shrink-0 items-center justify-center
                     rounded-lg bg-amber-100 text-amber-600"
            >
              <i class="fas fa-info text-xs"></i>
            </div>

            <div>

              <p class="text-xs font-semibold text-amber-800">
                Before you continue
              </p>

              <p class="mt-1 text-xs leading-5 text-amber-700">
                Once created, you can add specific questions and categories
                to this question set.
              </p>

            </div>

          </div>

        </div>


        <!-- DIVIDER -->
        <div class="border-t border-slate-200"></div>


        <!-- ACTIONS -->
        <div
          class="flex flex-col-reverse gap-2.5
                 sm:flex-row sm:justify-end"
        >

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="createQuestionSetModal"
            class="inline-flex h-10 items-center justify-center
                   rounded-lg border border-slate-200
                   bg-white px-4 text-sm font-medium text-slate-600
                   shadow-sm transition
                   hover:bg-slate-50 hover:text-slate-800
                   focus:outline-none focus:ring-4
                   focus:ring-slate-200
                   active:scale-[0.98]"
          >
            Cancel
          </button>


          <!-- Create -->
          <button
            type="submit"
            class="inline-flex h-10 items-center justify-center gap-2
                   rounded-lg bg-violet-600 px-5
                   text-sm font-semibold text-white
                   shadow-sm shadow-violet-600/20
                   transition-all duration-200
                   hover:bg-violet-700
                   hover:shadow-md hover:shadow-violet-600/20
                   focus:outline-none
                   focus:ring-4 focus:ring-violet-500/20
                   active:scale-[0.98]"
          >
            <i class="fas fa-plus text-xs"></i>
            <span>Create Set</span>
          </button>

        </div>

      </form>

    </div>

  </div>
</div>