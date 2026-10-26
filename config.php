<?php
// ---- Edit these to make it yours ----
const ASKER_NAME     = 'me';            // shown as "Will you go on a date with me?"
const ADMIN_PASSWORD = 'xs8nm-ecnah-7v7h2-3xmv5';     // password for responses.php
const LAST_DAY       = '2026-11-30';    // last day they can pick
const DATA_FILE      = __DIR__ . '/data/responses.json';
const TIMEZONE       = 'Asia/Manila';   // your timezone (php.net/timezones)

// Push notifications: install the ntfy app and subscribe to this topic.
// Anyone who knows the topic name can read it, so keep it secret.
const NTFY_TOPIC     = 'date-imcq5tg0t9oy34vu80ce';

date_default_timezone_set(TIMEZONE);

// Time slots for weekdays vs weekends
const WEEKNIGHT_TIMES = [
    '17:00' => 'earlier the better',
    '19:00' => 'very very good choice',
    '20:00' => 'fun night out',
    '21:00' => 'antok nako dito, joke lng',
];
const WEEKEND_TIMES = [
    '10:00' => 'perfect time for some sun',
    '13:00' => 'lunch date, low stakes, high snacks',
    '18:00' => 'golden hour, main character behavior',
];
const ACTIVITIES = [
    'food'   => ['🍝', 'Food'],
    'drinks' => ['🍹', 'Drinks'],
    'coffee' => ['☕', 'Coffee'],
    'movie' => ['🍿', 'Movie'],
    'walk'   => ['🌳', 'Walk in the park'],
    'fight'  => ['🥊', 'Suntukan (no gloves, ako ang punching bag)'],
    'game'  => ['🎳', 'Bowling, arcades, etc.'],
    'custom' => ['✨', "I'll pick something super unique"],
];

// ---- Helpers ----
session_start();

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_check(): void {
    if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(400);
        exit('Session expired. Go back and try again.');
    }
}

function is_weekend(DateTimeImmutable $d): bool { return (int)$d->format('N') >= 6; }

function times_for(DateTimeImmutable $d): array { return is_weekend($d) ? WEEKEND_TIMES : WEEKNIGHT_TIMES; }

function fmt_time(string $hm): string { return date('g:i A', strtotime($hm)); }

// Send a push notification to your phone via ntfy.sh (never blocks the page for long)
function notify(string $title, string $message, string $tags = 'purple_heart'): void {
    // Skip when testing locally with `php -S`, so only the live site sends pushes
    if (NTFY_TOPIC === '' || PHP_SAPI === 'cli-server') return;
    @file_get_contents('https://ntfy.sh/' . NTFY_TOPIC, false, stream_context_create(['http' => [
        'method'  => 'POST',
        'header'  => "Title: $title\r\nTags: $tags\r\nPriority: high\r\nContent-Type: text/plain; charset=utf-8",
        'content' => $message,
        'timeout' => 4,
        'ignore_errors' => true,
    ]]));
}

function load_responses(): array {
    if (!is_file(DATA_FILE)) return [];
    return json_decode(file_get_contents(DATA_FILE), true) ?: [];
}

function save_response(array $r): void {
    if (!is_dir(dirname(DATA_FILE))) mkdir(dirname(DATA_FILE), 0775, true);
    $fp = fopen(DATA_FILE, 'c+');
    flock($fp, LOCK_EX);
    $all = json_decode(stream_get_contents($fp), true) ?: [];
    $all[] = $r;
    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    flock($fp, LOCK_UN);
    fclose($fp);
}

function page_head(string $title): void { ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= h($title) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,600;0,9..144,800;1,9..144,600;1,9..144,800&family=Inter:wght@400;500;600&family=Caveat:wght@600&display=swap" rel="stylesheet">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>💜</text></svg>">
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="blob b1"></div><div class="blob b2"></div><div class="blob b3"></div>
<?php }

function page_foot(): void { ?>
<footer>yes I made this website. Sana naman ma appreciate mo ginawa ko dito hahahaha</footer>
</body>
</html>
<?php }
