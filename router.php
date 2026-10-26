<?php
// Dev-server router for `php -S`: keeps data/ private and
// returns a real 404 for missing files instead of running index.php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (str_starts_with($path, '/data')) {
    http_response_code(403);
    exit('Forbidden');
}
if ($path !== '/' && !is_file(__DIR__ . $path)) {
    http_response_code(404);
    exit('Not found');
}
return false;
