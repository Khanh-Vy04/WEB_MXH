const express = require('express');
const nodemailer = require('nodemailer');
const cors = require('cors');

// Thay thế import từ .env bằng khai báo trực tiếp
const EMAIL_USER = 'trinhngo1909@gmail.com';
const EMAIL_PASS = 'oafw ursq uasm libn';

console.log("🔧 DEBUG EMAIL_USER:", EMAIL_USER);
console.log("🔧 DEBUG EMAIL_PASS:", EMAIL_PASS ? "(Đã có mật khẩu ✅)" : "(Thiếu mật khẩu ❌)");

const app = express();
app.use(cors());
app.use(express.json());

app.post('/send-verification', async (req, res) => {
  const { email, verificationCode } = req.body;

  if (!email || !verificationCode) {
    return res.status(400).json({ error: 'Thiếu email hoặc mã xác thực' });
  }

  try {
    const transporter = nodemailer.createTransport({
      service: 'gmail',
      auth: {
        user: EMAIL_USER,
        pass: EMAIL_PASS
      }
    });

    const html = `
      <!DOCTYPE html>
      <html lang="vi">
      <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Đặt lại mật khẩu - AuraDisc</title>
      </head>
      <body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5;">
        <table cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f5f5f5; padding: 20px;">
          <tr>
            <td align="center">
              <table cellpadding="0" cellspacing="0" border="0" width="600" style="background-color: #ffffff; border-radius: 10px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                
                <!-- Header -->
                <tr>
                  <td style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); padding: 40px 30px; text-align: center;">
                    <h1 style="color: #ffffff; margin: 0; font-size: 28px; font-weight: bold; letter-spacing: 1px;">
                      🎵 AuraDisc
                    </h1>
                    <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0 0; font-size: 16px;">
                      Quản trị hệ thống
                    </p>
                  </td>
                </tr>
                
                <!-- Content -->
                <tr>
                  <td style="padding: 40px 30px;">
                    <h2 style="color: #333333; margin: 0 0 20px 0; font-size: 24px; font-weight: 600;">
                      Đặt lại mật khẩu quản trị
                    </h2>
                    
                    <p style="color: #666666; line-height: 1.6; margin: 0 0 20px 0; font-size: 16px;">
                      Kính chào quý khách hàng,
                    </p>
                    
                    <p style="color: #666666; line-height: 1.6; margin: 0 0 25px 0; font-size: 16px;">
                      AuraDisc đã nhận được yêu cầu đặt lại mật khẩu cho tài khoản quản trị của bạn. 
                      Vui lòng sử dụng mã xác thực dưới đây để tạo mật khẩu mới.
                    </p>
                    
                    <!-- OTP Box -->
                    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin: 30px 0;">
                      <tr>
                        <td align="center">
                          <div style="background: linear-gradient(135deg, #ff6b35 0%, #f7931e 100%); color: #ffffff; padding: 20px 30px; border-radius: 10px; display: inline-block; font-size: 32px; font-weight: bold; letter-spacing: 3px; text-align: center; box-shadow: 0 4px 15px rgba(255, 107, 53, 0.3);">
                            ${verificationCode}
                          </div>
                        </td>
                      </tr>
                    </table>
                    
                    <p style="color: #666666; line-height: 1.6; margin: 25px 0 20px 0; font-size: 16px;">
                      Để hoàn tất quá trình, bạn hãy quay lại trang web và nhập mã xác thực này.
                    </p>
                    
                    <!-- Security Notice -->
                    <div style="background-color: #fff3e0; border-left: 4px solid #ff6b35; padding: 20px; margin: 25px 0; border-radius: 5px;">
                      <h3 style="color: #e65100; margin: 0 0 10px 0; font-size: 18px; font-weight: 600;">
                        🔐 Lưu ý bảo mật quan trọng:
                      </h3>
                      <ul style="color: #bf360c; margin: 0; padding-left: 20px; line-height: 1.6;">
                        <li>Mã xác thực này sẽ <strong>hết hạn sau 15 phút</strong></li>
                        <li>Vì lý do bảo mật, vui lòng <strong>không chia sẻ</strong> mã này cho bất kỳ ai</li>
                        <li>Nếu bạn không phải là người yêu cầu thay đổi mật khẩu, xin vui lòng <strong>bỏ qua email này</strong></li>
                      </ul>
                    </div>
                    
                    <p style="color: #666666; line-height: 1.6; margin: 25px 0 0 0; font-size: 16px;">
                      Nếu bạn gặp bất kỳ khó khăn nào, đừng ngần ngại liên hệ với đội ngũ kỹ thuật.
                    </p>
                  </td>
                </tr>
                
                <!-- Footer -->
                <tr>
                  <td style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #e9ecef;">
                    <p style="color: #6c757d; margin: 0 0 10px 0; font-size: 14px;">
                      Trân trọng,<br>
                      <strong style="color: #ff6b35;">Đội ngũ AuraDisc</strong>
                    </p>
                    <p style="color: #adb5bd; margin: 0; font-size: 12px;">
                      Email này được gửi tự động, vui lòng không trả lời.
                    </p>
                  </td>
                </tr>
                
              </table>
            </td>
          </tr>
        </table>
      </body>
      </html>
    `;

    const info = await transporter.sendMail({
      from: `"AuraDisc Admin Support" <${EMAIL_USER}>`,
      to: email,
      subject: '🔐 Mã xác thực đặt lại mật khẩu quản trị - AuraDisc',
      html
    });

    console.log('✅ Email đã gửi thành công:', info.messageId);
    console.log('📧 Gửi tới:', email);
    console.log('🔢 Mã OTP:', verificationCode);
    
    res.json({ success: true, messageId: info.messageId });
  } catch (err) {
    console.error('❌ Lỗi gửi mail:', err.message);
    res.status(500).json({ 
      success: false, 
      message: 'Lỗi gửi mail', 
      error: err.message 
    });
  }
});

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({ 
    status: 'OK', 
    service: 'AuraDisc Mailer Service',
    timestamp: new Date().toISOString(),
    emailConfigured: !!(EMAIL_USER && EMAIL_PASS)
  });
});

const PORT = 3000;
app.listen(PORT, () => {
  console.log('🚀 =================================');
  console.log(`📧 AuraDisc Mailer API đang chạy tại http://localhost:${PORT}`);
  console.log('🌐 Health check: http://localhost:3000/health');
  console.log('📮 Send verification: POST http://localhost:3000/send-verification');
  console.log('🚀 =================================');
}); 