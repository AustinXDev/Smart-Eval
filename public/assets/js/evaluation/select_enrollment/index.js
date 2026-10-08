import { createEnrollmentSelector } from "./components/enrollmentSelector.js";
import { initEnrollmentForm } from "./components/enrollmentForm.js";

function getPageElements() {
  return {
    form: document.getElementById("enrollmentForm"),
    program: document.getElementById("program"),
    cards: document.querySelectorAll(".option-card"),
    selectedTypeInput: document.getElementById("selectedTypeInput"),
    proceedButton: document.getElementById("proceedBtn"),
    buttonText: document.getElementById("btnText"),
    buttonArrow: document.getElementById("btnArrow"),
    buttonLoader: document.getElementById("btnLoader"),
  };
}

function isCompletePage(elements) {
  return Object.values(elements).every((element) => element !== null);
}

async function initEnrollmentPage() {
  const elements = getPageElements();
  if (!isCompletePage(elements)) return;

  const selector = createEnrollmentSelector({
    cards: elements.cards,
    selectedTypeInput: elements.selectedTypeInput,
    proceedButton: elements.proceedButton,
    buttonText: elements.buttonText,
    buttonArrow: elements.buttonArrow,
    buttonLoader: elements.buttonLoader,
  });

  initEnrollmentForm(elements.form, selector);
}

document.addEventListener("DOMContentLoaded", initEnrollmentPage);
