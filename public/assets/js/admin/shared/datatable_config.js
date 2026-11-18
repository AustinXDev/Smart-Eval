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

    // The table scrolls sideways inside .dt-scroll, so the footer
    // (info + pagination) stays in view on small screens.
    // Layout and colours live in custom.css (.se-datatable).
    dom:
      "<'dt-scroll't>" +
      "<'dt-footer'<'dt-info'i><'dt-pagination'p>>",

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

  };

  // ==============================================================
  // INITIALIZE DATATABLE
  // ==============================================================

  const table = $(selector).DataTable({
    ...defaultConfig,
    ...options,
  });

  const wrapper = table.table().container();
  wrapper.classList.add("se-datatable");
  moveScrollRegion(wrapper);

  return table;
}

// ================================================================
// SCROLL REGION
// ================================================================

// The views wrap each table in a keyboard-scrollable region
// (role="region" tabindex="0" aria-label="..."). The table now scrolls
// inside .dt-scroll instead, so move those attributes there.
function moveScrollRegion(wrapper) {
  const scroller = wrapper.querySelector(":scope > .dt-scroll");
  const region = wrapper.parentElement?.closest('[role="region"]');
  if (!scroller || !region) return;

  scroller.setAttribute("role", "region");
  scroller.setAttribute("tabindex", "0");
  if (region.hasAttribute("aria-label")) {
    scroller.setAttribute("aria-label", region.getAttribute("aria-label"));
  }

  region.removeAttribute("role");
  region.removeAttribute("tabindex");
  region.removeAttribute("aria-label");
}
