// Client-side validation. Any input with class "vform" parent form and a data-rule attribute is checked.
const rules = {
  name:     v => /^[A-Za-z][A-Za-z .'-]{2,59}$/.test(v) || "Use letters only, 3 to 60 characters.",
  regno:    v => /^[A-Za-z0-9-]{5,20}$/.test(v) || "5 to 20 characters: letters, digits or hyphen.",
  age:      v => (/^\d{1,2}$/.test(v) && v >= 16 && v <= 60) || "Enter a number between 16 and 60.",
  email:    v => /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(v) || "Enter a valid email like name@example.com.",
  phone:    v => /^\d{10,13}$/.test(v) || "Phone must be 10 to 13 digits.",
  password: v => /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/.test(v) ||
                 "Min 8 characters with uppercase, lowercase, a digit and a special character.",
  confirm:  (v, form) => v === form.querySelector('[data-rule="password"]').value || "Passwords do not match.",
  roomno:   v => /^[A-Za-z0-9-]{2,10}$/.test(v) || "2 to 10 characters: letters, digits or hyphen.",
  capacity: v => (/^\d$/.test(v) && v >= 1 && v <= 6) || "Capacity must be a number from 1 to 6."
};

function check(input, form) {
  const value = input.value.trim();
  const box = input.parentElement.querySelector(".err");
  const result = value === "" ? "This field is required." : rules[input.dataset.rule](value, form);
  const good = result === true;
  if (box) box.textContent = good ? "" : result;
  input.className = good ? "ok" : "bad";
  return good;
}

document.querySelectorAll("form.vform").forEach(form => {
  const fields = [...form.querySelectorAll("[data-rule]")];
  fields.forEach(f => {
    f.addEventListener("blur", () => check(f, form));
    if (f.hasAttribute("data-digits")) f.addEventListener("input", () => f.value = f.value.replace(/\D/g, ""));
  });
  form.addEventListener("submit", ev => {
    if (!fields.map(f => check(f, form)).every(Boolean)) ev.preventDefault();
  });
});
