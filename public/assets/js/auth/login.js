import {notify} from '../../../../resources/components/notify.js';

document.addEventListener("DOMContentLoaded", () => {
  const showToggle = document.getElementById('show');
  const hiddenToggle = document.getElementById('hidden');
  const inputPassword = document.getElementById('inputPassword');


  // main password
  if (showToggle && hiddenToggle && inputPassword) {
    showToggle.addEventListener('click', () => {
      inputPassword.type = 'password';
      showToggle.classList.add('hidden');
      hiddenToggle.classList.remove('hidden');
    });

    hiddenToggle.addEventListener('click', () => {
      inputPassword.type = 'text';
      hiddenToggle.classList.add('hidden');
      showToggle.classList.remove('hidden');
    });
  }
});


//login validation

document.addEventListener('DOMContentLoaded', () => {
    const studentID = document.getElementById('inputStudentID');
    const password = document.getElementById('inputPassword');
    const form = document.getElementById('login-form');

    form.addEventListener('submit', (e) => {
        e.preventDefault();

        const StudentIDValue = studentID.value;
        const passwordValue = password.value;

        //console.log(StudentIDValue, passwordValue);
        const data = {
            student_id: StudentIDValue,
            password: passwordValue
        }

        fetch('../../app/auth/login.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }, 
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(result => {
            if(result.status === 'success'){
                notify('success', result.message);
                window.location.href = '../../views/student/evaluation.view.php';
                console.log(result);
                //form.reset();
            } else {
                notify('error', result.message);
                console.log('incorrect username and password');
            }
        })
        .catch(error => {
            console.log(error);
            notify('error', 'Something went wrong');
        })
    })
});