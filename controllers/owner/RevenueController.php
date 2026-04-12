<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Booking.php';

class RevenueController
{
    private Salon $salonModel;
    private Booking $bookingModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->bookingModel = new Booking();
    }

    public function index(): void
    {
        Auth::requireRole(['owner']);

        $ownerId = (int) Auth::id();
        $salon = $this->salonModel->getFirstByOwnerId($ownerId);

        if (!$salon) {
            echo '<h1>Owner Revenue</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        $dateFrom = trim($_GET['date_from'] ?? '');
        $dateTo = trim($_GET['date_to'] ?? '');

        $salonId = (int) $salon['id'];

        $completedBookings = $this->bookingModel->getCompletedBySalonIdInRange(
            $salonId,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null
        );

        $totalRevenue = $this->bookingModel->sumRevenueBySalonIdInRange(
            $salonId,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null
        );

        $completedCount = $this->bookingModel->countCompletedBySalonIdInRange(
            $salonId,
            $dateFrom !== '' ? $dateFrom : null,
            $dateTo !== '' ? $dateTo : null
        );

        $averageRevenue = $completedCount > 0 ? ($totalRevenue / $completedCount) : 0;

       render('owner/revenue/index', [
    'pageTitle' => 'Owner Revenue - ' . APP_NAME,
    'navSection' => 'owner',
    'salon' => $salon,
    'dateFrom' => $dateFrom,
    'dateTo' => $dateTo,
    'completedBookings' => $completedBookings,
    'totalRevenue' => $totalRevenue,
    'completedCount' => $completedCount,
    'averageRevenue' => $averageRevenue,
]);
    }
}