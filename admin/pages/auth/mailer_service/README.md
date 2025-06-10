# 📧 AuraDisc Mailer Service

Email service cho chức năng đặt lại mật khẩu quản trị AuraDisc.

## 🚀 Cách chạy

```bash
# Cài đặt dependencies
npm install

# Chạy service
npm start

# Hoặc chạy với nodemon (dev mode)
npm run dev
```

## 🔧 Cấu hình

Đảm bảo file `.env` có các thông tin sau:

```env
EMAIL_USER=trinhngo1909@gmail.com
EMAIL_PASS=oafw ursq uasm libn
```

## 📡 API Endpoints

### POST /send-verification
Gửi email chứa mã OTP để đặt lại mật khẩu.

**Request Body:**
```json
{
  "email": "admin@example.com",
  "verificationCode": "123456"
}
```

**Response:**
```json
{
  "success": true,
  "messageId": "email-message-id"
}
```

### GET /health
Kiểm tra trạng thái service.

**Response:**
```json
{
  "status": "OK",
  "service": "AuraDisc Mailer Service",
  "timestamp": "2024-01-01T00:00:00.000Z",
  "emailConfigured": true
}
```

## 🔐 Bảo mật

- Sử dụng Gmail App Password (không phải mật khẩu thường)
- File `.env` không được commit lên git
- Mã OTP có thời hạn 15 phút

## 🎨 Email Template

Email được gửi có thiết kế responsive với:
- Header gradient AuraDisc
- Mã OTP nổi bật
- Thông báo bảo mật
- Footer chuyên nghiệp

## 📝 Logs

Service sẽ log các thông tin:
- ✅ Email gửi thành công
- 📧 Địa chỉ email nhận
- 🔢 Mã OTP (để debug)
- ❌ Lỗi nếu có 