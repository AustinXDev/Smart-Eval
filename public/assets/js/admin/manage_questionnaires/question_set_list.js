import { fetchAllSets } from "./questions_api.js";

document.addEventListener('DOMContentLoaded', () => {
  loadQuestionSetList();
})

export async function loadQuestionSetList(){
  
  const res = await fetchAllSets();

  if (res.status !== 'success') {
    console.error('Failed to load question sets');
    return;
  }

  const sets = res.data;
  const questionsContainer = document.querySelector('.cards-container');

  questionsContainer.innerHTML = '';

  sets.forEach((s) => {
    let  badge = ``;
    
    if(s.active_evaluation_using_set){
      badge = `In Used`;
    } else {
      badge = `Used ${s.total_periods_using_set} time${s.total_periods_using_set !== 1 ? 's' : ''}`;
    }
 
     questionsContainer.insertAdjacentHTML('beforeend', `
      <div class="bg-white border rounded-lg shadow-sm hover:shadow-md transition p-4 space-y-3">
        
        <div class="flex items-center gap-3">
          <div class="bg-yellow-100 p-2 rounded text-lg">📋</div>
          <div>
            <h2 class="font-semibold text-gray-800">${s.set_name}</h2>
            <p class="text-sm text-gray-500">
              Total Questions: <span class="font-medium">${s.total_questions}</span>
            </p>
          </div>
        </div>

        <hr>

        <div class="flex justify-between items-center text-sm">
          <p class="text-gray-500">Date Created: ${s.created_at}</p>
          <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs whitespace-nowrap">
            ${badge}
          </span>
        </div>

        <div class="flex gap-2 pt-2">
          <button data-set-id="${s.set_id}" class="bg-purple-700 text-white px-3 py-1 rounded text-sm hover:bg-purple-800">
            Manage
          </button>
          <button data-set-id="${s.set_id}" class="border px-3 py-1 rounded text-sm hover:bg-gray-100">
            Edit
          </button>
          <button data-set-id="${s.set_id}" class="border text-red-600 px-3 py-1 rounded text-sm hover:bg-red-50">
            Delete
          </button>
        </div>

      </div>
    `);
  });
}