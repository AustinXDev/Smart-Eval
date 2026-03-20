import { notify } from "../../../../resources/components/notify.js";

const resetBTN = document.getElementById('reset-button');
const form = document.getElementById('forgot-password-form');

form.addEventListener('submit', async e => {
    e.preventDefault();
    const studentID = document.getElementById('inputStudentID').value.trim();
    if (!studentID) return notify('error', 'Student ID is required');

    resetBTN.disabled = true;
    resetBTN.textContent = 'Sending reset link...';

    try{
    const res = await fetch('../../app/auth/send_reset.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ studentID })
    });

    if(!res.ok){
        throw new Error('Failed to send reset link. Please try again later.');
    }

    //const text = await res.text();
    //console.log(text);

    const data = await res.json();
    notify(data.status, data.message);
    form.reset();

    } catch (error) {
        console.error('Error:', error);

        notify('error', 'An error occured while sending the reset link. Please try again later.')
    }   finally {
        resetBTN.disabled = false;
        resetBTN.textContent = 'Reset Password';
    }

});