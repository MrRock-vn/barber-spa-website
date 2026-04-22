<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Review.php';
require_once __DIR__ . '/../../models/Service.php';
require_once __DIR__ . '/../../models/Staff.php';

class DashboardController
{
    private Salon $salonModel;
    private Booking $bookingModel;
    private Review $reviewModel;
    private Service $serviceModel;
    private Staff $staffModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->bookingModel = new Booking();
        $this->reviewModel = new Review();
        $this->serviceModel = new Service();
        $this->staffModel = new Staff();
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
        $staffCount = $this->staffModel->countBySalonId($salonId);
        $serviceCount = $this->serviceModel->countBySalonId($salonId);
        $reviewCount = $this->reviewModel->countBySalonId($salonId);
        $recentReviews = $this->reviewModel->getRecentBySalonId($salonId, 5);
        $bookingChart = $this->bookingModel->getBookingCountsByLastDays(7, $salonId);
        $revenueChart = $this->bookingModel->getRevenueByLastMonths(6, $salonId);
        $topStaff = $this->bookingModel->getTopStaffBySalonId($salonId, 5);
        $topServices = $this->bookingModel->getTopServicesFromBookings($salonId, 5);
        $busyHours = $this->bookingModel->getBusyHoursBySalonId($salonId, 5);

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
    'staffCount' => $staffCount,
    'serviceCount' => $serviceCount,
    'reviewCount' => $reviewCount,
    'recentReviews' => $recentReviews,
    'bookingChart' => $bookingChart,
    'revenueChart' => $revenueChart,
    'topStaff' => $topStaff,
    'topServices' => $topServices,
    'busyHours' => $busyHours,
]);
    }
}
