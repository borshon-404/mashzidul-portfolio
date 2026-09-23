<?php
use App\Core\Security;
use App\Security\Csrf;

$projects = $projects ?? [];
$csrf = Csrf::getToken();
?>
<div class="admin-page-header">
  <div class="admin-page-header__left">
    <h2>Projects (<?= count($projects) ?>)</h2>
    <p>Manage portfolio projects. Expected 4 migrated projects must remain intact until intentionally edited.</p>
  </div>
  <div><a href="/admin/projects/create" class="btn btn--primary">+ Create Project</a></div>
</div>

<?php if (empty($projects)): ?>
  <div class="empty-state">
    <h3>No projects found</h3>
    <p>Create your first project.</p>
    <a href="/admin/projects/create" class="btn btn--primary">Create Project</a>
  </div>
<?php else: ?>
<div class="admin-table-wrapper">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Slug</th>
        <th>Category</th>
        <th>Tech / Features</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($projects as $p): ?>
        <tr>
          <td>
            <strong><?= Security::e($p['title']) ?></strong><br>
            <span class="text-muted" style="font-size:12px">ID: <?= (int)$p['id'] ?> | Order: <?= (int)($p['order_index'] ?? 0) ?><?php if (!empty($p['art_media_id'])): ?> | Media: <?= (int)$p['art_media_id'] ?><?php endif; ?></span>
          </td>
          <td><span class="admin-table__slug"><?= Security::e($p['slug']) ?></span></td>
          <td><?= Security::e($p['category'] ?? '-') ?></td>
          <td>
            <span class="badge badge--muted"><?= (int)($p['_tech_count'] ?? 0) ?> tech</span>
            <span class="badge badge--muted"><?= (int)($p['_feat_count'] ?? 0) ?> feats</span>
            <?php if (!empty($p['is_featured'])): ?><span class="badge badge--warning">Featured</span><?php endif; ?>
          </td>
          <td>
            <?php if (($p['is_visible'] ?? 1)==1): ?><span class="badge badge--success">Visible</span><?php else: ?><span class="badge badge--muted">Hidden</span><?php endif; ?>
          </td>
          <td>
            <div class="admin-table__actions">
              <a href="/admin/projects/<?= (int)$p['id'] ?>/edit" class="btn btn--ghost btn--sm">Edit</a>
              <form method="POST" action="/admin/projects/<?= (int)$p['id'] ?>/delete" data-confirm="Delete project '<?= Security::escapeAttr($p['title']) ?>'? This will delete <?= (int)($p['_tech_count'] ?? 0) ?> technologies and <?= (int)($p['_feat_count'] ?? 0) ?> features via CASCADE. Cannot be undone." style="display:inline">
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
