<!-- Edit Teacher Modal -->
<div id="editTeacherModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-2xl mx-2 sm:mx-0">
    
    <!-- Header -->
    <div class="bg-[#1A1A2E] -mx-6 px-6 py-4 flex items-center justify-between">
      <h2 class="text-lg font-semibold text-white m-0 ml-5">Teacher Information</h2>
    </div>

    

  <form id="editTeacherForm" enctype="multipart/form-data" method="POST">

    <!-- Profile -->
    <div class="flex items-center justify-center flex-col gap-5 mt-5 shadow-[rgba(0,0,0,0.1)_0px_1px_3px_0px,rgba(0,0,0,0.06)_0px_1px_2px_0px] rounded-md mx-7 py-5 bg-[#7C3AED]/5">
      <div>
        <img src="../../public/uploads/teachers/default_teacher.png" alt="photo" class="max-w-[120px] h-auto object-cover">
      </div>
      <div class="flex justify-center items-center flex-col">
        <h1 id="header-name" class="text-lg md:text-2xl lg:text-3xl mb-3"></h1>
        <label class="bg-[#16213E] text-white px-4 py-2 rounded-md cursor-pointer hover:bg-[#1A1A2E]">
            <i class="fas fa-plus"></i> Upload File
            <input type="file" class="hidden" id="fileInput" name="photo">
        </label>
      </div>
    </div>

    <!-- Information -->
    <div class="mt-7 p-3 shadow-[rgba(60,64,67,0.3)_0px_1px_2px_0px_rgba(60,64,67,0.15)_0px_1px_3px_1px] rounded-md mx-8 flex flex-col gap-4">
      <div class="flex items-center gap-2">
        <i class="fas fa-file-alt text-[#7C3AED] text-lg mr-1"></i>
        <p class="font-semibold">Teacher Detail</p>
    </div>

    <input type="hidden" name="teacher_id" id="teacherId" class="border-1">

    <div id="input-wrapper" class="w-full flex flex-col items-centers lg:flex-row gap-4">
        <div id="input-container" class="w-full">
          <label for="teachersId" class="text-gray-600 text-md">Employee ID <span class="text-red-500">*</span></label>
          <input type="text" name="employee_id" id="employee_Id" placeholder="Enter Teacher ID" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div id="input-container" class="w-full">
          <label for="teachersName" class="text-gray-600">Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="full_name" id="teacherName" placeholder="Enter Teacher Name" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>  
    </div>

    <div id="input-wrapper" class="w-full flex flex-col items-center lg:flex-row gap-4">
        <div id="input-container" class="w-full">
          <label for="teachersEmail" class="text-gray-600">Email Address <span class="text-red-500">*</span></label>
          <input type="email" name="email" id="teacherEmail" placeholder=" Enter Email Address" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div id="input-container" class="w-full">
          <label for="teachersDept" class="text-gray-600">Department <span class="text-red-500">*</span></label>
          <Select name="department" id="teacherDept" placeholder="Teacher Name" class="w-full border border-gray-300 rounded px-3 py-2" required>
            <option value="<?php echo $department; ?>" selected><?php echo strtoupper($department); ?></option>
          </Select>
        </div>  
    </div>
      
    <div class="flex justify-end gap-3 mt-4">
        <button type="button" data-close-modal="editTeacherModal" class="px-4 py-2 bg-gray-300 rounded cursor-pointer" ">
          Cancel
        </button>
        <button type="submit" class="save px-4 py-2 bg-blue-600 text-white rounded cursor-pointer" id="save">Save Changes</button>
    </div>
    </div>

  </form>
</div>