<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>No Evaluation Available</title>

  <?php include_once __DIR__ . '../../../public/assets/includes/head.php' ?>

  <!-- Tailwind CSS -->
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- Font Awesome -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
  >
</head>

<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4 relative overflow-hidden">
 
  <!-- Subtle decorative blobs -->
  <div aria-hidden="true" class="pointer-events-none select-none absolute -top-24 -left-24 w-96 h-96 rounded-full bg-purple-100 opacity-40 blur-3xl"></div>
  <div aria-hidden="true" class="pointer-events-none select-none absolute -bottom-24 -right-24 w-80 h-80 rounded-full bg-purple-200 opacity-30 blur-3xl"></div>
 
  <!-- Card -->
  <main class="relative z-10 w-full max-w-md">
    <div class="bg-white border border-gray-100 rounded-2xl shadow-sm px-8 py-12 text-center">
 
      <!-- Icon -->
      <div class="mx-auto mb-6 w-16 h-16 flex items-center justify-center rounded-2xl bg-purple-50" aria-hidden="true">
        <svg class="w-8 h-8 text-purple-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
          <rect x="9" y="3" width="6" height="4" rx="1"/>
          <path d="M9 12h6M9 16h4"/>
        </svg>
      </div>
 
      <!-- Title -->
      <h1 class="text-xl font-semibold text-gray-900 mb-3 tracking-tight">
        No Evaluation Available
      </h1>
 
      <!-- Description -->
      <p class="text-sm text-gray-500 leading-relaxed mb-8 max-w-xs mx-auto">
        There are currently no evaluation forms available. Please wait for the evaluation period to open.
      </p>
 
      <!-- Divider -->
      <div class="w-10 h-px bg-gray-100 mx-auto mb-8"></div>
 
      <!-- Back to Login -->
      <a
        href="login"
        class="inline-flex items-center justify-center gap-2 w-full px-5 py-2.5
               bg-purple-600 hover:bg-purple-700 active:bg-purple-800
               text-white text-sm font-medium
               rounded-xl shadow-sm
               transition-all duration-200
               focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2"
        aria-label="Back to login page"
      >
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M19 12H5M12 5l-7 7 7 7"/>
        </svg>
        Back to Login
      </a>
 
      <!-- Footer note -->
      <p class="mt-6 text-xs text-gray-400">
        Contact your administrator if you believe this is an error.
      </p>
 
    </div>
  </main>
 
</body>
</html>

