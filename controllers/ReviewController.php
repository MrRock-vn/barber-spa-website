<?php

declare(strict_types=1);

class ReviewController
{
    public function create(): void
    {
        echo '<h1>Create Review</h1>';
    }

    public function edit(int $id): void
    {
        echo '<h1>Edit Review #' . (int) $id . '</h1>';
    }
}