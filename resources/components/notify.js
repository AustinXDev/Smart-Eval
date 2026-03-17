const toast = document.getElementById('notification');
const toastMessage = document.getElementById('notification-message');
const iconBg = document.getElementById('icon-bg');
const icon = document.getElementById('icon');
const closeToast = document.getElementById('close-toast');

const ToastTypes = {
    success: {
      border: 'border-green-200',
      icon: '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 11.917 9.724 16.5 19 7.5"/>',
      icontxt: 'text-green-700',
      iconBg: 'bg-green-200',
      txtColor: 'text-green-700'
    },
    error: {
      border: 'border-red-500',
      icon: '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/>',
      icontxt: 'text-red-700',
      iconBg: 'bg-red-300',
      txtColor: 'text-red-700'
    },
    warning: {
      border: 'border-orange-red-200',
      icon: '<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 13V8m0 8h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>',
      icontxt: 'text-orange-700',
      iconBg: 'bg-orange-400',
      txtColor: 'text-orange-700'
    }
}

export function notify(type, message, duration = 5000){
    toastMessage.textContent = message;

    if(ToastTypes[type]){
      toast.classList.add(ToastTypes[type].border);
      iconBg.classList.add(ToastTypes[type].iconBg);
      icon.classList.add(ToastTypes[type].icontxt);
      icon.innerHTML = ToastTypes[type].icon;
      toastMessage.classList.add(ToastTypes[type].txtColor);
    }

    toast.classList.add('show');

    const hideTimeout = setTimeout(() => {
        toast.classList.remove('show');
    }, duration);

    closeToast.onclick = () => {
      toast.classList.remove('show');
      clearTimeout(hideTimeout);
    }

}