<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/Salon.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../models/Service.php';
require_once __DIR__ . '/../models/Staff.php';
require_once __DIR__ . '/../models/Review.php';

class SearchController
{
    private Salon $salonModel;
    private Category $categoryModel;
    private Service $serviceModel;
    private Staff $staffModel;
    private Review $reviewModel;

    public function __construct()
    {
        $this->salonModel = new Salon();
        $this->categoryModel = new Category();
        $this->serviceModel = new Service();
        $this->staffModel = new Staff();
        $this->reviewModel = new Review();
    }

    public function home(): void
    {
        $featuredSalons = $this->salonModel->getFeatured(6);
        $categories = $this->categoryModel->getActive(5);

        render('search/home', [
    'pageTitle' => APP_NAME . ' - Trang chủ',
    'navSection' => 'public',
    'featuredSalons' => $featuredSalons,
    'categories' => $categories,
]);
    }

    public function search(): void
    {
        $keyword = trim($_GET['keyword'] ?? '');
        $city = trim($_GET['city'] ?? '');
        $district = trim($_GET['district'] ?? '');
        $categoryId = trim($_GET['category_id'] ?? '');
        $rating = trim($_GET['rating'] ?? ($_GET['min_rating'] ?? ''));
        $minPrice = trim($_GET['min_price'] ?? ($_GET['price_from'] ?? ''));
        $maxPrice = trim($_GET['max_price'] ?? ($_GET['price_to'] ?? ''));
        $sort = trim($_GET['sort'] ?? 'rating_desc');
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 6;

        $filters = [
            'keyword' => $keyword,
            'city' => $city,
            'district' => $district,
            'category_id' => $categoryId,
            'rating' => $rating,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'sort' => $sort,
        ];

        $total = $this->salonModel->countSearch($filters);
        $pagination = paginate($total, $perPage, $page);

        $filters['limit'] = $pagination['limit'];
        $filters['offset'] = $pagination['offset'];

        $salons = $this->salonModel->search($filters);
        $categories = $this->categoryModel->getActive(20);

        render('search/search', [
    'pageTitle' => 'Kết quả tìm kiếm - ' . APP_NAME,
    'navSection' => 'public',
    'salons' => $salons,
    'categories' => $categories,
    'pagination' => $pagination,
]);
    }

   public function salonDetail($id): void
{
    $id = (int) $id;

    $salon = $this->salonModel->findActiveById($id);

    if (!$salon) {
        http_response_code(404);
        require_once __DIR__ . '/../views/errors/404.php';
        return;
    }

    $images = $this->salonModel->getImages($id);
    $services = $this->serviceModel->getActiveBySalonId($id);
    $staff = $this->staffModel->getActiveBySalonId($id);
    $reviewRating = trim($_GET['review_rating'] ?? '');
    $reviews = $this->reviewModel->getPublishedBySalonId($id, [
        'limit' => 20,
        'offset' => 0,
        'rating' => $reviewRating,
    ]);
    $ratingDistribution = $this->reviewModel->getRatingDistributionBySalonId($id);

    render('search/salon-detail', [
    'pageTitle' => $salon['name'] . ' - ' . APP_NAME,
    'navSection' => 'public',
    'salon' => $salon,
    'images' => $images,
    'services' => $services,
    'staffList' => $staff,
    'reviews' => $reviews,
    'ratingDistribution' => $ratingDistribution,
]);
}
}
