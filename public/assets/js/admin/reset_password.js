import { notify } from "../../../../resources/components/notify.js";

document.addEventListener("DOMContentLoaded", () => {
  const showConfirmToggle = document.getElementById('showConfirm');
  const hideConfirmToggle = document.getElementById('hideConfirm');
  const showToggle = document.getElementById('show');
  const hiddenToggle = document.getElementById('hidden');
  const inputPassword = document.getElementById('inputNewPassword');
  const inputConfirmPassword = document.getElementById('inputConfirmPassword');

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

  // confirm password
  if (showConfirmToggle && hideConfirmToggle && inputConfirmPassword) {
    showConfirmToggle.addEventListener('click', () => {
      inputConfirmPassword.type = 'password';
      showConfirmToggle.classList.add('hidden');
      hideConfirmToggle.classList.remove('hidden');
    });

    hideConfirmToggle.addEventListener('click', () => {
      inputConfirmPassword.type = 'text';
      hideConfirmToggle.classList.add('hidden');
      showConfirmToggle.classList.remove('hidden');
    });
  }
});

// Check if have token
const token = new URLSearchParams(window.location.search).get('token');

if(!token){
  alert('invalid or missing token. Please request a new password reset link.');
  window.location.href = '../../views/admin/forgot_password.view.php';
}

const form = document.getElementById('forgot-password-form');
const resetBTN = document.getElementById('reset-btn');

document.addEventListener('DOMContentLoaded', () => {
form.addEventListener('submit', async e => {
  e.preventDefault();

  const newPassword = document.getElementById('inputNewPassword').value.trim();
  const confirmPassword = document.getElementById('inputConfirmPassword').value.trim();

  const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;

  if(newPassword !== confirmPassword){
    notify('error', 'Password does not match.');
    return;
  }

  if(!passwordRegex.test(newPassword)){
    notify('error', 'Password must be min 8 chars, include uppercase, lowercase, number & symbol.');
    return;
  }

  resetBTN.disabled = true;
  resetBTN.textContent = 'Resetting password...';

  try{

    const res = await fetch('../../app/admin/password_reset.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({ token: token, password: newPassword})
    });

    const data = await res.json();
    notify(data.status, data.message);

    if(data.status === 'success'){
      setTimeout(() => {
        window.location.href = '../../views/admin/login.view.php';
      }, 2000);
    }

  } catch (error) {

    console.error('Error:', error);
    notify('error', 'An error occured while resetting the password. Please try again later.')

  } finally {

    resetBTN.disabled = false;
    resetBTN.textContent = 'Reset Password';

  }


})

});