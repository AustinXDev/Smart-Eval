export function renderUpdateForm(periodId, period) {
  console.log(period);
  setValue("update_period_id", periodId);
  setValue("update_academic_year", period.academic_year);

  checkRadioGroup(
    "update_semester",
    (radio) => radio.value === period.semester,
  );
  checkRadioGroup(
    "update_department",
    (radio) =>
      radio.value.toLowerCase() === (period.target_dept ?? "").toLowerCase(),
  );

  setQuerySelectorValue('input[name="update_start_date"]', period.start_date);
  setQuerySelectorValue('input[name="update_end_date"]', period.end_date);

  const select = document.getElementById("updateQuestionSetSelect");
  if (select) select.value = String(period.set_id);
}

function setValue(id, value) {
  const el = document.getElementById(id);
  if (el) el.value = value;
}

function setQuerySelectorValue(selector, value) {
  const el = document.querySelector(selector);
  if (el) el.value = value;
}

function checkRadioGroup(name, matches) {
  document.querySelectorAll(`input[name="${name}"]`).forEach((radio) => {
    radio.checked = matches(radio);
  });
}
