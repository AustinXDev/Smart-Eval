import { notify } from "../../../../resources/components/notify.js";

document.getElementById('forgot-password-form').addEventListener('submit', async e => {
    e.preventDefault();
    const studentID = document.getElementById('inputStudentID').value.trim();
    if (!studentID) return notify('error', 'Student ID is required');

    const res = await fetch('../../app/auth/send_reset.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ studentID })
    });

    const data = await res.json();
    notify(data.status, data.message);
});