<!-- Add Teacher Modal -->
<div id="addTeacherModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50 hidden">
  <div class="bg-white rounded-lg shadow-lg overflow-hidden w-full max-w-2xl mx-2 sm:mx-0">
    
    <!-- Header -->
    <div class="bg-[#1A1A2E] -mx-6 px-6 py-4">
      <h2 class="text-lg font-semibold text-white m-0 ml-5">Add Teacher</h2>
      <p class="text-white text-xs m-0 ml-5">Register a new faculty member</p>
    </div>

    <!-- Form -->
    <form id="addTeacherForm" class="flex flex-col gap-3 p-6" enctype="multipart/form-data" method="POST">

      <div class="flex flex-col gap-4">
        <div class="flex items-center gap-2">
          <i class="fas fa-camera text-[#7C3AED]"></i>
          <h1>Upload Teacher Photo</h1>
        </div>
        <div class="p-6 border-1 border-gray-200 rounded-md flex flex-col justify-center items-center gap-2">
          <p>Choose a file.</p>
          <label class="bg-[#16213E] text-white px-4 py-2 rounded-md cursor-pointer hover:bg-[#1A1A2E]">
           <i class="fas fa-plus"></i> Upload File
            <input type="file" class="hidden" id="fileInput" name="photo">
          </label>
          <span id="fileName" class="text-xs text-gray-400" style="font-family: roboto, 'sans-serif';">JPG or PNG - Max 5MB</span>
        </div>
      </div>

      <div id="input-wrapper" class="w-full flex flex-col items-centers lg:flex-row gap-4">
        <div id="input-container" class="w-full">
          <label for="teachersId" class="text-gray-600">Employee ID <span class="text-red-500">*</span></label>
          <input type="text" name="employee_id" id="teachersId" placeholder="Enter Teacher ID" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div id="input-container" class="w-full">
          <label for="teachersName" class="text-gray-600">Full Name <span class="text-red-500">*</span></label>
          <input type="text" name="full_name" id="teachersName" placeholder="Enter Teacher Name" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>  
      </div>

      <div id="input-wrapper" class="w-full flex flex-col items-center lg:flex-row gap-4">
        <div id="input-container" class="w-full">
          <label for="teachersEmail" class="text-gray-600">Email Address <span class="text-red-500">*</span></label>
          <input type="email" name="email" id="teachersEmail" placeholder=" Enter Email Address" class="w-full border border-gray-300 rounded px-3 py-2" required>
        </div>
        <div id="input-container" class="w-full">
          <label for="teachersDept" class="text-gray-600">Department <span class="text-red-500">*</span></label>
          <Select name="department" id="teachersDept" placeholder="Teacher Name" class="w-full border border-gray-300 rounded px-3 py-2" required>
            <option value="<?php echo $department; ?>"><?php echo strtoupper($department); ?></option>
          </Select>
        </div>  
      </div>
      
      <div class="flex justify-end gap-3 mt-4">
        <button type="button" data-close-modal="addTeacherModal" class="px-4 py-2 bg-gray-300 rounded cursor-pointer" ">
          Cancel
        </button>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded cursor-pointer">Add Teacher</button>
      </div>
    </form>

  </div>
</div>