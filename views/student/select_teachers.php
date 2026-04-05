<?php require '../../app/middleware/require_auth.php'; ?>
<?php require_once __DIR__ . '/../../app/middleware/student_enrollment_guard.php'; ?>

<?php 
$student = $_SESSION['student'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Select Teachers</title>

<?php include_once __DIR__ . '/../../public/assets/includes/head.php'; ?>
</head>

<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-5xl mx-auto bg-white p-6 rounded-xl shadow">

    <!-- Header -->
    <div class="text-center mb-6">
        <span class="bg-amber-500 text-white px-3 py-1 rounded-full text-sm">
            Irregular Student
        </span>
        <h1 class="text-2xl font-bold mt-3">Select Your Teachers</h1>
        <p class="text-gray-500 text-sm">Choose teachers to evaluate</p>
    </div>

    <!-- Teachers Grid -->
    <div id="teachersGrid" class="grid grid-cols-2 md:grid-cols-4 gap-4"></div>

    <!-- Summary -->
    <div id="summarySection" class="hidden mt-6 bg-green-50 border p-4 rounded">
        <p><strong id="selectedCount">0</strong> selected</p>
        <p id="selectedList" class="text-sm text-gray-600"></p>
    </div>

    <!-- Buttons -->
    <div class="mt-6 text-center">
        <button id="proceedBtn" disabled
            class="bg-blue-500 text-white px-6 py-2 rounded disabled:bg-gray-300">
            Proceed
        </button>
    </div>

</div>

<script src="/Smart-Eval/public/assets/js/evaluation/select_teachers.js"></script>
</body>
</html>