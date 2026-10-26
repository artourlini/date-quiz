<?php
require __DIR__ . '/config.php';

if (empty($_SESSION['said_yes'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $day = DateTimeImmutable::createFromFormat('!Y-m-d', $_POST['day'] ?? '');
    $time = $_POST['time'] ?? '';
    $activity = $_POST['activity'] ?? '';
    $custom = trim(mb_substr($_POST['custom'] ?? '', 0, 300));

    $valid = $day
        && $day >= new DateTimeImmutable('tomorrow')
        && $day <= new DateTimeImmutable(LAST_DAY)
        && array_key_exists($time, times_for($day))
        && array_key_exists($activity, ACTIVITIES);

    if (!$valid) {
        $_SESSION['error'] = 'Pick a day, a time and an activity first!';
        header('Location: pick-a-time.php');
        exit;
    }

    $booking = [
        'submitted_at' => date('c'),
        'date'         => $day->format('Y-m-d'),
        'time'         => $time,
        'activity'     => $activity,
        'custom'       => $activity === 'custom' ? $custom : '',
        'no_clicks'    => $_SESSION['no_count'] ?? 0,
    ];
    save_response($booking);

    [$emoji, $actName] = ACTIVITIES[$activity];
    $when = new DateTimeImmutable($booking['date'] . ' ' . $time);
    notify("It's a date!",
        $when->format('l, F j') . ' at ' . $when->format('g:i A') . "\n"
        . $emoji . ' ' . ($booking['custom'] ?: $actName), 'calendar,purple_heart');
    $_SESSION['booking'] = $booking;

    // Post/Redirect/Get so refreshing doesn't save twice
    header('Location: done.php');
    exit;
}

$b = $_SESSION['booking'] ?? null;
if (!$b) {
    header('Location: pick-a-time.php');
    exit;
}

[$emoji, $actName] = ACTIVITIES[$b['activity']];
$when = new DateTimeImmutable($b['date'] . ' ' . $b['time']);

page_head("It's a date!");
render_collage([
    ['photo7.jpg',  'night out ✨'],
    ['photo8.jpg',  'lazy mornings'],
    ['photo9.jpg',  'pout check'],
    ['photo10.jpg', 'pretty in pink'],
    ['photo11.jpg', 'car selfie 😛'],
    ['photo12.jpg', 'that smile'],
]);
?>
<main class="done">
  <div class="big-emoji">💌</div>
  <p class="eyebrow">Confirmed</p>
  <h1>It's a <em>date</em>!</h1>
  <p class="sub">
    <?php if ($b['no_clicks'] > 0): ?>
      (It only took you <?= (int)$b['no_clicks'] ?> "no" <?= $b['no_clicks'] == 1 ? 'click' : 'clicks' ?> to get here.)
    <?php else: ?>
      No hesitation. I respect that.
    <?php endif; ?>
  </p>

  <div class="ticket">
    <div>📅 <b><?= $when->format('l, F j') ?></b></div>
    <div>⏰ <b><?= $when->format('g:i A') ?></b></div>
    <div><?= $emoji ?> <b><?= h($b['activity'] === 'custom' && $b['custom'] ? $b['custom'] : $actName) ?></b></div>
  </div>

  <p class="sub" style="margin-top:28px">Screenshot this and send it to me. No take-backs. 😌</p>
</main>
<?php page_foot();
