<?php
use App\Core\Security;
use App\Security\Csrf;

$service = $service ?? null;
$isEdit = $is_edit ?? false;
$errors = $errors ?? [];
$csrf = Csrf::getToken();

$val = fn($k,$d='') => $service[$k] ?? $d;
$err = fn($k) => $errors[$k] ?? null;

$action = $isEdit ? '/admin/services/'.(int)($service['id'] ?? 0).'/update' : '/admin/services';
?>
<div class="admin-page-header">
  <div class="admin-page-header__left">
    <h2><?= $isEdit ? 'Edit Service' : 'Create Service' ?></h2>
    <p><?= $isEdit ? 'Update service details.' : 'Create new service. Slug must be unique ^[a-z0-9-]+$' ?></p>
  </div>
  <div><a href="/admin/services" class="btn btn--ghost">← Back</a></div>
</div>

<form method="POST" action="<?= Security::e($action) ?>" class="admin-form">
  <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">Basic</h3>
    <div class="field__row">
      <div class="field <?= $err('title')?'field--error':'' ?>">
        <label for="field-title">Title <span class="req">*</span></label>
        <input type="text" id="field-title" name="title" value="<?= Security::e((string)$val('title')) ?>" required maxlength="255" placeholder="e.g. Custom Website Design">
        <?php if($err('title')): ?><div class="field__error"><?= Security::e($err('title')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('slug')?'field--error':'' ?>">
        <label for="field-slug">Slug <span class="req">*</span></label>
        <input type="text" id="field-slug" name="slug" value="<?= Security::e((string)$val('slug')) ?>" required pattern="^[a-z0-9-]+$" maxlength="255" placeholder="e.g. custom-website-design">
        <div class="field__hint">Unique, lowercase, hyphens only.</div>
        <?php if($err('slug')): ?><div class="field__error"><?= Security::e($err('slug')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field <?= $err('description')?'field--error':'' ?>">
      <label for="field-description">Description</label>
      <textarea id="field-description" name="description" maxlength="5000" placeholder="Service description"><?= Security::e((string)$val('description')) ?></textarea>
      <?php if($err('description')): ?><div class="field__error"><?= Security::e($err('description')) ?></div><?php endif; ?>
    </div>

    <div class="field__row">
      <div class="field <?= $err('icon_key')?'field--error':'' ?>">
        <label for="field-icon_key">Icon Key</label>
        <input type="text" id="field-icon_key" name="icon_key" value="<?= Security::e((string)$val('icon_key')) ?>" maxlength="50" placeholder="e.g. palette, code, globe, cart">
        <div class="field__hint">Allowed: palette, code, globe, cart, monitor, refresh, wrench, gauge, search, bug or custom [a-z0-9_-]</div>
        <?php if($err('icon_key')): ?><div class="field__error"><?= Security::e($err('icon_key')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('order_index')?'field--error':'' ?>">
        <label for="field-order_index">Order Index</label>
        <input type="number" id="field-order_index" name="order_index" value="<?= Security::e((string)$val('order_index',0)) ?>" min="0" max="9999" placeholder="0">
        <?php if($err('order_index')): ?><div class="field__error"><?= Security::e($err('order_index')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field">
      <label>Visibility</label>
      <label class="checkbox">
        <input type="checkbox" name="is_visible" value="1" <?= ((int)$val('is_visible',1)===1)?'checked':'' ?>>
        <span>Visible</span>
      </label>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Update Service' : 'Create Service' ?></button>
    <a href="/admin/services" class="btn btn--ghost">Cancel</a>
  </div>
</form>
