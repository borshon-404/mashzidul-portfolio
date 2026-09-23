<?php
use App\Core\Security;
use App\Security\Csrf;

$page = $page ?? null;
$isEdit = $is_edit ?? false;
$errors = $errors ?? [];
$csrf = Csrf::getToken();

$val = function($key, $default='') use ($page) {
    return $page[$key] ?? $default;
};

$err = function($key) use ($errors) {
    return $errors[$key] ?? null;
};

$action = $isEdit ? '/admin/pages/'.(int)($page['id'] ?? 0).'/update' : '/admin/pages';
$titleText = $isEdit ? 'Edit Page' : 'Create Page';
?>
<div class="admin-page-header">
  <div class="admin-page-header__left">
    <h2><?= Security::e($titleText) ?></h2>
    <p><?= $isEdit ? 'Update page details and SEO metadata.' : 'Create a new static page. Slug must be unique and match ^[a-z0-9-]+$' ?></p>
  </div>
  <div>
    <a href="/admin/pages" class="btn btn--ghost">← Back to Pages</a>
  </div>
</div>

<form method="POST" action="<?= Security::e($action) ?>" class="admin-form">
  <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">Basic Information</h3>
    <div class="field__row">
      <div class="field <?= $err('title') ? 'field--error' : '' ?>">
        <label for="field-title">Title <span class="req">*</span></label>
        <input type="text" id="field-title" name="title" value="<?= Security::e((string)$val('title')) ?>" required maxlength="255" placeholder="e.g. About Us">
        <?php if ($err('title')): ?><div class="field__error"><?= Security::e($err('title')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('slug') ? 'field--error' : '' ?>">
        <label for="field-slug">Slug <span class="req">*</span></label>
        <input type="text" id="field-slug" name="slug" value="<?= Security::e((string)$val('slug')) ?>" required pattern="^[a-z0-9-]+$" maxlength="255" placeholder="e.g. about">
        <div class="field__hint">Lowercase letters, numbers, hyphens only. Must be unique.</div>
        <?php if ($err('slug')): ?><div class="field__error"><?= Security::e($err('slug')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field__row">
      <div class="field">
        <label>Visibility</label>
        <label class="checkbox">
          <input type="checkbox" name="is_visible" value="1" <?= ((int)$val('is_visible',1)===1) ? 'checked' : '' ?>>
          <span>Visible (published)</span>
        </label>
      </div>
      <div class="field <?= $err('robots') ? 'field--error' : '' ?>">
        <label for="field-robots">Robots</label>
        <select id="field-robots" name="robots">
          <?php
          $robotsOptions = ['index, follow','noindex, nofollow','index, nofollow','noindex, follow'];
          $currentRobots = $val('robots','index, follow');
          foreach ($robotsOptions as $opt):
          ?>
            <option value="<?= Security::e($opt) ?>" <?= $currentRobots===$opt ? 'selected' : '' ?>><?= Security::e($opt) ?></option>
          <?php endforeach; ?>
        </select>
        <?php if ($err('robots')): ?><div class="field__error"><?= Security::e($err('robots')) ?></div><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">SEO Metadata</h3>
    <p class="admin-form__section-desc">Meta title, description, canonical URL, OG and Twitter metadata.</p>

    <div class="field <?= $err('meta_title') ? 'field--error' : '' ?>">
      <label for="field-meta_title">Meta Title</label>
      <input type="text" id="field-meta_title" name="meta_title" value="<?= Security::e((string)$val('meta_title')) ?>" maxlength="255" placeholder="SEO title (max 255)">
      <?php if ($err('meta_title')): ?><div class="field__error"><?= Security::e($err('meta_title')) ?></div><?php endif; ?>
    </div>

    <div class="field <?= $err('meta_description') ? 'field--error' : '' ?>">
      <label for="field-meta_description">Meta Description</label>
      <textarea id="field-meta_description" name="meta_description" maxlength="1000" placeholder="Meta description (max 1000)"><?= Security::e((string)$val('meta_description')) ?></textarea>
      <?php if ($err('meta_description')): ?><div class="field__error"><?= Security::e($err('meta_description')) ?></div><?php endif; ?>
    </div>

    <div class="field <?= $err('canonical_url') ? 'field--error' : '' ?>">
      <label for="field-canonical_url">Canonical URL</label>
      <input type="url" id="field-canonical_url" name="canonical_url" value="<?= Security::e((string)$val('canonical_url')) ?>" maxlength="255" placeholder="https://mashzidultanun.com/about/">
      <?php if ($err('canonical_url')): ?><div class="field__error"><?= Security::e($err('canonical_url')) ?></div><?php endif; ?>
    </div>

    <div class="field__row">
      <div class="field <?= $err('og_title') ? 'field--error' : '' ?>">
        <label for="field-og_title">OG Title</label>
        <input type="text" id="field-og_title" name="og_title" value="<?= Security::e((string)$val('og_title')) ?>" maxlength="255" placeholder="Open Graph title">
        <?php if ($err('og_title')): ?><div class="field__error"><?= Security::e($err('og_title')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('twitter_title') ? 'field--error' : '' ?>">
        <label for="field-twitter_title">Twitter Title</label>
        <input type="text" id="field-twitter_title" name="twitter_title" value="<?= Security::e((string)$val('twitter_title')) ?>" maxlength="255" placeholder="Twitter title">
        <?php if ($err('twitter_title')): ?><div class="field__error"><?= Security::e($err('twitter_title')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field <?= $err('og_description') ? 'field--error' : '' ?>">
      <label for="field-og_description">OG Description</label>
      <textarea id="field-og_description" name="og_description" maxlength="1000" placeholder="OG description"><?= Security::e((string)$val('og_description')) ?></textarea>
      <?php if ($err('og_description')): ?><div class="field__error"><?= Security::e($err('og_description')) ?></div><?php endif; ?>
    </div>

    <div class="field <?= $err('twitter_description') ? 'field--error' : '' ?>">
      <label for="field-twitter_description">Twitter Description</label>
      <textarea id="field-twitter_description" name="twitter_description" maxlength="1000" placeholder="Twitter description"><?= Security::e((string)$val('twitter_description')) ?></textarea>
      <?php if ($err('twitter_description')): ?><div class="field__error"><?= Security::e($err('twitter_description')) ?></div><?php endif; ?>
    </div>
  </div>

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">Media References (Read-only in M4.2)</h3>
    <p class="admin-form__section-desc">Media library UI is M4.3+. Current references are displayed without upload functionality.</p>

    <div class="field__row">
      <div class="field">
        <label>OG Image ID</label>
        <div class="media-ref">
          <span>Current: <strong><?= Security::e((string)($val('og_image_id') ?? 'None')) ?></strong></span>
        </div>
        <input type="hidden" name="og_image_id" value="<?= Security::e((string)($val('og_image_id') ?? '')) ?>">
      </div>
      <div class="field">
        <label>Twitter Image ID</label>
        <div class="media-ref">
          <span>Current: <strong><?= Security::e((string)($val('twitter_image_id') ?? 'None')) ?></strong></span>
        </div>
        <input type="hidden" name="twitter_image_id" value="<?= Security::e((string)($val('twitter_image_id') ?? '')) ?>">
      </div>
    </div>
    <div class="field__hint">To change media, use Media library in future milestone or update via database directly. No upload UI in M4.2.</div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Update Page' : 'Create Page' ?></button>
    <a href="/admin/pages" class="btn btn--ghost">Cancel</a>
  </div>
</form>
