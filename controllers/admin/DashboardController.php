<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Category.php';
require_once __DIR__ . '/../../models/Payment.php';
require_once __DIR__ . '/../../models/Review.php';

class DashboardController
{
    private User $userModel;
    private Salon $salonModel;
    private Booking $bookingModel;
    private Category $categoryModel;
    private Payment $paymentModel;
    private Review $reviewModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->salonModel = new Salon();
        $this->bookingModel = new Booking();
        $this->categoryModel = new Category();
        $this->paymentModel = new Payment();
        $this->reviewModel = new Review();
    }

    public function index(): void
    {
        Auth::requireRole(['admin']);

        $totalUsers = $this->userModel->countAllUsers();
        $inactiveUsers = $this->userModel->countInactiveUsers();
        $recentUsers = $this->userModel->getRecentUsers(5);

        $totalSalons = $this->salonModel->countAllSalons();
        $pendingSalons = $this->salonModel->countPendingSalons();
        $recentSalons = $this->salonModel->getRecentForAdmin(5);

        $totalBookings = $this->bookingModel->countAllBookings();
        $todayBookings = $this->bookingModel->countTodayBookings();
        $completedBookings = $this->bookingModel->countBookingsByStatus('completed');
        $cancelledBookings = $this->bookingModel->countBookingsByStatus('cancelled');
        $pendingBookings = $this->bookingModel->countBookingsByStatus('pending');
        $confirmedBookings = $this->bookingModel->countBookingsByStatus('confirmed');
        $totalRevenue = $this->bookingModel->sumAllRevenue();
        $recentBookings = $this->bookingModel->getRecentForAdmin(5);
        $bookingChart = $this->bookingModel->getBookingCountsByLastDays(7);
        $revenueChart = $this->bookingModel->getRevenueByLastMonths(6);
        $topSalons = $this->bookingModel->getTopSalonsByBookings(5);
        $topServices = $this->bookingModel->getTopServicesFromBookings(null, 5);

        $activeSalons = $this->salonModel->countSalonsByStatus('active');
        $totalCategories = $this->categoryModel->countAll();
        $totalReviews = $this->reviewModel->countAllReviews();
        $flaggedReviews = $this->reviewModel->countFlagged();
        $recentReviews = $this->reviewModel->getRecentForAdmin(5);
        $totalPayments = $this->paymentModel->countAllPayments();
        $successfulPayments = $this->paymentModel->countByStatus('success');
        $paymentStatusCounts = $this->paymentModel->getStatusCounts();
        $recentPayments = $this->paymentModel->getRecentForAdmin(5);

        render('admin/dashboard/index', [
            'pageTitle' => 'Admin Dashboard - ' . APP_NAME,
            'navSection' => 'admin',
            'totalUsers' => $totalUsers,
            'inactiveUsers' => $inactiveUsers,
            'recentUsers' => $recentUsers,
            'totalSalons' => $totalSalons,
            'activeSalons' => $activeSalons,
            'pendingSalons' => $pendingSalons,
            'recentSalons' => $recentSalons,
            'totalBookings' => $totalBookings,
            'todayBookings' => $todayBookings,
            'completedBookings' => $completedBookings,
            'cancelledBookings' => $cancelledBookings,
            'pendingBookings' => $pendingBookings,
            'confirmedBookings' => $confirmedBookings,
            'totalRevenue' => $totalRevenue,
            'recentBookings' => $recentBookings,
            'totalCategories' => $totalCategories,
            'totalReviews' => $totalReviews,
            'flaggedReviews' => $flaggedReviews,
            'totalPayments' => $totalPayments,
            'successfulPayments' => $successfulPayments,
            'paymentStatusCounts' => $paymentStatusCounts,
            'recentPayments' => $recentPayments,
            'recentReviews' => $recentReviews,
            'bookingChart' => $bookingChart,
            'revenueChart' => $revenueChart,
            'topSalons' => $topSalons,
            'topServices' => $topServices,
        ]);
    }
}
