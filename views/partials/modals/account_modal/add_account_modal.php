<!-- Add account (account_management/form.js submits #addAccountForm) -->
<div id="addAccountModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="addAccountTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-plus"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="addAccountTitle" class="se-modal__title">Add Administrator</h2>
        <p class="se-modal__subtitle">Create an administrator account and email the sign-in details.</p>
      </div>
      <button type="button" data-close-modal="addAccountModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="addAccountForm" class="se-modal__form" novalidate>
      <input type="hidden" name="status" value="active">

      <div class="se-modal__body">
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Account Details</h3>
          <?php $prefix = 'add'; require __DIR__ . '/_account_fields.php'; ?>
        </section>

        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Temporary Password</h3>
          <div class="se-grid">
            <div>
              <label for="add_password" class="se-label">Password <span class="se-required">*</span></label>
              <div class="se-input-icon relative">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                <input type="password" name="password" id="add_password" class="se-input pr-10" maxlength="72" autocomplete="new-password" required
                  aria-describedby="add_password_help add_password_error">
                <button type="button" data-toggle-password="add_password" class="absolute right-1.5 top-1/2 flex h-8 w-8 -translate-y-1/2 items-center justify-center rounded-md text-slate-400 hover:bg-slate-100 hover:text-slate-600 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#6010FF]" aria-label="Show password" aria-pressed="false">
                  <i class="fa-solid fa-eye text-xs" aria-hidden="true"></i>
                </button>
              </div>
              <p id="add_password_help" class="se-help">8–72 characters, with at least one letter and one number.</p>
              <p id="add_password_error" class="se-field-error hidden" data-error-for="password"></p>
            </div>

            <div>
              <label for="add_confirm_password" class="se-label">Confirm Password <span class="se-required">*</span></label>
              <div class="se-input-icon">
                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                <input type="password" name="confirm_password" id="add_confirm_password" class="se-input" maxlength="72" autocomplete="new-password" required
                  aria-describedby="add_confirm_password_error">
              </div>
              <p id="add_confirm_password_error" class="se-field-error hidden" data-error-for="confirm_password"></p>
            </div>
          </div>

          <div class="se-note se-note--info mt-4">
            <i class="fa-solid fa-envelope-circle-check" aria-hidden="true"></i>
            <p>The username and this temporary password are emailed to the new administrator, who should change it after signing in.</p>
          </div>
        </section>
      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="addAccountModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-user-plus text-xs" aria-hidden="true"></i>
          <span>Create Account</span>
        </button>
      </footer>
    </form>
  </div>
</div>
