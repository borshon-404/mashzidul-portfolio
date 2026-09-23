<?php
use App\Core\Security;
use App\Security\Csrf;

$pages = $pages ?? [];
$csrf = Csrf::getToken();
?>
<div class="admin-page-header">
  <div class="admin-page-header__left">
    <h2>Pages (<?= count($pages) ?>)</h2>
    <p>Manage static pages and their SEO metadata. Current expected: 11 pages.</p>
  </div>
  <div>
    <a href="/admin/pages/create" class="btn btn--primary">+ Create Page</a>
  </div>
</div>

<?php if (empty($pages)): ?>
  <div class="empty-state">
    <h3>No pages found</h3>
    <p>Create your first page to get started.</p>
    <a href="/admin/pages/create" class="btn btn--primary">Create Page</a>
  </div>
<?php else: ?>
<div class="admin-table-wrapper">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Slug</th>
        <th>Status</th>
        <th>Updated</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($pages as $p): ?>
        <tr>
          <td><strong><?= Security::e($p['title'] ?? '(no title)') ?></strong></td>
          <td><span class="admin-table__slug"><?= Security::e($p['slug']) ?></span></td>
          <td>
            <?php if (($p['is_visible'] ?? 1) == 1): ?>
              <span class="badge badge--success">Visible</span>
            <?php else: ?>
              <span class="badge badge--muted">Hidden</span>
            <?php endif; ?>
          </td>
          <td><span class="text-muted" style="font-size:12px"><?= Security::e($p['updated_at'] ?? $p['created_at'] ?? '-') ?></span></td>
          <td>
            <div class="admin-table__actions">
              <a href="/admin/pages/<?= (int)$p['id'] ?>/edit" class="btn btn--ghost btn--sm">Edit</a>
              <form method="POST" action="/admin/pages/<?= (int)$p['id'] ?>/delete" data-confirm="Are you sure you want to delete page '<?= Security::escapeAttr($p['title'] ?? $p['slug']) ?>'? This will also delete <?= (int)($p['id'] ?? 0) ?> related sections via CASCADE. This cannot be undone." style="display:inline">
                <input type="hidden" name="_csrf" value="<?= Security::e($csrf) ?>">
                <button type="submit" class="btn btn--danger btn--sm">Delete</button>
              </form>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
