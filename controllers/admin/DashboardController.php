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
        $totalRevenue = $this->bookingModel->sumAllRevenue();
        $recentBookings = $this->bookingModel->getRecentForAdmin(5);

        $totalCategories = $this->categoryModel->countAll();

        render('admin/dashboard/index', [
    'pageTitle' => 'Admin Dashboard - ' . APP_NAME,
    'navSection' => 'admin',
    'totalUsers' => $totalUsers,
    'inactiveUsers' => $inactiveUsers,
    'recentUsers' => $recentUsers,
    'totalSalons' => $totalSalons,
    'pendingSalons' => $pendingSalons,
    'recentSalons' => $recentSalons,
    'totalBookings' => $totalBookings,
    'todayBookings' => $todayBookings,
    'totalRevenue' => $totalRevenue,
    'recentBookings' => $recentBookings,
    'totalCategories' => $totalCategories,
]);
    }
}