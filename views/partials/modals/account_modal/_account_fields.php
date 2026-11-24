<?php
/**
 * Fields shared by the Add and Edit account forms.
 * $prefix: id prefix ("add" / "edit"). Each field has a <p data-error-for="…">
 * that account_management/form.js fills with the server's field message.
 */
$fieldId = static fn (string $name): string => $prefix . '_' . $name;
?>
<div class="se-grid">
  <div>
    <label for="<?= $fieldId('full_name') ?>" class="se-label">Full Name <span class="se-required">*</span></label>
    <div class="se-input-icon">
      <i class="fa-solid fa-user" aria-hidden="true"></i>
      <input type="text" name="full_name" id="<?= $fieldId('full_name') ?>" class="se-input" maxlength="100" autocomplete="off" required
        aria-describedby="<?= $fieldId('full_name') ?>_error">
    </div>
    <p id="<?= $fieldId('full_name') ?>_error" class="se-field-error hidden" data-error-for="full_name"></p>
  </div>

  <div>
    <label for="<?= $fieldId('username') ?>" class="se-label">Username <span class="se-required">*</span></label>
    <div class="se-input-icon">
      <i class="fa-solid fa-at" aria-hidden="true"></i>
      <input type="text" name="username" id="<?= $fieldId('username') ?>" class="se-input" maxlength="50" autocomplete="off" required
        pattern="[A-Za-z0-9._\-]{4,50}" aria-describedby="<?= $fieldId('username') ?>_help <?= $fieldId('username') ?>_error">
    </div>
    <p id="<?= $fieldId('username') ?>_help" class="se-help">4–50 letters, numbers, dots, dashes or underscores.</p>
    <p id="<?= $fieldId('username') ?>_error" class="se-field-error hidden" data-error-for="username"></p>
  </div>

  <div>
    <label for="<?= $fieldId('email') ?>" class="se-label">Email Address <span class="se-required">*</span></label>
    <div class="se-input-icon">
      <i class="fa-solid fa-envelope" aria-hidden="true"></i>
      <input type="email" name="email" id="<?= $fieldId('email') ?>" class="se-input" maxlength="100" autocomplete="off" required
        aria-describedby="<?= $fieldId('email') ?>_error">
    </div>
    <p id="<?= $fieldId('email') ?>_error" class="se-field-error hidden" data-error-for="email"></p>
  </div>

  <div>
    <label for="<?= $fieldId('role') ?>" class="se-label">Role <span class="se-required">*</span></label>
    <div class="se-input-icon">
      <i class="fa-solid fa-user-tag" aria-hidden="true"></i>
      <select name="role" id="<?= $fieldId('role') ?>" class="se-select" required aria-describedby="<?= $fieldId('role') ?>_error">
        <option value="" disabled selected>Select a role</option>
        <option value="super admin">Super Admin (full access)</option>
        <option value="department head">Department Head (dashboards, reports, monitoring)</option>
      </select>
    </div>
    <p id="<?= $fieldId('role') ?>_error" class="se-field-error hidden" data-error-for="role"></p>
  </div>
</div>
