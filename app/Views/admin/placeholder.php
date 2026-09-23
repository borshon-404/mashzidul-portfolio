<?php
/**
 * Placeholder for unfinished admin sections
 * Milestone 4.1 — Shows "Coming in the next milestone"
 */

use App\Core\Security;

$section = $section ?? 'unknown';
$title = $title ?? ucfirst($section);
?>
<div class="admin-placeholder">
    <div class="admin-card" style="text-align:center;padding:60px 20px">
        <h2><?= Security::e($title) ?></h2>
        <p class="lead" style="margin-top:12px">This section is coming in the next milestone.</p>
        <p class="muted">Milestone 4.1 only includes authentication + dashboard shell. CRUD editors for <?= Security::e($section) ?> will be built in M4.2+</p>
        <div style="margin-top:24px">
            <span class="badge">M4.2 — <?= Security::e($title) ?> CRUD — Coming Next</span>
        </div>
        <p style="margin-top:30px"><a href="/admin/dashboard" class="btn btn--ghost">← Back to Dashboard</a></p>
    </div>
</div>
