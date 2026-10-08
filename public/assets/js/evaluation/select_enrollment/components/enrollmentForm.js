import { selectEnrollmentType } from "../api/evaluationApi.js";

export function initEnrollmentForm(form, selector) {
  if (!form || !selector) return;

  form.addEventListener("submit", (event) => {
    submitEnrollment(event, selector);
  });
}

async function submitEnrollment(event, selector) {
  event.preventDefault();

  const enrollmentType = selector.getSelectedType();
  if (!enrollmentType) {
    alert("Please select an enrollment type");
    return;
  }

  selector.setLoading(true);

  try {
    const data = await selectEnrollmentType(enrollmentType);

    if (data.status !== "success") {
      StatusModal.show(
        "Failed",
        data?.message || "Unable to select enrollment type.",
        "error",
      );

      selector.resetLoading();
      return;
    }

    window.location.href = data.redirect;
  } catch (error) {
    StatusModal.show("Failed", error.message, "error");
    selector.resetLoading();
  }
}
