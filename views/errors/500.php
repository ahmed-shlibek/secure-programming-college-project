<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>500 — Something went wrong</title>
  <link rel="stylesheet" href="<?= url('css/style.css') ?>">
</head>
<body>

<div class="error-page">
  <div class="error-content">
    <div class="error-code">500</div>
    <h1 class="error-title">Something went wrong</h1>
    <p class="error-text">
      An unexpected error occurred on our end. The issue has been logged and
      we're looking into it. Please try again in a moment.
    </p>
    <a href="<?= url('login') ?>" class="btn btn-primary" style="display:inline-flex;">
      <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
      Go Home
    </a>
  </div>
</div>

</body>
</html>
