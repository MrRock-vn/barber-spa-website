<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Booking.php';

class DashboardController
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
            echo '<h1>Owner Dashboard</h1>';
            echo '<p>Bạn chưa có salon nào.</p>';
            return;
        }

        $salonId = (int) $salon['id'];

        $totalBookings = $this->bookingModel->countBySalonId($salonId);
        $todayBookings = $this->bookingModel->countTodayBySalonId($salonId);
        $upcomingBookings = $this->bookingModel->countUpcomingBySalonId($salonId);
        $completedBookings = $this->bookingModel->countCompletedBySalonId($salonId);
        $revenue = $this->bookingModel->sumRevenueBySalonId($salonId);
        $recentBookings = $this->bookingModel->getRecentBySalonId($salonId, 5);

        render('owner/dashboard/index', [
    'pageTitle' => 'Owner Dashboard - ' . APP_NAME,
    'navSection' => 'owner',
    'salon' => $salon,
    'totalBookings' => $totalBookings,
    'todayBookings' => $todayBookings,
    'upcomingBookings' => $upcomingBookings,
    'completedBookings' => $completedBookings,
    'revenue' => $revenue,
    'recentBookings' => $recentBookings,
]);
    }
}