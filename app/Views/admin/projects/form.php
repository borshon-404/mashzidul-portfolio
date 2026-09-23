<?php
use App\Core\Security;
use App\Security\Csrf;

$project = $project ?? null;
$isEdit = $is_edit ?? false;
$errors = $errors ?? [];
$csrf = Csrf::getToken();

$val = fn($k,$d='') => $project[$k] ?? $d;
$err = fn($k) => $errors[$k] ?? null;

$action = $isEdit ? '/admin/projects/'.(int)($project['id'] ?? 0).'/update' : '/admin/projects';

// Normalize techs and features for form
$techList = $project['technologies_list'] ?? $project['technologies'] ?? [];
if (!is_array($techList)) $techList = [];
// techList may be array of arrays (from DB) or strings
$techStrings = [];
foreach ($techList as $t) {
    if (is_array($t) && isset($t['technology'])) $techStrings[] = $t['technology'];
    elseif (is_string($t)) $techStrings[] = $t;
    elseif (is_array($t) && isset($t[0])) $techStrings[] = $t[0];
}
$featList = $project['features_list'] ?? $project['features'] ?? [];
$featStrings = [];
foreach ($featList as $f) {
    if (is_array($f) && isset($f['feature'])) $featStrings[] = $f['feature'];
    elseif (is_string($f)) $featStrings[] = $f;
}

?>
<div class="admin-page-header">
  <div class="admin-page-header__left">
    <h2><?= $isEdit ? 'Edit Project' : 'Create Project' ?></h2>
    <p><?= $isEdit ? 'Update project and its technologies/features. Uses transaction to prevent partial updates.' : 'Create new project with technologies and features. Transactional safe.' ?></p>
  </div>
  <div><a href="/admin/projects" class="btn btn--ghost">← Back</a></div>
</div>

