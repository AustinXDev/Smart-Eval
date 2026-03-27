import { notify } from "../../../../../resources/components/notify.js";

const resetBTN = document.getElementById("reset-button");
const form = document.getElementById("forgot-password-form");

form.addEventListener('submit', async e => {
  e.preventDefault();

  const adminUsername = document.getElementById('inputAdmin').value.trim();

  if(!adminUsername) return notify('error', 'Username is required.');

  resetBTN.disabled = true;
  resetBTN.textContent = 'Sending Reset Link...';

  try{
    const res = await fetch('/Smart-Eval/app/admin/send_reset.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ admin_username : adminUsername })
    });

    if(!res.ok){
      notify('error', 'Failed to send reset link. Please try again later.');
      return;
    }

    const data = await res.json();
    notify(data.status, data.message);
    form.reset();
    
  } catch(error) {
     console.error('Error:', error);

      notify('error', 'An error occured while sending the reset link. Please try again later.')
  } finally {
    resetBTN.disabled = false;
    resetBTN.textContent = 'Reset Password';
  }
});