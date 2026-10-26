<?php
require __DIR__ . '/config.php';

if (isset($_GET['logout'])) {
    unset($_SESSION['admin']);
    header('Location: responses.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    if (hash_equals(ADMIN_PASSWORD, $_POST['password'] ?? '')) {
        session_regenerate_id(true);
        $_SESSION['admin'] = true;
        header('Location: responses.php');
        exit;
    }
    $error = 'Wrong password.';
}

page_head('Responses');
?>
<main>
  <p class="eyebrow">Behind the scenes</p>
  <h1>Who said <em>yes</em>?</h1>

  <?php if (empty($_SESSION['admin'])): ?>
    <form method="post" style="max-width:320px;margin:0 auto;text-align:center">
      <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
      <input type="password" name="password" placeholder="Password" autofocus>
      <?php if ($error): ?><p class="plea"><?= h($error) ?></p><?php endif; ?>
      <div class="submit-row" style="margin-top:16px"><button class="btn btn-yes">Let me in</button></div>
    </form>
  <?php else: $rows = array_reverse(load_responses()); ?>
    <p class="sub"><?= count($rows) ?> response<?= count($rows) === 1 ? '' : 's' ?> · <a href="?logout=1" class="back">log out</a></p>
    <?php if ($rows): ?>
      <div class="table-wrap">
        <table>
          <tr><th>When</th><th>Activity</th><th>"No" clicks</th><th>Submitted</th></tr>
          <?php foreach ($rows as $r):
              $act = ACTIVITIES[$r['activity']][1] ?? $r['activity'];
              if ($r['custom']) $act .= ': ' . $r['custom'];
              $when = new DateTimeImmutable($r['date'] . ' ' . $r['time']); ?>
            <tr>
              <td><?= $when->format('D M j, g:i A') ?></td>
              <td><?= h($act) ?></td>
              <td><?= (int)$r['no_clicks'] ?></td>
              <td><?= h(date('M j, g:i A', strtotime($r['submitted_at']))) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</main>
<?php page_foot();
