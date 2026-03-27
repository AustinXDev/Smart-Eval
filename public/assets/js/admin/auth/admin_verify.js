import { notify } from "../../../../../resources/components/notify.js";

const otpInputs = document.querySelectorAll('.otp-input');


otpInputs.forEach((input, index) => {

  //Only allows numbers
  input.addEventListener('keypress', (e) => {
        if (!/[0-9]/.test(e.key)) {
            e.preventDefault();
        }
    });
  
  // Auto move to next input
  input.addEventListener('input', () => {
        if (input.value.length === 1) {
            if (index < otpInputs.length - 1) {
                otpInputs[index + 1].focus(); // ← move to next
            } else {
                submitOTP(); // ← auto submit on last digit
            }
        }
  });  

  // Move back on backspace
  input.addEventListener('keydown', (e) => {
        if (e.key === 'Backspace' && input.value === '') {
            if (index > 0) {
                otpInputs[index - 1].focus(); // ← move to previous
                otpInputs[index - 1].value = '';
            }
        }
  });

  //handle paste
  input.addEventListener('paste', (e) => {
      e.preventDefault();
      const pasted = e.clipboardData.getData('text').slice(0, 6);
      if (!/^\d+$/.test(pasted)) return; // numbers only
      pasted.split('').forEach((char, i) => {
          if (otpInputs[i]) otpInputs[i].value = char;
      });
      otpInputs[Math.min(pasted.length, 5)].focus();

      if(pasted.length === 6){
        submitOTP();
      }
  });
});

function getOTPCode() {
    return Array.from(otpInputs).map(i => i.value).join('');
}

function clearOTP() {
    otpInputs.forEach(input => {
        input.value = '';
        input.classList.remove('filled');
    });
    otpInputs[0].focus();
}

function submitOTP() {
    const code = getOTPCode();
    if (code.length < 6) {
        notify('error', '❌ Please enter the complete 6-digit code.');
        return;
    }
    
    fetch('/Smart-Eval/app/admin/verify_2FA.php', {
      method: 'POST',
      headers: {
        'Content-Type' : 'application/json',
      },
      body: JSON.stringify({ code })
    })
    .then(response => response.json())
    .then(result => {
      if(result.status === 'success'){
        notify('success', result.message);
        setTimeout(() => {
                window.location.href = '/Smart-Eval/views/admin/dashboard.view.php';
        }, 1500);
        clearOTP();
      } else {
        notify('error', result.message);
        clearOTP();
      }
    })
    .catch(error => {
      console.log(error);
      notify('error', 'Something went wrong!');
      clearOTP();
    })
}

//resend btn function
let countdown = 60;
const timerEl = document.getElementById('timer');
const countdownText = document.getElementById('countdown-text');
const resendBTN = document.getElementById('resend-btn');

function startCountdown(){
  countdown = 10;
  countdownText.classList.remove('hidden');
  resendBTN.classList.add('hidden');

  const interval = setInterval(() => {
    countdown--;
    timerEl.textContent = countdown;

    if(countdown <= 0){
      clearInterval(interval);
      countdownText.classList.add('hidden');
      resendBTN.classList.remove('hidden');
    }
  }, 1000)
}

startCountdown();

resendBTN.addEventListener('click', () => {
  resendBTN.disabled = true;
  resendBTN.textContent = 'Sending...';

  fetch('../../app/admin/resend_2FA.php', {
    method: 'POST',
    headers: {
      'Content-Type' : 'application/json',
    }
  })
  .then((response => response.json()))
  .then(result => {

    if(result.status === 'success'){
      notify('success', result.message);
      startCountdown();
      clearOTP();
    } else {
      notify('error', result.message);
      resendBTN.disabled = false;
      resendBTN.textContent = 'Resend Code';
    } 

  }) .catch(error => {
    console.log(error);
    notify('error', 'Something went wrong!');
  }) .finally(() => {
    resendBTN.disabled = false;
    resendBTN.textContent = 'Resend Code';
  })
});



