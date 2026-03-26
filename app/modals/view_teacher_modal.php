<!-- View Teacher Modal -->
<div id="viewDetails" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-2xl mx-2 sm:mx-0">
    
    <!-- Header -->
    <div class="bg-[#1A1A2E] -mx-6 px-6 py-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-white m-0 ml-5">Teacher Information</h2>
      <button data-close-modal="viewDetails">
        <i class="fas fa-times text-xl mr-5 text-white"></i>
      </button>
    </div>

    <!-- Profile -->
    <div class="flex items-center gap-5 mt-5 pl-8">
      <div>
        <img src="../../public/uploads/teachers/default_teacher.png" alt="photo" class="w-20 h-20">
      </div>
      <div>
        <h1 id="name" class="text-xl md:text-2xl lg:text-3xl">Juan Dela Cruz</h1>
        <p class="text-gray-600 text-sm md:text-md">Employee ID: <span id="id"> S10005</span></p>
        <p id="department" class="text-gray-600 text-sm md:text-md"></p>
      </div>
    </div>

  <!-- Information -->
  <div class="mt-7 p-3 shadow-[rgba(60,64,67,0.3)_0px_1px_2px_0px_rgba(60,64,67,0.15)_0px_1px_3px_1px] border-1 border-gray-400 rounded-md mx-8 flex flex-col gap-4">
    <div class="flex items-center gap-2">
      <i class="fas fa-file-alt text-[#7C3AED] text-lg mr-1"></i>
      <p class="font-semibold">Teacher Detail</p>
    </div>
    <div class="flex gap-3 items-center bg-[#5B21B6]/90 max-w-[300px] text-white px-5 py-4 rounded-md">
      <i class="fas fa-envelope text-gray-500 text-lg text-white"></i>
      <div>
        <p>Email</p>
        <p id="email">example@gmail.com</p>
      </div>
    </div>
  </div>

    <!-- Handles Container --->
  <div class="mt-7 p-3">
    <div class="flex px-5 gap-2 items-center ">
      <i class="fas fa-graduation-cap text-[#5B21B6] text-lg"></i>
      <h1 class="font-semibold">Handle Level and Program</h1>
    </div>
    <!-- Form -->
     <form id="addHandleForm" class="flex flex-col gap-3 p-2 md:p-6" method="POST">
      <input type="hidden" name="teacher_id" id="handle_teacher_id">
      <input type="hidden" name="department" id="department" value="<?php echo htmlspecialchars($department); ?>">
      <div class="flex gap-3">
        <select class="w-full border-1 border-gray-500 text-sm md:p-2 rounded-sm" name="level" id="" required>
          <?php if ($department === 'college') {?>
            <option value="" disabled>Grade Level</option>
            <option value="1">1st Year</option>
            <option value="2">2nd Year</option>
            <option value="3">3rd Year</option>
            <option value="4">4th Year</option>
          <?php } else {?>
            <option value="" disabled>Grade Level</option>
            <option value="11">Grade 11</option>
            <option value="12">Grade 12</option>
          <?php } ?>
          
        </select>

        <select class="w-full border-1 border-gray-500 md:p-2 text-sm rounded-sm" name="program" id="">
          <option value="" disabled>Program</option>
          <option value="1">BSIT</option>
          <option value="2">BSOA</option>
          <option value="3">ABM</option>
        </select>
        <button class="w-full border-1 border-gray-500 p-[5px] text-sm md:p-2 rounded-sm bg-[#5B21B6] text-white cursor-pointer" type="submit" id="addHandleBtn">
          <span>
            <i class="fas fa-plus"></i>
          </span>
          Add Handle
        </button>
      </div>
    </form> 

    <!-- Handle List--->
    <div class="px-5 mt-2 min-h-[200px] overflow-y-auto">
      <table class="table-auto border border-gray-400 rounded-md min-w-full text-left" id="handleTable">
        <colgroup>
          <col class="lg:w-1/5">
          <col class="lg:w-1/5">
          <col class="lg:w-3/5">
        </colgroup>

        <thead class="text-left bg-gray-300">
          <tr>
            <td colspan="3" class="p-3">Assigned Handles</td>
          </tr>
        </thead>
        <tbody class="">
          <!-- JS fecthing fill this space --->
        </tbody>
      </table>
    </div>

    </div>
  </div>
</div>