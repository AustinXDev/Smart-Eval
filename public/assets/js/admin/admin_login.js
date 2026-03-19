import { notify } from "../../../../resources/components/notify.js";

document.addEventListener("DOMContentLoaded", () => {
  const showToggle = document.getElementById('show');
  const hiddenToggle = document.getElementById('hidden');
  const inputPassword = document.getElementById('inputAdminPassword');


  // main password
  if (showToggle && hiddenToggle && inputPassword) {
    showToggle.addEventListener('click', () => {
      inputPassword.type = 'password';
      showToggle.classList.add('hidden');
      hiddenToggle.classList.remove('hidden');
      console.log('click')
    });

    hiddenToggle.addEventListener('click', () => {
      inputPassword.type = 'text';
      hiddenToggle.classList.add('hidden');
      showToggle.classList.remove('hidden');
    });
  }
});


document.addEventListener('DOMContentLoaded', () => {
  const adminUserName = document.getElementById('inputAdminUserName');
  const adminPassword = document.getElementById('inputAdminPassword');
  const form = document.getElementById('login-form');

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const adminUsernameValue = adminUserName.value;
    const adminPasswordValue = adminPassword.value;

    const data = {
      admin_username : adminUsernameValue,
      admin_password : adminPasswordValue
    }

    fetch('../../app/admin/login.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
      console.log(result);
      if(result.status === 'success'){
        notify(result.status, result.message);
        form.reset();
      } else {
        notify(result.status, result.message);
      }
    })
    .catch(error => {
      console.log(error);
      notify(result.status, 'Something went wrong!')
    })

  })
});