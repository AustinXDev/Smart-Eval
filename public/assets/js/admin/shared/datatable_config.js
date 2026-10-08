// ================================================================
// DATATABLE CONFIGURATION
// ================================================================

export function createDataTable(
  selector,
  options = {},
  searchParam = "Search...",
) {
  // ==============================================================
  // RETURN EXISTING INSTANCE
  // ==============================================================

  if ($.fn.DataTable.isDataTable(selector)) {
    return $(selector).DataTable();
  }

  // ==============================================================
  // DEFAULT CONFIGURATION
  // ==============================================================

  const defaultConfig = {
    // --------------------------------------------------------------
    // IMPORTANT:
    // No "f" here.
    //
    // "f" = DataTables built-in search box.
    //
    // Your pages use their own custom search fields.
    // --------------------------------------------------------------

    dom:
      "rt" +
      "<'mt-5 flex flex-col gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:items-center sm:justify-between'" +
      "<'dataTables-info'i>" +
      "<'dataTables-pagination'p>" +
      ">",

    // ==============================================================
    // TABLE SETTINGS
    // ==============================================================

    paging: true,

    searching: true,

    info: true,

    lengthChange: false,

    pageLength: 5,

    ordering: false,

    autoWidth: false,

    // ==============================================================
    // LANGUAGE
    // ==============================================================

    language: {
      search: "",

      searchPlaceholder: searchParam,

      // ------------------------------------------------------------
      // EMPTY TABLE
      // ------------------------------------------------------------

      emptyTable: `
        <div class="flex flex-col items-center justify-center py-12">

          <div
            class="
              flex h-12 w-12
              items-center justify-center
              rounded-xl
              bg-slate-50
              text-slate-400
            "
          >
            <i class="fa-solid fa-folder-open text-lg"></i>
          </div>

          <p class="mt-3 text-sm font-semibold text-slate-700">
            No records found
          </p>

          <p class="mt-1 text-xs text-slate-400">
            There are currently no records available.
          </p>

        </div>
      `,

      // ------------------------------------------------------------
      // NO SEARCH RESULTS
      // ------------------------------------------------------------

      zeroRecords: `
        <div class="flex flex-col items-center justify-center py-12">

          <div
            class="
              flex h-12 w-12
              items-center justify-center
              rounded-xl
              bg-slate-50
              text-slate-400
            "
          >
            <i class="fa-solid fa-magnifying-glass text-lg"></i>
          </div>

          <p class="mt-3 text-sm font-semibold text-slate-700">
            No matching records
          </p>

          <p class="mt-1 text-xs text-slate-400">
            Try adjusting your search or filters.
          </p>

        </div>
      `,

      // ------------------------------------------------------------
      // TABLE INFORMATION
      // ------------------------------------------------------------

      info: "Showing _START_ to _END_ of _TOTAL_ records",

      infoEmpty: "Showing 0 to 0 of 0 records",

      // ------------------------------------------------------------
      // PAGINATION
      // ------------------------------------------------------------

      paginate: {
        previous: `
          <span class="inline-flex items-center gap-1.5">

            <i class="fa-solid fa-chevron-left text-[9px]"></i>

            <span>
              Previous
            </span>

          </span>
        `,

        next: `
          <span class="inline-flex items-center gap-1.5">

            <span>
              Next
            </span>

            <i class="fa-solid fa-chevron-right text-[9px]"></i>

          </span>
        `,
      },
    },

    // ==============================================================
    // INITIALIZATION
    // ==============================================================

    initComplete: function () {
      stylePagination();
    },
  };

  // ==============================================================
  // INITIALIZE DATATABLE
  // ==============================================================

  const table = $(selector).DataTable({
    ...defaultConfig,
    ...options,
  });

  // ==============================================================
  // RE-STYLE AFTER EVERY DRAW
  // ==============================================================

  table.on("draw.dt", function () {
    stylePagination();
  });

  return table;
}

// ================================================================
// STYLE PAGINATION
// ================================================================

function stylePagination() {
  $(".dataTables_info")
    .removeClass("text-gray-600 text-sm sm:text-base mt-2")
    .addClass("text-xs font-medium text-slate-400 mx-4");

  $(".dataTables_paginate")
    .removeAttr("style")

    .addClass(["mx-4", "my-2", "flex", "items-center"]);

  $(".dataTables_paginate .paginate_button")
    .removeAttr("style")

    .addClass(
      [
        "inline-flex",
        "min-h-9",
        "min-w-9",
        "items-center",
        "justify-center",
        "rounded-lg",
        "border",
        "border-slate-200",
        "bg-white",
        "px-3",
        "mx-1",
        "text-xs",
        "font-semibold",
        "text-slate-600",
        "transition-all",
        "duration-150",
      ].join(" "),
    );

  // ==============================================================
  // CURRENT PAGE
  // ==============================================================

  $(".dataTables_paginate .paginate_button.current")
    .removeAttr("style")

    .addClass(
      [
        "!border-violet-600",
        "!bg-violet-600",
        "!text-white",
        "shadow-sm",
        "shadow-violet-600/20",
      ].join(" "),
    );

  // ==============================================================
  // HOVER
  // ==============================================================

  $(".dataTables_paginate .paginate_button")
    .not(".current")
    .not(".disabled")

    .addClass(
      [
        "hover:border-violet-200",
        "hover:bg-violet-50",
        "hover:text-violet-600",
      ].join(" "),
    );

  // ==============================================================
  // DISABLED
  // ==============================================================

  $(".dataTables_paginate .paginate_button.disabled")
    .removeAttr("style")

    .addClass(
      [
        "!cursor-not-allowed",
        "!border-slate-100",
        "!bg-slate-50",
        "!text-slate-300",
      ].join(" "),
    );
}
