<?php
/**
 * Variables provided by AdminController::contacts().
 *
 * @var array<int, array<string, mixed>> $contacts Contact rows, newest first
 * @var string|null                      $error    Flash error message (raw HTML)
 * @var string|null                      $success  Flash success message
 */
require_once __DIR__ . '/../../layouts/header.php';
?>

<main class="admin-wrap">

  <div class="section-header" style="text-align:left;margin-bottom:1.5rem;">
    <h2>Contact Requests</h2>
    <p>Messages submitted through the contact form — newest first. Showing the most recent <?= count($contacts) ?>.</p>
  </div>

  <?php if (!empty($error)): ?>
    <div class="alert alert-error" style="margin-bottom:1rem;"><div><?= $error ?></div></div>
  <?php endif; ?>
  <?php if (!empty($success)): ?>
    <div class="alert alert-success" style="margin-bottom:1rem;"><span><?= e($success) ?></span></div>
  <?php endif; ?>

  <?php if (empty($contacts)): ?>
    <div class="admin-empty">No contact requests yet.</div>
  <?php else: ?>
    <div class="admin-table-wrap">
      <table class="admin-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>IP</th>
            <th>Attachment</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($contacts as $c): ?>
            <tr>
              <td class="nowrap"><?= e(date('M j, Y H:i', strtotime($c['created_at']))) ?></td>
              <td class="nowrap"><?= e($c['name']) ?></td>
              <td class="nowrap email"><a href="mailto:<?= e($c['email']) ?>"><?= e($c['email']) ?></a></td>
              <td class="msg"><?= nl2br(e($c['message'])) ?></td>
              <td class="nowrap"><?= e($c['ip_address'] ?? '—') ?></td>
              <td class="nowrap">
                <?php if (!empty($c['attachment_key'])): ?>
                  <a class="dl-link" href="<?= url('admin/contacts/' . (int) $c['id'] . '/attachment') ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    PDF<?php if (!empty($c['attachment_size'])): ?> · <?= e(number_format($c['attachment_size'] / 1024)) ?> KB<?php endif; ?>
                  </a>
                <?php else: ?>
                  <span class="muted">—</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>

</main>

<?php require_once __DIR__ . '/../../layouts/footer.php'; ?>
