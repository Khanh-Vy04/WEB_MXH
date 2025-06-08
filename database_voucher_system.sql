-- ================================
-- VOUCHER SYSTEM DATABASE DESIGN
-- ================================

USE web_project;

-- 1. Bảng vouchers - Quản lý các voucher
CREATE TABLE IF NOT EXISTS vouchers (
    voucher_id INT PRIMARY KEY AUTO_INCREMENT,
    voucher_code VARCHAR(50) UNIQUE NOT NULL,
    voucher_name VARCHAR(255) NOT NULL,
    description TEXT,
    discount_type ENUM('percentage', 'fixed_amount') NOT NULL,
    discount_value DECIMAL(10,2) NOT NULL, -- Nếu percentage thì là % (0-100), nếu fixed thì là số tiền
    min_order_amount DECIMAL(10,2) DEFAULT 0, -- Số tiền đơn hàng tối thiểu để áp dụng
    max_discount_amount DECIMAL(10,2) NULL, -- Số tiền giảm tối đa (chỉ áp dụng cho percentage)
    usage_limit INT DEFAULT NULL, -- Số lần sử dụng tối đa (NULL = không giới hạn)
    used_count INT DEFAULT 0, -- Số lần đã sử dụng
    per_user_limit INT DEFAULT 1, -- Số lần 1 user có thể sử dụng
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Bảng orders - Quản lý đơn hàng
CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    order_code VARCHAR(50) UNIQUE NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0,
    final_amount DECIMAL(10,2) NOT NULL,
    voucher_id INT NULL,
    voucher_code VARCHAR(50) NULL,
    order_status ENUM('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    payment_status ENUM('pending', 'paid', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    shipping_address TEXT,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(20),
    customer_email VARCHAR(255),
    notes TEXT,
    order_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id) ON DELETE SET NULL
);

-- 3. Bảng order_items - Chi tiết sản phẩm trong đơn hàng
CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    product_price DECIMAL(10,2) NOT NULL,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);

-- 4. Bảng voucher_usage - Lịch sử sử dụng voucher
CREATE TABLE IF NOT EXISTS voucher_usage (
    usage_id INT PRIMARY KEY AUTO_INCREMENT,
    voucher_id INT NOT NULL,
    user_id INT NOT NULL,
    order_id INT NULL,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    discount_applied DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL
);

