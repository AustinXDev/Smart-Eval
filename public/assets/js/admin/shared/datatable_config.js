export function createDataTable(selector, options = {}, searchParam) {
  if ($.fn.DataTable.isDataTable(selector)) {
    return $(selector).DataTable(); // return existing instance
  }

  const defaultConfig = {
    dom:
      "<'flex flex-row justify-between items-center mb-4 gap-4'<'flex items-center gap-2'f><'flex items-center gap-2'l>>" +
      "rt" +
      "<'flex flex-col sm:flex-row justify-between items-center mt-4 gap-2 info-pagination min-w-full'<'text-gray-600'i><'pagination'p>>",
    paging: true,
    searching: true,
    info: true,
    lengthChange: false,
    pageLength: 5,
    ordering: false,
    language: { lengthMenu: "_MENU_" },
    initComplete: function () {
      styleControls(searchParam);
      $(".dataTables_info").addClass("text-gray-600 text-sm sm:text-base mt-2");
    },
  };

  const table = $(selector).DataTable({
    ...defaultConfig,
    ...options,
  });

  table.on("draw.dt", function () {
    stylePagination();
  });

  return table;
}

function stylePagination() {
  $(".pagination .paginate_button").css({
    padding: "0.25rem 0.75rem",
    border: "1px solid #d1d5db",
    "border-radius": "5px",
    "font-size": "0.875rem",
    color: "#374151",
    margin: "0.25rem",
    cursor: "pointer",
    display: "inline-block",
    background: "white",
  });

  $(".pagination .paginate_button.current").css({
    background: "#16213E",
    color: "#ffffff",
    "border-color": "#16213E",
  });

  $(".pagination .paginate_button.disabled").css({
    color: "#9ca3af",
    cursor: "not-allowed",
    "border-color": "#e5e7eb",
  });

  $(".pagination .paginate_button")
    .not(".current")
    .not(".disabled")
    .off("mouseenter mouseleave")
    .on("mouseenter", function () {
      $(this).css("background", "#f3f4f6");
    })
    .on("mouseleave", function () {
      $(this).css("background", "white");
    });
}

function styleControls(params) {
  stylePagination();

  $(".dataTables_filter input")
    .attr("placeholder", `${params}...`)
    .addClass(
      "border border-gray-200 ml-2 rounded-md px-3 py-1 w-full sm:w-80 focus:ring-1 focus:ring-blue-300 focus:outline-none text-sm",
    );

  $(".dataTables_length select").addClass(
    "border w-40 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer",
  );

  $(".dataTables_filter label").addClass("mb-0").css("display", "flex");
}
