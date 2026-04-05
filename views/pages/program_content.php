<div class="p-4 space-y-6 bg-gray-100">

  <!-- HEADER -->
  <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">

    <div>
      <h1 class="text-2xl font-semibold text-gray-800">Manage Programs</h1>
      <p class="text-sm text-gray-500">Organize and manage academic programs</p>
    </div>

    <button
      class="addProgram flex items-center justify-center gap-2 bg-gradient-to-r from-purple-600 to-indigo-600 text-white 
             px-5 py-2.5 rounded-xl shadow-md
             hover:scale-[1.03] active:scale-[0.97] transition-all duration-200">

      <i class="fa-solid fa-plus"></i>
      Add New Program
    </button>

  </div>


  <!-- TABLE -->
  <div class="bg-white rounded-2xl shadow-lg overflow-hidden p-4">

    <div class="flex w-full flex-col gap-4 md:flex-row md:gap-8  mb-4  mt-2">

      <select id="departmentFilter"
        class="border border-gray-300 rounded-lg px-3 py-2 text-sm 
               focus:ring-2 focus:ring-purple-500 outline-none max-w-[200px]">
        <option value="All">Show All</option>
        <option>College</option>
        <option>SHS</option>
      </select>

    </div>

    <div class="overflow-x-auto">
      <table id="programTable" class="w-full text-sm">

        <!-- HEADER -->
        <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide whitespace-nowrap">
          <tr>
            <th class="px-5 py-3 text-left">Program Code</th>
            <th class="px-5 py-3 text-left">Program Name</th>
            <th class="px-5 py-3 text-left">Department</th>
            <th class="px-5 py-3 text-left">Status</th>
            <th class="px-5 py-3 text-center">Actions</th>
          </tr>
        </thead>

        <!-- BODY -->
        <tbody class="divide-y">
          <!-- JS fill this line -->
        </tbody>

      </table>
    </div>

  </div>

</div>