-- 5. Bảng user_vouchers - Voucher được gán cho user cụ thể
CREATE TABLE IF NOT EXISTS user_vouchers (
    user_voucher_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    voucher_id INT NOT NULL,
    assigned_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_used TINYINT(1) DEFAULT 0,
    used_date TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (voucher_id) REFERENCES vouchers(voucher_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_voucher (user_id, voucher_id)
);

-- 6. Bảng shopping_cart - Giỏ hàng
CREATE TABLE IF NOT EXISTS shopping_cart (
    cart_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- 7. Cập nhật bảng users để thêm thông tin khách hàng
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS address TEXT,
ADD COLUMN IF NOT EXISTS date_of_birth DATE NULL,
ADD COLUMN IF NOT EXISTS total_orders INT DEFAULT 0,
ADD COLUMN IF NOT EXISTS total_spent DECIMAL(10,2) DEFAULT 0,
ADD COLUMN IF NOT EXISTS last_order_date DATETIME NULL,
ADD COLUMN IF NOT EXISTS customer_level ENUM('bronze', 'silver', 'gold', 'platinum') DEFAULT 'bronze',
ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- ================================
-- SAMPLE DATA
-- ================================

-- Thêm mẫu voucher
INSERT INTO vouchers (voucher_code, voucher_name, description, discount_type, discount_value, min_order_amount, max_discount_amount, usage_limit, start_date, end_date) VALUES
('WELCOME10', 'Chào mừng khách hàng mới', 'Giảm 10% cho đơn hàng đầu tiên', 'percentage', 10.00, 100000, 50000, 100, '2024-01-01', '2024-12-31'),
('SALE50K', 'Giảm 50k', 'Giảm 50,000đ cho đơn hàng từ 500k', 'fixed_amount', 50000.00, 500000, NULL, 50, '2024-01-01', '2024-12-31'),
('VIP20', 'Voucher VIP', 'Giảm 20% cho khách hàng VIP', 'percentage', 20.00, 200000, 100000, NULL, '2024-01-01', '2024-12-31'),
('FREESHIP', 'Miễn phí vận chuyển', 'Giảm 30,000đ phí vận chuyển', 'fixed_amount', 30000.00, 0, NULL, 200, '2024-01-01', '2024-12-31');

-- ================================
-- INDEXES FOR PERFORMANCE
-- ================================

CREATE INDEX idx_vouchers_code ON vouchers(voucher_code);
CREATE INDEX idx_vouchers_active ON vouchers(is_active, start_date, end_date);
CREATE INDEX idx_orders_user ON orders(user_id);
CREATE INDEX idx_orders_status ON orders(order_status);
CREATE INDEX idx_orders_date ON orders(order_date);
CREATE INDEX idx_voucher_usage_user ON voucher_usage(user_id);
CREATE INDEX idx_voucher_usage_voucher ON voucher_usage(voucher_id);

-- ================================
-- STORED PROCEDURES
-- ================================

DELIMITER //

-- Procedure kiểm tra voucher có hợp lệ không
CREATE PROCEDURE CheckVoucherValidity(
    IN p_voucher_code VARCHAR(50),
    IN p_user_id INT,
    IN p_order_amount DECIMAL(10,2),
    OUT p_valid BOOLEAN,
    OUT p_discount_amount DECIMAL(10,2),
    OUT p_message VARCHAR(255)
)
BEGIN
    DECLARE v_voucher_id INT;
    DECLARE v_discount_type VARCHAR(20);
    DECLARE v_discount_value DECIMAL(10,2);
    DECLARE v_min_order_amount DECIMAL(10,2);
    DECLARE v_max_discount_amount DECIMAL(10,2);
    DECLARE v_usage_limit INT;
    DECLARE v_used_count INT;
    DECLARE v_per_user_limit INT;
    DECLARE v_user_used_count INT;
    DECLARE v_start_date DATE;
    DECLARE v_end_date DATE;
    DECLARE v_is_active TINYINT;

    SET p_valid = FALSE;
    SET p_discount_amount = 0;
    SET p_message = '';

    -- Lấy thông tin voucher
    SELECT voucher_id, discount_type, discount_value, min_order_amount, max_discount_amount,
           usage_limit, used_count, per_user_limit, start_date, end_date, is_active
    INTO v_voucher_id, v_discount_type, v_discount_value, v_min_order_amount, v_max_discount_amount,
         v_usage_limit, v_used_count, v_per_user_limit, v_start_date, v_end_date, v_is_active
    FROM vouchers
    WHERE voucher_code = p_voucher_code;

    -- Kiểm tra voucher có tồn tại không
    IF v_voucher_id IS NULL THEN
        SET p_message = 'Mã voucher không tồn tại';
    -- Kiểm tra voucher có active không
    ELSEIF v_is_active = 0 THEN
        SET p_message = 'Mã voucher đã bị vô hiệu hóa';
    -- Kiểm tra thời gian hiệu lực
    ELSEIF CURDATE() < v_start_date THEN
        SET p_message = 'Mã voucher chưa có hiệu lực';
    ELSEIF CURDATE() > v_end_date THEN
        SET p_message = 'Mã voucher đã hết hạn';
    -- Kiểm tra số tiền đơn hàng tối thiểu
    ELSEIF p_order_amount < v_min_order_amount THEN
        SET p_message = CONCAT('Đơn hàng phải có giá trị tối thiểu ', FORMAT(v_min_order_amount, 0), 'đ');
    -- Kiểm tra giới hạn sử dụng tổng
    ELSEIF v_usage_limit IS NOT NULL AND v_used_count >= v_usage_limit THEN
        SET p_message = 'Mã voucher đã hết lượt sử dụng';
    ELSE
        -- Kiểm tra giới hạn sử dụng per user
        SELECT COUNT(*) INTO v_user_used_count
        FROM voucher_usage
        WHERE voucher_id = v_voucher_id AND user_id = p_user_id;

        IF v_user_used_count >= v_per_user_limit THEN
            SET p_message = 'Bạn đã sử dụng hết lượt cho mã voucher này';
        ELSE
            -- Tính toán số tiền giảm
            IF v_discount_type = 'percentage' THEN
                SET p_discount_amount = p_order_amount * v_discount_value / 100;
                IF v_max_discount_amount IS NOT NULL AND p_discount_amount > v_max_discount_amount THEN
                    SET p_discount_amount = v_max_discount_amount;
                END IF;
            ELSE
                SET p_discount_amount = v_discount_value;
            END IF;

            -- Đảm bảo không giảm quá số tiền đơn hàng
            IF p_discount_amount > p_order_amount THEN
                SET p_discount_amount = p_order_amount;
            END IF;

            SET p_valid = TRUE;
            SET p_message = 'Mã voucher hợp lệ';
        END IF;
    END IF;
END//

DELIMITER ; 