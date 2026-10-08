import { get, post } from "../../../services/http.js";

/*
|--------------------------------------------------------------------------
| DATABASE INTEGRATION
|--------------------------------------------------------------------------
| All communication with PHP/MySQL is handled through Fetch API.
|
| JavaScript NEVER directly accesses MySQL.
|--------------------------------------------------------------------------
*/

export async function fetchEvaluation() {
  try {
    return await get("evaluation/evaluation_status.php");
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}

export async function saveEvaluation({
  teacherId,
  evalId,
  answers,
  comment,
  update = false,
}) {
  /*
   * PHP ENDPOINT:
   * POST /api/evaluation/save.php
   *
   * Request:
   *
   * {
   *   "teacher_id": 123,
   *   "answers": {
   *      "1": 5,
   *      "2": 4,
   *      "3": 3
   *   }
   * }
   *
   * The PHP endpoint should validate the authenticated student,
   * teacher, evaluation period, and answers before inserting/
   * updating evaluation_answers in MySQL.
   *
   * Expected response:
   *
   * {
   *   "success": true,
   *   "message": "Evaluation saved successfully."
   * }
   */
  try {
    return await post("evaluation/submit.php", {
      teacher_id: teacherId,
      eval_id: evalId,
      answers: answers,
      comment: comment,
      update,
    });
  } catch (error) {
    return {
      status: "error",
      message: error.message,
    };
  }
}
