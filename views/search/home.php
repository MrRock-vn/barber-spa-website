<section class="hero-section d-flex align-items-center text-white">
    <div class="container">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="hero-badge mb-3">BARBER SPA BOOKING PLATFORM</span>
                <h1 class="hero-title mb-3">
                    Đặt lịch cắt tóc, gội đầu và chăm sóc diện mạo thật nhanh
                </h1>
                <p class="hero-subtitle mb-4">
                    Tìm salon phù hợp, chọn dịch vụ yêu thích và đặt lịch online chỉ trong vài bước đơn giản.
                </p>

                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-primary hero-btn-primary">
                        Khám phá salon
                    </a>
                    <a href="<?= e(BASE_URL . '/my-bookings') ?>" class="btn btn-outline-light hero-btn-secondary">
                        Lịch hẹn của tôi
                    </a>
                </div>

                <div class="row g-3 hero-stats">
                    <div class="col-6 col-md-4">
                        <div class="hero-stat-card">
                            <h4>Salon đáng tin cậy</h4>
                            <p>Đội ngũ chuyên nghiệp</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hero-stat-card">
                            <h4>Dịch vụ đa dạng</h4>
                            <p>Phù hợp nhiều nhu cầu</p>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="hero-stat-card">
                            <h4>Đặt lịch dễ dàng</h4>
                            <p>Quy trình nhanh gọn</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="hero-image-card">
                    <img
                        loading="lazy"
                        src="https://images.unsplash.com/photo-1621605815971-fbc98d665033?auto=format&fit=crop&w=1200&q=80"
                        alt="Barber Spa"
                        class="hero-main-image"
                    >
                </div>
            </div>
        </div>
    </div>
</section>

<section class="search-section">
    <div class="container">
        <div class="search-card">
            <div class="row align-items-center g-3">
                <div class="col-lg-4">
                    <h2 class="mb-0">Tìm salon phù hợp với bạn</h2>
                </div>
                <div class="col-lg-8">
                    <form action="<?= e(BASE_URL . '/search') ?>" method="GET" class="row g-2">
                        <div class="col-md-9">
                            <input
                                type="text"
                                name="q"
                                class="form-control form-control-lg search-input"
                                placeholder="Nhập tên salon, khu vực hoặc dịch vụ..."
                                maxlength="100"
                                autocomplete="off"
                                value="<?= e($_GET['q'] ?? '') ?>"
                            >
                        </div>
                        <div class="col-md-3 d-grid">
                            <button type="submit" class="btn btn-primary btn-lg search-btn">
                                Tìm kiếm
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($categories)): ?>
<section class="categories-section py-5">
    <div class="container">
        <div class="section-heading text-center mb-4">
            <span class="section-badge">DANH MỤC DỊCH VỤ</span>
            <h2 class="section-title">Lựa chọn dịch vụ yêu thích</h2>
            <p class="section-subtitle">
                Khám phá các nhóm dịch vụ nổi bật để bắt đầu trải nghiệm của bạn.
            </p>
        </div>

        <div class="row g-3">
            <?php foreach ($categories as $category): ?>
                <div class="col-6 col-md-4 col-lg-3">
                    <a href="<?= e(BASE_URL . '/search?q=' . urlencode((string) $category['name'])) ?>" class="category-pill d-flex align-items-center justify-content-center text-center text-decoration-none">
                        <?= e($category['name']) ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<section class="categories-section py-5">
    <div class="container">
        <div class="section-heading text-center mb-4">
            <span class="section-badge">DANH MỤC DỊCH VỤ</span>
            <h2 class="section-title">Danh mục đang cập nhật</h2>
            <p class="section-subtitle">
                Vui lòng sử dụng thanh tìm kiếm để tìm salon và dịch vụ phù hợp.
            </p>
        </div>
        <div class="text-center text-muted py-5">
            Chưa có danh mục dịch vụ khả dụng. Dùng tìm kiếm để tiếp tục.
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredSalons)): ?>
<section class="featured-salons-section py-5">
    <div class="container">
        <div class="section-heading text-center mb-5">
            <span class="section-badge">SALON NỔI BẬT</span>
            <h2 class="section-title">Những địa điểm được khách hàng yêu thích</h2>
            <p class="section-subtitle">
                Chọn salon chất lượng cao với đội ngũ chuyên nghiệp và nhiều đánh giá tích cực.
            </p>
        </div>

        <div class="row g-4">
            <?php foreach ($featuredSalons as $salon): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card salon-card shadow-sm border-0 h-100">
                        <div class="salon-image-wrap">
                            <img
                                loading="lazy"
                                src="<?= e($salon['image_url'] ?? 'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&fit=crop&w=1200&q=80') ?>"
                                alt="<?= e($salon['name']) ?>"
                                class="salon-image"
                            >
                            <span class="salon-badge">Nổi bật</span>
                        </div>

                        <div class="card-body d-flex flex-column">
                            <h5 class="salon-title mb-2"><?= e($salon['name']) ?></h5>
                            <p class="salon-address text-muted mb-3">
                                <?= e($salon['address'] ?? 'Địa chỉ đang cập nhật') ?>
                            </p>

                            <div class="d-flex justify-content-between align-items-center mt-auto">
                                <?php if (!empty($salon['rating'])): ?>
                                    <span class="rating-pill">
                                        ⭐ <?= e((string) $salon['rating']) ?>
                                    </span>
                                <?php endif; ?>
                                <a href="<?= e(BASE_URL . '/salon/' . $salon['id']) ?>" class="btn btn-primary salon-btn">
                                    Xem chi tiết
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php else: ?>
<section class="featured-salons-section py-5">
    <div class="container">
        <div class="section-heading text-center mb-5">
            <span class="section-badge">SALON NỔI BẬT</span>
            <h2 class="section-title">Những địa điểm được khách hàng yêu thích</h2>
            <p class="section-subtitle">
                Chọn salon chất lượng cao với đội ngũ chuyên nghiệp và nhiều đánh giá tích cực.
            </p>
        </div>
        <div class="text-center text-muted py-5">
            Chưa có salon nổi bật để hiển thị. Vui lòng thử lại sau hoặc tìm kiếm trực tiếp.
        </div>
    </div>
