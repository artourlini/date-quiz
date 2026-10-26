<?php
require __DIR__ . '/config.php';

// Arriving from the Yes button
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['answer'] ?? '') === 'yes') {
    csrf_check();
    $_SESSION['said_yes'] = true;
    $_SESSION['no_count'] = max(0, (int)($_POST['no_count'] ?? 0));
    $n = $_SESSION['no_count'];
    notify('She said YES', $n ? "After clicking No $n time" . ($n == 1 ? '' : 's') . '.' : 'No hesitation at all.', 'tada,purple_heart');
    header('Location: pick-a-time.php');
    exit;
}

// No skipping the big question
if (empty($_SESSION['said_yes'])) {
    header('Location: index.php');
    exit;
}

$days = [];
$last = new DateTimeImmutable(LAST_DAY);
for ($d = new DateTimeImmutable('tomorrow'); $d <= $last; $d = $d->modify('+1 day')) $days[] = $d;

$error = $_SESSION['error'] ?? null;
unset($_SESSION['error']);

page_head('Pick a time — our date');
?>
<main>
  <a href="index.php" class="back">← back</a>
  <p class="eyebrow">Step 2 of 2</p>
  <h1>Pick a <em>time</em>.</h1>
  <p class="sub">You said yes (legally binding). Now the fun part — when are we doing this?</p>

  <?php if ($error): ?><p class="plea"><?= h($error) ?></p><?php endif; ?>

  <form method="post" action="done.php" id="pick">
    <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">

    <p class="label">Day</p>
    <div class="days">
      <?php foreach ($days as $i => $d): ?>
        <label class="choice day">
          <input type="radio" name="day" value="<?= $d->format('Y-m-d') ?>"
                 data-weekend="<?= is_weekend($d) ? 1 : 0 ?>" <?= $i === 0 ? 'checked' : '' ?>>
          <span>
            <small><?= $d->format('D') ?></small>
            <b><?= $d->format('j') ?></b>
            <small><?= $d->format('M') ?></small>
          </span>
        </label>
      <?php endforeach; ?>
    </div>

    <p class="label" id="timeLabel">Time</p>
    <?php foreach (['weeknight' => WEEKNIGHT_TIMES, 'weekend' => WEEKEND_TIMES] as $group => $times): ?>
      <div class="grid time-group" data-group="<?= $group ?>">
        <?php foreach ($times as $hm => $quip): ?>
          <label class="choice time">
            <input type="radio" name="time" value="<?= $hm ?>">
            <span><b><?= fmt_time($hm) ?></b><small><?= h($quip) ?></small></span>
          </label>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>

    <p class="label">What are we doing?</p>
    <div class="acts">
      <?php foreach (ACTIVITIES as $key => [$emoji, $name]): ?>
        <label class="choice act">
          <input type="radio" name="activity" value="<?= $key ?>">
          <span><?= $emoji ?> <?= h($name) ?></span>
        </label>
      <?php endforeach; ?>
    </div>
    <div id="customWrap">
      <textarea name="custom" rows="2" maxlength="300" placeholder="Surprise me… what's the plan?"></textarea>
    </div>

    <div class="submit-row">
      <button type="submit" class="btn btn-yes" id="go" disabled>Pick a time to continue</button>
    </div>
  </form>
</main>

<script>
  const form = document.getElementById('pick');
  const go = document.getElementById('go');
  const label = document.getElementById('timeLabel');
  const customWrap = document.getElementById('customWrap');

  function refresh() {
    const day = form.querySelector('input[name=day]:checked');
    const weekend = day && day.dataset.weekend === '1';
    const group = weekend ? 'weekend' : 'weeknight';
    label.textContent = weekend ? 'Time · Weekend menu 🥂' : 'Time · Weeknight menu 🍝';

    document.querySelectorAll('.time-group').forEach(g => {
      const active = g.dataset.group === group;
      g.classList.toggle('active', active);
      // Hidden slots can't be submitted
      g.querySelectorAll('input').forEach(i => { i.disabled = !active; if (!active) i.checked = false; });
    });

    const time = form.querySelector('input[name=time]:checked');
    const act = form.querySelector('input[name=activity]:checked');
    customWrap.classList.toggle('show', act && act.value === 'custom');

    go.disabled = !(day && time && act);
    go.textContent = !time ? 'Pick a time to continue'
                   : !act ? 'Pick an activity to continue'
                   : "It's a date 💌";
  }

  form.addEventListener('change', refresh);
  refresh();
</script>
<?php page_foot();
