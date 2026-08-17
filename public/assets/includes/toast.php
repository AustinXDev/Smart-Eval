<div id="notification" class="fixed right-0 top-0 md:right-5 md:top-5 bg-white flex items-center w-full max-w-sm p-4 text-body bg-neutral-primary-soft rounded-sm shadow-xs border z-[10000]" role="alert">
    <div id="icon-bg" class="inline-flex items-center justify-center shrink-0 w-7 h-7 text-fg-success bg-success-soft rounded">
        <svg id="icon" class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"></svg>
        <span class="sr-only">Check icon</span>
    </div>
    <div id='notification-message' class="ms-3 text-sm font-normal">Item moved successfully.</div>
    <button id='close-toast' type="button" class="ms-auto flex items-center justify-center text-body hover:text-heading bg-transparent box-border border border-transparent hover:bg-neutral-secondary-medium focus:ring-4 focus:ring-neutral-tertiary font-medium leading-5 rounded text-sm h-8 w-8 focus:outline-none" data-dismiss-target="#toast-success" aria-label="Close">
        <span class="sr-only">Close</span>
        <svg class="w-5 h-5" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18 17.94 6M18 18 6.06 6"/></svg>
    </button>
</div>
