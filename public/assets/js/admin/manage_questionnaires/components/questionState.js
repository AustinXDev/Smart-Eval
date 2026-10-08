let currentSetId = null;
let allQuestions = null;

export function setCurrentSetId(id) {
  currentSetId = id;
}

export function getCurrentSetId() {
  return currentSetId;
}

export function setAllQuestions(questions) {
  allQuestions = questions;
}

export function getAllQuestions() {
  return allQuestions;
}
