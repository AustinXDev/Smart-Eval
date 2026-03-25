import {notify} from '../../../../resources/components/notify.js';

const form = document.getElementById('addTeacherForm');

form.addEventListener('submit', (e) => {
  e.preventDefault();
  const formData = new FormData(form);

  console.log(formData)
  console.log("Submit clicked"); // 👈 check thiss

  showConfirmation({
    title: "Add Teacher",
    message: "Are you sure you want to add this teacher?",
    onConfirm: () => {
      fetch('/Smart-Eval/app/handlers/add_teacher.php', {
        method: "POST",
        body: formData
      })
      .then(res => {
        if (!res.ok) throw new Error("Network error");
        return res.json();
      })
      .then(data => {
        if (data.status === "success") {
          alert(data.message);
          //notify(data.status, data.message);
          closeModal("addTeacherModal");
          form.reset();
          loadTeachers();
        } else {
          alert(data.errors ? data.errors.join("\n") : data.message);
        }
      })
      .catch(err => {
        console.error(err);
        alert("Something went wrong: " + err.message);
      });
    }
  });
})