<?php 
require_once __DIR__ . '/../../app/middleware/require_admin_auth.php';
require_once __DIR__ . '/../../app/config/nav.php'; 
require_once __DIR__ . '/../../app/helpers/session.php';

if (isAdminLoggedIn()) {
    $admin     = getAdmin();
    $role      = strtolower(str_replace(' ', '_', $admin['role']));
    $name      = $admin['username'];
    $logout    = '/Smart-Eval/app/auth/logout.admin.php';
} else {
    $role      = 'student';
    $studentID = getStudent();
    $logout    = '/Smart-Eval/app/auth/logout.student.php';
}

$nav        = $navigation[$role] ?? [];
$currentUrl = $_SERVER['REQUEST_URI'];

$department = $_GET['dept'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"/>
   
  <!-- jQuery (required for DataTables) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

  <!-- DataTables JS -->
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.tailwind.min.css">

  <!-- Modal JS -->
  <script src="../../public/assets/js/modal/modal.js"></script>
</head>
<body>
  <!-- header -->
  <?php require __DIR__ . '/../partials/header.php'; ?>
  
  <!-- Sidebar -->
  <?php require __DIR__ . '/../partials/sidebar.php'; ?>

  <!-- Modal Content -->
  <?php require_once __DIR__ . '/../../app/modals/add_teacher_modal.php'; ?>
  <?php require_once __DIR__ . '/../../app/modals/confirmation_modal.php'; ?>

  <main class="pt-22 lg:ml-90 p-6  min-h-screen">
    <?php require __DIR__ . '/../pages/teachers_content.php'?>
  </main>

<script>
$(document).ready(function() {
var table = $('#teachersTable').DataTable({
    dom:
       "<'flex flex-col sm:flex-row sm:justify-between items-center  mb-4 gap-4'<'flex items-center gap-2'f><'flex items-center gap-2'l>>" +
      "rt" +
      "<'flex flex-col sm:flex-row sm:justify-between items-center mt-4 gap-2 info-pagination min-w-full'<'text-gray-600 'i><'pagination'p>>",
    paging: true,
    searching: true,
    info: true,
    lengthChange: false,
    pageLength: 5,
    ordering: false,
    columnDefs: [
        { orderable: false, targets: 5 }
    ],
    language: {
        lengthMenu: "_MENU_"
    },
    initComplete: function() {
        styleControls();
    }
});

table.on('draw.dt', function() {
    stylePagination();
});

$('#statusFilter').on('change', function() {
    var status = $(this).val();
    if (status) {
        table.column(4).search('^' + status + '$', true, false).draw();
    } else {
        table.column(4).search('').draw();
    }
});

$('#searchBox').on('keyup', function() {
    table.search(this.value).draw();
});

function stylePagination() {
  $('.pagination .paginate_button').css({
        'padding': '0.25rem 0.75rem',
        'border': '1px solid #d1d5db',
        'border-radius': '5px',
        'font-size': '0.875rem',
        'color': '#374151',
        'margin': '0.25rem',
        'cursor': 'pointer',
        'display': 'inline-block',
        'background': 'white'
  });

  $('.pagination .paginate_button.current').css({
      'background': '#16213E',
      'color': '#ffffff',
      'border-color': '#16213E'
  });

  $('.pagination .paginate_button.disabled').css({
      'color': '#9ca3af',
      'cursor': 'not-allowed',
      'border-color': '#e5e7eb'
  });

  $('.pagination .paginate_button')
    .not('.current')
    .not('.disabled')
    .off('mouseenter mouseleave')
    .on('mouseenter', function() {
        $(this).css('background', '#f3f4f6');
    })
    .on('mouseleave', function() {
        $(this).css('background', 'white');
    });
}

function styleControls() {
  stylePagination(); 

  var $searchInput = $('.dataTables_filter input');
  $searchInput
    .attr('placeholder', 'Search teachers...')
    .addClass('border ml-2 rounded-md px-3 py-1 w-full sm:w-64 focus:ring-1 focus:ring-blue-300 focus:outline-none text-sm');

  var $lengthSelect = $('.dataTables_length select');
  $lengthSelect
    .addClass('border w-40 rounded-md px-2 py-1 text-sm focus:ring-1 focus:ring-blue-300 focus:outline-none bg-white cursor-pointer');

  $('.dataTables_filter label').addClass('mb-0');

  $('.dataTables_filter label').css('display', 'flex');
}
});
</script>
</body>
</html>