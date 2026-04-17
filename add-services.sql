-- Thêm 3 dịch vụ vào các salon mới

-- Services salon 4: Premium Hair Studio Tan Binh
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(4, 1, 'Cat toc Undercut Taper', 'Undercut taper hien dai voi chi tiet chi ti.', 200000, 60, 'public/uploads/services/s4-undercut.jpg', 1, 1),
(4, 3, 'Nhuom toc Ash', 'Nhuom tong ash ban chay, sang bong va tre trung.', 650000, 180, 'public/uploads/services/s4-ash.jpg', 1, 2),
(4, 5, 'Goi dau SPA cao cap', 'Goi dau ket hop tinh dau thom va massage thu gian.', 150000, 45, 'public/uploads/services/s4-goispa.jpg', 1, 3);

-- Services salon 5: Beauty Palace Go Vap
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(5, 5, 'Massage lung toan bo cao cap', 'Massage toan bo lung voi các dầu thơm chuyên dụng.', 350000, 90, 'public/uploads/services/s5-massage.jpg', 1, 1),
(5, 4, 'Facial toàn diện premium', 'Cham soc da toan dien voi my pham co bao hanh.', 450000, 120, 'public/uploads/services/s5-facial.jpg', 1, 2),
(5, 1, 'Nhuom toc fashion & Uon', 'Nhuom toc mau thoi trang ket hop uon layer.', 700000, 180, 'public/uploads/services/s5-nhuom-uon.jpg', 1, 3);

-- Services salon 6: Modern Barbershop District 5
INSERT INTO services (salon_id, category_id, name, description, price, duration, image, is_active, sort_order) VALUES
(6, 1, 'Cat toc Undercut nam', 'Undercut co dien voi chi tiet sach sao.', 180000, 50, 'public/uploads/services/s6-undercut.jpg', 1, 1),
(6, 2, 'Uon toc nam hien dai', 'Uon toc nam voi hieu ung tu nhien.', 350000, 90, 'public/uploads/services/s6-uon.jpg', 1, 2),
(6, 5, 'Goi dau Barber Grooming', 'Goi dau chuyen sau cho nam voi massage da dau.', 120000, 40, 'public/uploads/services/s6-goi.jpg', 1, 3);
