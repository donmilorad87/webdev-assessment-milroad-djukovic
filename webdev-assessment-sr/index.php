<?php
// Landing page — also proves PHP-FPM is wired up behind nginx.
// Top-level and one-level-deep .html files (GLOB_BRACE isn't available on Alpine PHP).
$files = array_merge(
    glob(__DIR__ . '/*.html') ?: [],
    glob(__DIR__ . '/*/*.html') ?: []
);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Attest — hosted files</title>
  <style>
    body { font-family: system-ui, sans-serif; max-width: 720px; margin: 3rem auto; padding: 0 1rem; }
    code { background: #f0f0f0; padding: .1rem .3rem; border-radius: 3px; }
    li { margin: .25rem 0; }
  </style>
</head>
<body>
  <h1>Hosted files</h1>
  <p>Served by nginx, PHP rendered by PHP-FPM <code><?= phpversion() ?></code>.</p>
  <ul>
    <?php foreach ($files as $f): $rel = ltrim(str_replace(__DIR__, '', $f), '/'); ?>
      <li><a href="<?= htmlspecialchars($rel) ?>"><?= htmlspecialchars($rel) ?></a></li>
    <?php endforeach; ?>
  </ul>
</body>
</html>
