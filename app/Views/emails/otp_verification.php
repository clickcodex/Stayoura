<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Verification Code - Stayora</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f4f6f8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #0F172A;">
  <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color: #f4f6f8; padding: 40px 15px;">
    <tr>
      <td align="center">
        <!-- Main Card -->
        <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 540px; background-color: #ffffff; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.06); overflow: hidden; border: 1px solid #E2E8F0;">
          
          <!-- Header Banner -->
          <tr>
            <td style="background: linear-gradient(135deg, #0F172A 0%, #0F172A 100%); padding: 32px 36px; text-align: center;">
              <h1 style="margin: 0; color: #ffffff; font-size: 26px; font-weight: 800; letter-spacing: -0.5px;">
                Stay<span style="color: #38BDF8;">ora</span>
              </h1>
              <p style="margin: 6px 0 0; color: #94a3b8; font-size: 13px; letter-spacing: 0.5px; text-transform: uppercase;">
                Luxury Stays &amp; Villa Getaways
              </p>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding: 36px 36px 28px;">
              <h2 style="margin: 0 0 12px; font-size: 20px; font-weight: 800; color: #0F172A;">
                Hello <?= htmlspecialchars($userName ?? 'Valued Guest') ?>,
              </h2>
              <p style="margin: 0 0 20px; font-size: 14.5px; line-height: 1.6; color: #475569;">
                Please use the one-time verification code below to complete your verification on Stayora.
              </p>

              <!-- OTP Code Display Box -->
              <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="margin: 28px 0;">
                <tr>
                  <td align="center" style="background-color: #f0f9ff; border: 2px dashed #C9A227; border-radius: 16px; padding: 24px 20px;">
                    <span style="display: block; font-size: 11px; font-weight: 800; color: #1E40AF; text-transform: uppercase; letter-spacing: 1.5px; margin-bottom: 8px;">
                      Your Verification Code
                    </span>
                    <span style="display: block; font-size: 38px; font-weight: 900; letter-spacing: 8px; color: #1E3A8A; font-family: 'Courier New', Courier, monospace;">
                      <?= htmlspecialchars($otp ?? '000000') ?>
                    </span>
                    <span style="display: block; font-size: 12px; color: #64748B; margin-top: 8px;">
                      ⏱️ Valid for <strong><?= htmlspecialchars($expires ?? '10 minutes') ?></strong>
                    </span>
                  </td>
                </tr>
              </table>

              <!-- Security Notice -->
              <div style="background-color: #fffbeb; border-left: 4px solid #f59e0b; padding: 12px 16px; border-radius: 6px; margin: 24px 0 10px;">
                <p style="margin: 0; font-size: 12.5px; line-height: 1.5; color: #92400e;">
                  <strong>Security Reminder:</strong> Stayora will never contact you asking for your OTP or password. Please do not share this code with anyone.
                </p>
              </div>

              <p style="margin: 24px 0 0; font-size: 13.5px; color: #64748B; line-height: 1.5;">
                If you did not request this verification code, please ignore this email or contact our support team.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #F8FAFC; padding: 24px 36px; text-align: center; border-top: 1px solid #E2E8F0;">
              <p style="margin: 0 0 6px; font-size: 12px; color: #94a3b8;">
                &copy; <?= date('Y') ?> Stayora Platforms. All rights reserved.
              </p>
              <p style="margin: 0; font-size: 11px; color: #cbd5e1;">
                This is an automated system email. Please do not reply directly to this message.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