<form method="POST" action="<?= Security::e($action) ?>" class="admin-form">
  <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">Basic Information</h3>
    <div class="field__row">
      <div class="field <?= $err('title')?'field--error':'' ?>">
        <label for="field-title">Title <span class="req">*</span></label>
        <input type="text" id="field-title" name="title" value="<?= Security::e((string)$val('title')) ?>" required maxlength="255" placeholder="e.g. SEO Agency Website">
        <?php if($err('title')): ?><div class="field__error"><?= Security::e($err('title')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('slug')?'field--error':'' ?>">
        <label for="field-slug">Slug <span class="req">*</span></label>
        <input type="text" id="field-slug" name="slug" value="<?= Security::e((string)$val('slug')) ?>" required pattern="^[a-z0-9-]+$" maxlength="255" placeholder="e.g. seo-agency-website">
        <div class="field__hint">Unique, lowercase, hyphens only.</div>
        <?php if($err('slug')): ?><div class="field__error"><?= Security::e($err('slug')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field__row">
      <div class="field <?= $err('category')?'field--error':'' ?>">
        <label for="field-category">Category</label>
        <input type="text" id="field-category" name="category" value="<?= Security::e((string)$val('category')) ?>" maxlength="100" placeholder="e.g. Web Design">
        <?php if($err('category')): ?><div class="field__error"><?= Security::e($err('category')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('client_name')?'field--error':'' ?>">
        <label for="field-client_name">Client Name</label>
        <input type="text" id="field-client_name" name="client_name" value="<?= Security::e((string)$val('client_name')) ?>" maxlength="255" placeholder="e.g. Acme Corp">
        <?php if($err('client_name')): ?><div class="field__error"><?= Security::e($err('client_name')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field__row">
      <div class="field <?= $err('role')?'field--error':'' ?>">
        <label for="field-role">Role</label>
        <input type="text" id="field-role" name="role" value="<?= Security::e((string)$val('role')) ?>" maxlength="255" placeholder="e.g. Full Stack Developer">
        <?php if($err('role')): ?><div class="field__error"><?= Security::e($err('role')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('project_date')?'field--error':'' ?>">
        <label for="field-project_date">Project Date</label>
        <input type="date" id="field-project_date" name="project_date" value="<?= Security::e((string)$val('project_date')) ?>" placeholder="YYYY-MM-DD">
        <?php if($err('project_date')): ?><div class="field__error"><?= Security::e($err('project_date')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field__row">
      <div class="field <?= $err('duration')?'field--error':'' ?>">
        <label for="field-duration">Duration</label>
        <input type="text" id="field-duration" name="duration" value="<?= Security::e((string)$val('duration')) ?>" maxlength="50" placeholder="e.g. 2 weeks">
        <?php if($err('duration')): ?><div class="field__error"><?= Security::e($err('duration')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('cost')?'field--error':'' ?>">
        <label for="field-cost">Cost</label>
        <input type="text" id="field-cost" name="cost" value="<?= Security::e((string)$val('cost')) ?>" maxlength="50" placeholder="e.g. $1200">
        <?php if($err('cost')): ?><div class="field__error"><?= Security::e($err('cost')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field__row">
      <div class="field <?= $err('live_url')?'field--error':'' ?>">
        <label for="field-live_url">Live URL</label>
        <input type="url" id="field-live_url" name="live_url" value="<?= Security::e((string)$val('live_url')) ?>" maxlength="255" placeholder="https://example.com">
        <?php if($err('live_url')): ?><div class="field__error"><?= Security::e($err('live_url')) ?></div><?php endif; ?>
      </div>
      <div class="field <?= $err('github_url')?'field--error':'' ?>">
        <label for="field-github_url">GitHub URL</label>
        <input type="url" id="field-github_url" name="github_url" value="<?= Security::e((string)$val('github_url')) ?>" maxlength="255" placeholder="https://github.com/user/repo">
        <?php if($err('github_url')): ?><div class="field__error"><?= Security::e($err('github_url')) ?></div><?php endif; ?>
      </div>
    </div>

    <div class="field <?= $err('overview')?'field--error':'' ?>">
      <label for="field-overview">Overview</label>
      <textarea id="field-overview" name="overview" maxlength="10000" placeholder="Project overview..."><?= Security::e((string)$val('overview')) ?></textarea>
      <?php if($err('overview')): ?><div class="field__error"><?= Security::e($err('overview')) ?></div><?php endif; ?>
    </div>

    <div class="field__row">
      <div class="field <?= $err('order_index')?'field--error':'' ?>">
        <label for="field-order_index">Order Index</label>
        <input type="number" id="field-order_index" name="order_index" value="<?= Security::e((string)$val('order_index',0)) ?>" min="0" max="9999">
        <?php if($err('order_index')): ?><div class="field__error"><?= Security::e($err('order_index')) ?></div><?php endif; ?>
      </div>
      <div class="field">
        <label>Flags</label>
        <div style="display:flex;gap:12px;flex-wrap:wrap">
          <label class="checkbox"><input type="checkbox" name="is_visible" value="1" <?= ((int)$val('is_visible',1)===1)?'checked':'' ?>><span>Visible</span></label>
          <label class="checkbox"><input type="checkbox" name="is_featured" value="1" <?= ((int)$val('is_featured',0)===1)?'checked':'' ?>><span>Featured</span></label>
        </div>
      </div>
    </div>

    <div class="field">
      <label>Featured Media Reference (Read-only M4.2)</label>
      <div class="media-ref">Current art_media_id: <strong><?= Security::e((string)($val('art_media_id') ?? 'None')) ?></strong> — Media library UI is future milestone.</div>
      <input type="hidden" name="art_media_id" value="<?= Security::e((string)($val('art_media_id') ?? '')) ?>">
    </div>
  </div>

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">Technologies (Normalized)</h3>
    <p class="admin-form__section-desc">Add/remove technologies. Unique per project. Uses transaction, prevents orphan records, prepared statements. Table: project_technologies (project_id, technology) UNIQUE(project_id, technology) CASCADE.</p>
    <?php if($err('technologies')): ?><div class="field__error" style="margin-bottom:12px"><?= Security::e($err('technologies')) ?></div><?php endif; ?>

    <div id="tech-list" class="dynamic-list">
      <div class="dynamic-list__items">
        <?php if (!empty($techStrings)): ?>
          <?php foreach ($techStrings as $t): ?>
            <div class="dynamic-list__item">
              <input type="text" name="technologies[]" value="<?= Security::e($t) ?>" placeholder="e.g. React">
              <button type="button" class="btn btn--ghost btn--sm btn--remove">Remove</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="dynamic-list__item">
            <input type="text" name="technologies[]" placeholder="e.g. HTML">
            <button type="button" class="btn btn--ghost btn--sm btn--remove">Remove</button>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn btn--ghost btn--sm dynamic-list__add">+ Add Technology</button>
      <div class="field__hint">Each technology 1-100 chars, max 50. Duplicates removed automatically.</div>
    </div>
  </div>

  <div class="admin-form__section">
    <h3 class="admin-form__section-title">Features (Normalized with Ordering)</h3>
    <p class="admin-form__section-desc">Add/remove features. Preserves ordering via order_index. Transactional. Table: project_features (project_id, feature, order_index) CASCADE.</p>
    <?php if($err('features')): ?><div class="field__error" style="margin-bottom:12px"><?= Security::e($err('features')) ?></div><?php endif; ?>

    <div id="feat-list" class="dynamic-list">
      <div class="dynamic-list__items">
        <?php if (!empty($featStrings)): ?>
          <?php foreach ($featStrings as $f): ?>
            <div class="dynamic-list__item">
              <input type="text" name="features[]" value="<?= Security::e($f) ?>" placeholder="e.g. Responsive design">
              <button type="button" class="btn btn--ghost btn--sm btn--remove">Remove</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="dynamic-list__item">
            <input type="text" name="features[]" placeholder="e.g. SEO optimized">
            <button type="button" class="btn btn--ghost btn--sm btn--remove">Remove</button>
          </div>
        <?php endif; ?>
      </div>
      <button type="button" class="btn btn--ghost btn--sm dynamic-list__add">+ Add Feature</button>
      <div class="field__hint">Each feature 1-2000 chars, max 100. Order is saved as displayed (order_index).</div>
    </div>
  </div>

  <div class="form-actions">
    <button type="submit" class="btn btn--primary"><?= $isEdit ? 'Update Project' : 'Create Project' ?></button>
    <a href="/admin/projects" class="btn btn--ghost">Cancel</a>
  </div>
</form>
