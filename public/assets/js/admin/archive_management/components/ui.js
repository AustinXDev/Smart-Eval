export const byId = (id) => document.getElementById(id);

export function announce(message, error = false) {
  const notice = byId("archive-notice");
  notice.textContent = message;
  notice.classList.remove("hidden", "text-red-700", "text-slate-700");
  notice.classList.add(error ? "text-red-700" : "text-slate-700");
}
export function filterValues(prefix, names) {
  return Object.fromEntries(
    names.map((name) => [name, byId(prefix + "-" + name).value]),
  );
}
export function setOptions(id, values, label, value) {
  const select = byId(id);
  const selected = select.value;
  while (select.options.length > 1) select.remove(1);
  for (const item of values) select.add(new Option(label(item), value(item)));
  if (Array.from(select.options).some((option) => option.value === selected))
    select.value = selected;
}
