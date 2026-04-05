import { notify } from "../../../../resources/components/notify.js";

document.addEventListener("DOMContentLoaded", () => {
  const showToggle = document.getElementById("show");
  const hiddenToggle = document.getElementById("hidden");
  const inputPassword = document.getElementById("inputPassword");

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
});

//login validation
document.addEventListener("DOMContentLoaded", () => {
  const studentID = document.getElementById("inputStudentID");
  const password = document.getElementById("inputPassword");
  const form = document.getElementById("login-form");
  const signinBTN = document.getElementById("signin-btn");

  form.addEventListener("submit", (e) => {
    e.preventDefault();

    const StudentIDValue = studentID.value;
    const passwordValue = password.value;

    //console.log(StudentIDValue, passwordValue);
    const data = {
      student_id: StudentIDValue,
      password: passwordValue,
    };

    signinBTN.disabled = true;
    signinBTN.value = "Sign In...";

    let isSuccess = false;

    fetch("../../app/auth/login.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((res) => res.json())
      .then((result) => {
        if (result.status === "success") {
          isSuccess = true;

          notify("success", result.message);
          signinBTN.textContent = "Redirecting...";

          setTimeout(() => {
            signinBTN.value = "Sign In";
            window.location.href = result.redirect;
          }, 1500);
          //console.log(result);
          form.reset();
        } else {
          notify("error", result.message);
          console.log("incorrect username and password");
        }
      })
      .catch((error) => {
        console.log(error);
        notify("error", "Something went wrong");
      })
      .finally(() => {
        if (!isSuccess) {
          signinBTN.disabled = false;
          signinBTN.value = "Sign In";
        }
      });
  });
});
