import { notify } from "../../../../resources/views/components/notify.js";

document.addEventListener("DOMContentLoaded", () => {
  const showConfirmToggle = document.getElementById("showConfirm");
  const hideConfirmToggle = document.getElementById("hideConfirm");
  const showToggle = document.getElementById("show");
  const hiddenToggle = document.getElementById("hidden");
  const inputPassword = document.getElementById("inputPassword");
  const inputConfirmPassword = document.getElementById("inputConfirmPassword");

  // main password
  if (showToggle && hiddenToggle && inputPassword) {
    showToggle.addEventListener("click", () => {
      inputPassword.type = "password";
      showToggle.classList.add("hidden");
      hiddenToggle.classList.remove("hidden");
    });

    hiddenToggle.addEventListener("click", () => {
      inputPassword.type = "text";
      hiddenToggle.classList.add("hidden");
      showToggle.classList.remove("hidden");
    });
  }

  // confirm password
  if (showConfirmToggle && hideConfirmToggle && inputConfirmPassword) {
    showConfirmToggle.addEventListener("click", () => {
      inputConfirmPassword.type = "password";
      showConfirmToggle.classList.add("hidden");
      hideConfirmToggle.classList.remove("hidden");
    });

    hideConfirmToggle.addEventListener("click", () => {
      inputConfirmPassword.type = "text";
      hideConfirmToggle.classList.add("hidden");
      showConfirmToggle.classList.remove("hidden");
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const studentID = document.getElementById("inputStudentID");
  const password = document.getElementById("inputPassword");
  const confirmPassword = document.getElementById("inputConfirmPassword");
  const form = document.getElementById("signUpForm");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const StudentIDValue = studentID.value;
    const passwordValue = password.value;
    const confirmPasswordValue = confirmPassword.value;

    if (passwordValue !== confirmPasswordValue) {
      notify("error", "Password does not match");
      return;
    }

    if (
      passwordValue.length < 8 ||
      passwordValue.length > 16 ||
      !/[0-9]/.test(passwordValue) ||
      !/[!@#$%^&*(),.?":{}|<>]/.test(passwordValue)
    ) {
      notify(
        "warning",
        "Password must be 8-16 characters long, include at least one number and one special character.",
      );
      return;
    }

    const data = {
      student_id: StudentIDValue,
      password: passwordValue,
    };

    fetch("../../app/auth/register.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((result) => {
        if (result.status === "success") {
          notify("success", result.message);
          //window.location.href = '../../views/student/evaluation.view.php';
          console.log(result);
          form.reset();
        } else {
          notify("error", result.message);
          alert(result.message);
          console.log("incorrect username and password");
        }
      })
      .catch((error) => {
        console.log(error);
        //notify('error', 'Something went wrong');
      });
  });
});
