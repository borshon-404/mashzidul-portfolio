<?php
use App\Core\Security;
use App\Security\Csrf;

$services = $services ?? [];
$csrf = Csrf::getToken();
?>
<div class="admin-page-header">
  <div class="admin-page-header__left">
    <h2>Services (<?= count($services) ?>)</h2>
    <p>Manage services. Expected 10 migrated services must remain intact.</p>
  </div>
  <div>
    <a href="/admin/services/create" class="btn btn--primary">+ Create Service</a>
  </div>
</div>

<?php if (empty($services)): ?>
  <div class="empty-state">
    <h3>No services found</h3>
    <p>Create your first service.</p>
    <a href="/admin/services/create" class="btn btn--primary">Create Service</a>
  </div>
<?php else: ?>
<div class="admin-table-wrapper">
  <table class="admin-table">
    <thead>
      <tr>
        <th>Title</th>
        <th>Slug</th>
        <th>Order</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($services as $s): ?>
        <tr>
          <td><strong><?= Security::e($s['title']) ?></strong><br><span class="text-muted" style="font-size:12px"><?= Security::e(mb_strimwidth($s['description'] ?? '', 0, 80, '...')) ?></span></td>
          <td><span class="admin-table__slug"><?= Security::e($s['slug']) ?></span></td>
          <td><?= (int)($s['order_index'] ?? 0) ?></td>
          <td>
            <?php if (($s['is_visible'] ?? 1)==1): ?><span class="badge badge--success">Visible</span><?php else: ?><span class="badge badge--muted">Hidden</span><?php endif; ?>
          </td>
          <td>
            <div class="admin-table__actions">
              <a href="/admin/services/<?= (int)$s['id'] ?>/edit" class="btn btn--ghost btn--sm">Edit</a>
              <form method="POST" action="/admin/services/<?= (int)$s['id'] ?>/delete" data-confirm="Delete service '<?= Security::escapeAttr($s['title']) ?>'? This cannot be undone." style="display:inline">
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
