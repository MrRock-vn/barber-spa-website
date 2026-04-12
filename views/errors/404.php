<?php

declare(strict_types=1);
?>

<div class="container" style="max-width: 720px;">
    <div class="card">
        <div class="card-body p-5 text-center">
            <h1 class="display-4 fw-bold mb-3">404</h1>
            <h3 class="mb-3">Không tìm thấy trang</h3>
            <p class="text-muted mb-4">
                Trang bạn đang truy cập không tồn tại hoặc đã được di chuyển.
            </p>
            <a href="<?= e(BASE_URL . '/home') ?>" class="btn btn-dark">Quay về trang chủ</a>
        </div>
    </div>
</div>