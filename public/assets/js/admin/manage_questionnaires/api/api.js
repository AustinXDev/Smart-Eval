import { get, post } from "../../../services/http.js";

const BASE = "/Smart-Eval/app/Controllers/questionnaires";

function toJson(res) {
  return res.json();
}

// ── Question sets ────────────────────────────────────
export async function fetchAllSets() {
  try {
    return await get("questionnaires/get_sets.php");
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function addQuestionSet(formData) {
  try {
    return await post("questionnaires/add_set.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function editQuestionSet(formData) {
  try {
    return await post("questionnaires/update_set.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function deleteQuestionSet(setId) {
  try {
    return await post("questionnaires/delete_set.php", { set_id: setId });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

// ── Questions ────────────────────────────────────────
export async function fetchAllQuestions(setId) {
  try {
    return await get(`questionnaires/get_question.php?id=${setId}`);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function addQuestion(formData) {
  try {
    return await post("questionnaires/add_question.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function editQuestion(formData) {
  try {
    return await post("questionnaires/update_question.php", formData);
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function deleteQuestion(questionId) {
  try {
    return await post("questionnaires/delete_question.php", {
      question_id: questionId,
    });
  } catch (error) {
    return {
      error: "error",
      message: error.message,
    };
  }
  return fetch(`${BASE}/delete_question.php`, {
    method: "POST",
    body: new URLSearchParams({ question_id: questionId }),
  }).then(toJson);
}

export async function activateQuestion(questionId) {
  try {
    return await post("questionnaires/activate_question.php", {
      question_id: questionId,
    });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
