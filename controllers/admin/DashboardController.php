<?php

declare(strict_types=1);

require_once __DIR__ . '/../../models/User.php';
require_once __DIR__ . '/../../models/Salon.php';
require_once __DIR__ . '/../../models/Booking.php';
require_once __DIR__ . '/../../models/Category.php';

class DashboardController
{
    private User $userModel;
    private Salon $salonModel;
    private Booking $bookingModel;
    private Category $categoryModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->salonModel = new Salon();
        $this->bookingModel = new Booking();
        $this->categoryModel = new Category();
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

        $activeSalons = $this->salonModel->countSalonsByStatus('active');
        $totalCategories = $this->categoryModel->countAll();

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
        ]);
    }
}