<?php

declare(strict_types=1);

$flashTypes = [
    'success' => 'success',
    'error' => 'danger',
    'warning' => 'warning',
    'info' => 'info',
];

foreach ($flashTypes as $key => $bootstrapClass):
    if (!hasFlash($key)) {
        continue;
    }
?>
    <div class="alert alert-<?= e($bootstrapClass) ?> app-flash">
        <?= e(getFlash($key)) ?>
    </div>
<?php endforeach; ?>