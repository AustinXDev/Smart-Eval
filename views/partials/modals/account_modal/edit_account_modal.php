<!-- Edit account (account_management/form.js fills and submits #editAccountForm) -->
<div id="editAccountModal" class="se-modal hidden" role="dialog" aria-modal="true" aria-labelledby="editAccountTitle">
  <div class="se-modal__panel se-modal__panel--md">

    <header class="se-modal__header">
      <span class="se-modal__icon" aria-hidden="true">
        <i class="fa-solid fa-user-pen"></i>
      </span>
      <div class="se-modal__heading">
        <h2 id="editAccountTitle" class="se-modal__title">Edit Administrator</h2>
        <p class="se-modal__subtitle">Update the account details and access status.</p>
      </div>
      <button type="button" data-close-modal="editAccountModal" class="se-modal__close" aria-label="Close">
        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
      </button>
    </header>

    <form id="editAccountForm" class="se-modal__form" novalidate>
      <input type="hidden" name="admin_id" id="edit_admin_id">

      <div class="se-modal__body">
        <section class="se-modal__section">
          <h3 class="se-modal__section-title">Account Details</h3>
          <?php $prefix = 'edit'; require __DIR__ . '/_account_fields.php'; ?>
        </section>

        <section class="se-modal__section">
          <fieldset>
            <legend class="se-modal__section-title">Account Status</legend>
            <div class="se-choices se-choices--stack" id="editStatusChoices">
              <label class="se-choice">
                <input type="radio" name="status" value="active">
                <span class="se-choice__card">
                  <span class="se-choice__icon" aria-hidden="true"><i class="fa-solid fa-user-check"></i></span>
                  <span class="se-choice__text">
                    <span class="se-choice__title">Activate</span>
                    <span class="se-choice__hint">Can sign in and use the administrator portal</span>
                  </span>
                </span>
              </label>
              <label class="se-choice">
                <input type="radio" name="status" value="inactive">
                <span class="se-choice__card">
                  <span class="se-choice__icon" aria-hidden="true"><i class="fa-solid fa-user-lock"></i></span>
                  <span class="se-choice__text">
                    <span class="se-choice__title">Deactivate</span>
                    <span class="se-choice__hint">Sign-in is blocked and any open session ends</span>
                  </span>
                </span>
              </label>
            </div>
            <p class="se-field-error hidden" data-error-for="status"></p>
          </fieldset>
          <p id="editSelfNote" class="se-help hidden">This is your account: you can't change your own role or deactivate it.</p>
        </section>
      </div>

      <footer class="se-modal__footer">
        <button type="button" data-close-modal="editAccountModal" class="se-btn se-btn--secondary">Cancel</button>
        <button type="submit" class="se-btn se-btn--primary">
          <i class="fa-solid fa-floppy-disk text-xs" aria-hidden="true"></i>
          <span>Save Changes</span>
        </button>
      </footer>
    </form>
  </div>
</div>
