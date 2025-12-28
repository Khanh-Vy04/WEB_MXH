INSERT INTO users (username, password, email, full_name, role_id, created_at)
VALUES ('superadmin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'superadmin@example.com', 'Super Admin', 3, NOW());
-- Password is 'password' (hash may vary, this is a standard bcrypt hash for 'password')
-- Note: You may need to generate a fresh hash using password_hash('your_password', PASSWORD_DEFAULT) in PHP if this doesn't work.