</section>
<?php endif; ?>

<section class="booking-steps-section py-5">
    <div class="container">
        <div class="section-heading text-center mb-5">
            <span class="section-badge">QUY TRÌNH ĐẶT LỊCH</span>
            <h2 class="section-title">Đặt lịch chỉ với 3 bước</h2>
            <p class="section-subtitle">
                Hệ thống giúp bạn hoàn tất booking nhanh chóng, thuận tiện và dễ sử dụng.
            </p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">01</div>
                    <h5>Tìm salon</h5>
                    <p>Nhập từ khóa để tìm salon hoặc khu vực phù hợp với bạn.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">02</div>
                    <h5>Chọn dịch vụ</h5>
                    <p>Chọn danh mục hoặc xem chi tiết salon để tìm dịch vụ mong muốn.</p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-card">
                    <div class="step-number">03</div>
                    <h5>Đặt lịch nhanh</h5>
                    <p>Tiếp tục quy trình booking với các chức năng cũ của hệ thống.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="promo-section py-5">
    <div class="container">
        <div id="promoCarousel" class="carousel slide promo-carousel" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="0" class="active" aria-current="true"></button>
                <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="1"></button>
                <button type="button" data-bs-target="#promoCarousel" data-bs-slide-to="2"></button>
            </div>

            <div class="carousel-inner">
                <div class="carousel-item active">
                    <div class="promo-slide">
                        <img
                            loading="lazy"
                            src="https://images.unsplash.com/photo-1622286342621-4bd786c2447c?auto=format&fit=crop&w=1600&q=80"
                            alt="Promo 1"
                            class="promo-slide-image"
                        >
                        <div class="promo-overlay">
                            <div class="promo-content">
                                <span class="promo-tag">ƯU ĐÃI HOT</span>
                                <h2>Sẵn sàng làm mới diện mạo của bạn?</h2>
                                <p>Tìm salon phù hợp và bắt đầu đặt lịch ngay hôm nay với trải nghiệm hiện đại và chuyên nghiệp.</p>
                                <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-light btn-lg">
                                    Bắt đầu ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="promo-slide">
                        <img
                            loading="lazy"
                            src="https://images.unsplash.com/photo-1517832606299-7ae9b720a186?auto=format&fit=crop&w=1600&q=80"
                            alt="Promo 2"
                            class="promo-slide-image"
                        >
                        <div class="promo-overlay">
                            <div class="promo-content">
                                <span class="promo-tag">XU HƯỚNG MỚI</span>
                                <h2>Phong cách barber hiện đại, lịch lãm và tự tin</h2>
                                <p>Khám phá không gian dịch vụ đẳng cấp và đội ngũ barber tận tâm tại Barber Spa.</p>
                                <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-light btn-lg">
                                    Khám phá salon
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="carousel-item">
                    <div class="promo-slide">
                        <img
                            loading="lazy"
                            src="https://images.unsplash.com/photo-1503951914875-452162b0f3f1?auto=format&fit=crop&w=1600&q=80"
                            alt="Promo 3"
                            class="promo-slide-image"
                        >
                        <div class="promo-overlay">
                            <div class="promo-content">
                                <span class="promo-tag">TRẢI NGHIỆM CAO CẤP</span>
                                <h2>Thư giãn - làm đẹp - nâng tầm phong cách</h2>
                                <p>Không chỉ là cắt tóc, đây là trải nghiệm chăm sóc toàn diện dành cho bạn.</p>
                                <a href="<?= e(BASE_URL . '/search') ?>" class="btn btn-light btn-lg">
                                    Xem ngay
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <button class="carousel-control-prev" type="button" data-bs-target="#promoCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#promoCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    </div>
</section>

