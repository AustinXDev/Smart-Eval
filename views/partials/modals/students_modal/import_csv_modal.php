<!-- Upload CSV Modal -->
<div
  id="uploadCSVModal"
  class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-950/50 px-3 py-5 backdrop-blur-sm sm:px-5"
  role="dialog"
  aria-modal="true"
  aria-labelledby="uploadCSVTitle"
>

  <div
    class="flex max-h-[95vh] w-full max-w-2xl flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-2xl shadow-slate-900/20"
  >

    <!-- ================================================= -->
    <!-- HEADER -->
    <!-- ================================================= -->
    <div
      class="relative border-b border-slate-200 bg-white px-6 py-5 sm:px-7"
    >

      <!-- Accent -->
      <div
        class="absolute left-0 top-0 h-full w-1 bg-violet-600"
      ></div>

      <div class="flex items-center gap-4">

        <!-- Icon -->
        <div
          class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-violet-50 text-violet-600"
        >
          <i class="fas fa-file-upload text-lg"></i>
        </div>

        <!-- Title -->
        <div>

          <h2
            id="uploadCSVTitle"
            class="text-lg font-semibold tracking-tight text-slate-900"
          >
            Upload CSV File
          </h2>

          <p class="mt-0.5 text-sm text-slate-500">
            Import multiple students using a CSV file
          </p>

        </div>

      </div>

    </div>


    <!-- ================================================= -->
    <!-- FORM -->
    <!-- ================================================= -->
    <form
      id="uploadCsvForm"
      class="flex flex-1 flex-col overflow-y-auto"
      method="POST"
      enctype="multipart/form-data"
    >

      <div class="space-y-5 p-5 sm:p-7">


        <!-- ================================================= -->
        <!-- UPLOAD AREA -->
        <!-- ================================================= -->
        <section
          class="rounded-xl border border-slate-200 bg-slate-50/60 p-5"
        >

          <div
            id="dropZone"
            class="group flex min-h-[220px] w-full cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-slate-300 bg-white px-5 py-8 text-center transition-all duration-200 hover:border-violet-400 hover:bg-violet-50/30"
          >

            <!-- Upload Icon -->
            <div
              class="mb-4 flex h-16 w-16 items-center justify-center rounded-2xl bg-violet-50 text-violet-500 transition-transform duration-200 group-hover:scale-105"
            >
              <i class="fas fa-cloud-upload-alt text-2xl"></i>
            </div>


            <!-- Main Text -->
            <p class="text-sm font-semibold text-slate-800 sm:text-base">
              Drag & Drop your CSV file here
            </p>

            <p class="mt-1 text-xs text-slate-400">
              or click below to browse from your computer
            </p>


            <!-- Choose File -->
            <label
              for="csvInput"
              class="mt-5 inline-flex h-10 cursor-pointer items-center gap-2 rounded-lg bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md focus-within:ring-4 focus-within:ring-violet-500/20 active:scale-[0.98]"
            >

              <i class="fas fa-folder-open text-xs"></i>

              Choose File

              <input
                type="file"
                id="csvInput"
                name="csv"
                accept=".csv"
                class="hidden"
                required
              >

            </label>


            <!-- File Name -->
            <p
              id="fileName"
              class="mt-4 hidden max-w-full truncate rounded-lg bg-emerald-50 px-3 py-2 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20"
            ></p>

          </div>


          <!-- File Type -->
          <div class="mt-3 flex items-center justify-center gap-2 text-xs text-slate-400">

            <i class="fas fa-info-circle"></i>

            <span>
              Only <strong class="font-semibold text-slate-600">.CSV</strong>
              files are accepted
            </span>

          </div>

        </section>


        <!-- ================================================= -->
        <!-- CSV INSTRUCTIONS -->
        <!-- ================================================= -->
        <section
          class="rounded-xl border border-slate-200 bg-white"
        >

          <!-- Section Header -->
          <div
            class="flex items-center gap-3 border-b border-slate-100 px-5 py-4"
          >

            <div
              class="flex h-9 w-9 items-center justify-center rounded-lg bg-violet-100 text-violet-600"
            >
              <i class="fas fa-book-open text-sm"></i>
            </div>

            <div>

              <h3 class="text-sm font-semibold text-slate-900">
                CSV Upload Instructions
              </h3>

              <p class="text-xs text-slate-500">
                Make sure your file follows the required format
              </p>

            </div>

          </div>


          <!-- Instructions Content -->
          <div class="p-5">

            <!-- Format -->
            <div
              class="mb-4 flex items-center justify-between rounded-lg bg-slate-50 px-4 py-3"
            >

              <span class="text-sm text-slate-600">
                Allowed file format
              </span>

              <span
                class="inline-flex items-center gap-1.5 rounded-md bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700 ring-1 ring-inset ring-violet-600/20"
              >
                <i class="fas fa-file-csv"></i>
                .CSV only
              </span>

            </div>


            <!-- Resource Buttons -->
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-2">

              <!-- Template -->
              <a
                href="<?php echo $_ENV['APP_API']?>student/download_template.php"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg bg-violet-600 px-4 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-violet-500/20"
              >

                <i class="fas fa-download text-xs"></i>

                Download Template

              </a>


              <!-- Program Guide -->
              <a
                href="<?php echo $_ENV['APP_API'] ?>student/download_legend.php?department=<?php echo urlencode($department); ?>"
                class="inline-flex h-10 items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-4 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-200"
              >

                <i class="fas fa-book text-xs"></i>

                Program Guide

              </a>

            </div>


            <!-- Help Text -->
            <div
              class="mt-4 rounded-lg border border-blue-100 bg-blue-50/60 px-4 py-3"
            >

              <div class="flex gap-3">

                <i class="fas fa-info-circle mt-0.5 text-sm text-blue-500"></i>

                <div>

                  <p class="text-xs font-medium text-blue-800">
                    Before uploading
                  </p>

                  <p class="mt-1 text-xs leading-5 text-blue-700">
                    Ensure your CSV follows the required format. Use the
                    template and match program IDs correctly.
                  </p>

                </div>

              </div>

            </div>


            <!-- Example -->
            <div class="mt-3 flex items-center gap-2 text-xs text-slate-500">

              <i class="fas fa-code text-slate-400"></i>

              <span>
                Example:
              </span>

              <code
                class="rounded bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700"
              >
                1 = BSIT
              </code>

              <code
                class="rounded bg-slate-100 px-2 py-1 font-mono text-xs text-slate-700"
              >
                2 = BSOA
              </code>

            </div>

          </div>

        </section>


      </div>


      <!-- ================================================= -->
      <!-- FOOTER -->
      <!-- ================================================= -->
      <div
        class="border-t border-slate-200 bg-white px-5 py-4 sm:px-7"
      >

        <div
          class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end"
        >

          <!-- Cancel -->
          <button
            type="button"
            data-close-modal="uploadCSVModal"
            class="inline-flex h-11 items-center justify-center rounded-lg border border-slate-200 bg-white px-5 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none focus:ring-4 focus:ring-slate-200 active:scale-[0.98]"
          >
            Cancel
          </button>


          <!-- Upload -->
          <button
            type="submit"
            class="inline-flex h-11 items-center justify-center gap-2 rounded-lg bg-violet-600 px-6 text-sm font-semibold text-white shadow-sm shadow-violet-600/20 transition hover:bg-violet-700 hover:shadow-md hover:shadow-violet-600/20 focus:outline-none focus:ring-4 focus:ring-violet-500/20 active:scale-[0.98]"
          >

            <i class="fas fa-upload text-xs"></i>

            Upload CSV

          </button>

        </div>

      </div>

    </form>

  </div>

</div>