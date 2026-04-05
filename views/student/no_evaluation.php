<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>No Evaluation</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php'?>

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../../public/assets/css/custom.css">

  <!-- Icons cdn --->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body class="h-screen flex justify-center items-center">
  <!-- EMPTY STATE CONTAINER -->
<div class="h-full flex items-center justify-center px-4">

  <!-- CARD -->
  <div class="max-w-md w-full bg-white rounded-2xl md:shadow-lg p-8 text-center md:border md:border-gray-100">

    <!-- ICON -->
    <div class="flex justify-center mb-6">
      <div class="w-20 h-20 flex items-center justify-center rounded-full bg-purple-100">
        <svg class="w-10 h-10 text-purple-600" fill="none" stroke="currentColor" stroke-width="1.8"
          viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M9 5h6m-7 4h8m-8 4h5m-7 7h10a2 2 0 002-2V7a2 2 0 00-2-2h-3.5a1.5 1.5 0 01-3 0H7a2 2 0 00-2 2v11a2 2 0 002 2z" />
        </svg>
      </div>
    </div>

    <!-- TITLE -->
    <h2 class="text-2xl font-semibold text-gray-800 mb-2">
      No Evaluation Available
    </h2>

    <!-- MESSAGE -->
    <p class="text-gray-500 text-sm leading-relaxed mb-6">
      There are currently no evaluation forms available.
      Please wait for the evaluation period to open.
    </p>

    <!-- LOGIN BUTTON -->
    <a href="../auth/login.view.php"
      class="inline-flex items-center justify-center gap-2 bg-purple-600 hover:bg-purple-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition duration-200 shadow-md hover:shadow-lg">

      <!-- ICON -->
      <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M17 16l4-4m0 0l-4-4m4 4H7" />
      </svg>

      Back to Login
    </a>

  </div>

</div>
</body>
</html>