<section class="angels-showcase py-5">
    <div class="container">
        <div class="angels-heading">
            <div class="angels-line"></div>
            <div>
                <h2>BARBER SPA ANGELS</h2>
                <p>Đội ngũ chuyên gia Barber Spa, tận tâm phục vụ phái mạnh.</p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= e(BASE_URL . '/search') ?>" class="angel-item">
                    <div class="angel-thumb-wrap">
                        <img loading="lazy" src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=900&q=80" alt="Angel 1" class="angel-thumb">
                        <div class="angel-ribbon">Senior Stylist</div>
                    </div>
                    <div class="angel-meta">
                        <div class="angel-channel">Chuyên gia cắt tóc</div>
                        <div class="angel-address">Phong cách hiện đại, tinh tế</div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= e(BASE_URL . '/search') ?>" class="angel-item">
                    <div class="angel-thumb-wrap">
                        <img loading="lazy" src="https://images.unsplash.com/photo-1488426862026-3ee34a7d66df?auto=format&fit=crop&w=900&q=80" alt="Angel 2" class="angel-thumb">
                        <div class="angel-ribbon">Master Barber</div>
                    </div>
                    <div class="angel-meta">
                        <div class="angel-channel">Tạo kiểu nam chuẩn salon</div>
                        <div class="angel-address">Phong cách lịch lãm, nam tính</div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= e(BASE_URL . '/search') ?>" class="angel-item">
                    <div class="angel-thumb-wrap">
                        <img loading="lazy" src="https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=900&q=80" alt="Angel 3" class="angel-thumb">
                        <div class="angel-ribbon">Style Advisor</div>
                    </div>
                    <div class="angel-meta">
                        <div class="angel-channel">Tư vấn phong cách cá nhân</div>
                        <div class="angel-address">Đầu tư phong cách chuẩn chuyên nghiệp</div>
                    </div>
                </a>
            </div>

            <div class="col-6 col-md-4 col-lg-3">
                <a href="<?= e(BASE_URL . '/search') ?>" class="angel-item">
                    <div class="angel-thumb-wrap">
                        <img loading="lazy" src="https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=900&q=80" alt="Angel 4" class="angel-thumb">
                        <div class="angel-ribbon">Grooming Expert</div>
                    </div>
                    <div class="angel-meta">
                        <div class="angel-channel">Chăm sóc tóc và da đầu</div>
                        <div class="angel-address">Phong cách barber chuyên sâu</div>
                    </div>
                </a>
            </div>
        </div>
    </div>
</section>

<footer class="shine-footer">
    <div class="container">
        <div class="shine-footer-top row g-4">
            <div class="col-md-6">
                <div class="shine-logo-box">
                    <div class="shine-logo-text">BARBER SPA</div>
                    <div class="shine-logo-desc">Logo hiện tại ở hệ thống Barber Spa</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="shine-logo-box">
                    <div class="shine-logo-text">BARBER SPA PRO</div>
                    <div class="shine-logo-desc">Phiên bản thương hiệu hiện đại mới</div>
                </div>
            </div>
        </div>

        <div class="row g-4 shine-footer-main">
            <div class="col-md-3">
                <h5>Về chúng tôi</h5>
                <ul>
                    <li><a href="<?= e(BASE_URL . '/home') ?>">Về chúng tôi</a></li>
                    <li><a href="<?= e(BASE_URL . '/search') ?>">Tìm salon gần nhất</a></li>
                    <li><a href="<?= e(BASE_URL . '/my-bookings') ?>">Lịch hẹn của tôi</a></li>
                </ul>
            </div>

            <div class="col-md-3">
                <h5>Liên hệ</h5>
                <ul>
                    <li>Hotline: 1900.27.27.03</li>
                    <li>Liên hệ học nghề: 0967.86.3030</li>
                    <li>Liên hệ quảng cáo</li>
                </ul>
            </div>

            <div class="col-md-3">
                <h5>Chính sách</h5>
                <ul>
                    <li>Giờ phục vụ: 8h30 - 20h30</li>
                    <li>Chính sách bảo mật</li>
                    <li>Điều kiện giao dịch</li>
                </ul>
            </div>

            <div class="col-md-3">
                <h5>Thanh toán</h5>
                <div class="payment-icons">
                    <span>💵</span>
                    <span>🏦</span>
                    <span>💳</span>
                    <span>VISA</span>
                    <span>MC</span>
                </div>
            </div>
        </div>
    </div>

    <div class="shine-footer-bottom">
        © 2026 Barber Spa / Địa chỉ: 148B Trường Định, Hà Nội / Hệ thống đặt lịch barber spa hiện đại
    </div>
</footer>