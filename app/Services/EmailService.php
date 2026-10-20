<?php
namespace App\Services;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Throwable;

class EmailService {

    private array $config;

    public function __construct() {
        $this->config = require __DIR__ . '/../Config/mail.php';
    }

    /**
     * Check if valid SMTP credentials are configured (not placeholders)
     */
    public function isConfigured(): bool {
        $user = $this->config['username'] ?? '';
        $pass = $this->config['password'] ?? '';
        $host = $this->config['host'] ?? '';

        if (empty($user) || empty($pass) || empty($host)) {
            return false;
        }

        // Check against known default placeholder strings
        if (str_contains($user, 'yourdomain@email.com') || str_contains($pass, 'your_email_password')) {
            return false;
        }

        return true;
    }

    /**
     * Send an HTML email
     *
     * @param string $toEmail Recipient email address
     * @param string $toName Recipient name
     * @param string $subject Email subject line
     * @param string $htmlBody Full HTML message
     * @param string|null $altBody Plain text fallback
     * @return array ['success' => bool, 'error' => string|null, 'is_simulated' => bool]
     */
    public function sendMail(
        string $toEmail,
        string $toName,
        string $subject,
        string $htmlBody,
        ?string $altBody = null
    ): array {
        if (!$this->isConfigured()) {
            error_log("[Email Simulation] To: {$toEmail} | Subject: {$subject} | Body: (HTML Template rendered successfully)");
            return [
                'success'      => true,
                'error'        => null,
                'is_simulated' => true,
                'message'      => 'Email simulated (Configure SMTP in .env to send live emails)',
            ];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $this->config['host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $this->config['username'];
            $mail->Password   = $this->config['password'];
            $mail->SMTPSecure = strtolower($this->config['encryption'] ?? 'ssl') === 'tls' 
                ? PHPMailer::ENCRYPTION_STARTTLS 
                : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = (int)($this->config['port'] ?? 465);

            $fromAddr = $this->config['from']['address'] ?? 'noreply@stayora.com';
            $fromName = $this->config['from']['name'] ?? 'Stayora Support';
            $mail->setFrom($fromAddr, $fromName);

            if (!empty($this->config['reply_to']['address'])) {
                $mail->addReplyTo($this->config['reply_to']['address'], $this->config['reply_to']['name'] ?? '');
            }

            $mail->addAddress($toEmail, $toName);
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = $altBody ?: strip_tags($htmlBody);

            $mail->send();

            return [
                'success'      => true,
                'error'        => null,
                'is_simulated' => false,
            ];
        } catch (Exception $e) {
            error_log("PHPMailer Error: " . $e->getMessage());
            return [
                'success'      => false,
                'error'        => $e->getMessage(),
                'is_simulated' => false,
            ];
        } catch (Throwable $e) {
            error_log("Email General Error: " . $e->getMessage());
            return [
                'success'      => false,
                'error'        => $e->getMessage(),
                'is_simulated' => false,
            ];
        }
    }

    /**
     * Render an email template with data
     */
    public function renderTemplate(string $templateName, array $data = []): string {
        extract($data);
        ob_start();
        $file = __DIR__ . "/../Views/emails/{$templateName}.php";
        if (file_exists($file)) {
            include $file;
        } else {
            echo "<p>Your Stayora Verification Code is: <strong>" . htmlspecialchars($data['otp'] ?? '') . "</strong></p>";
        }
        return ob_get_clean();
    }

    /**
     * Send OTP Verification Email
     */
    public function sendOtpEmail(string $toEmail, string $userName, string $otp, string $purpose = 'verification'): array {
        $subject = "Stayora Verification Code: {$otp}";
        $html = $this->renderTemplate('otp_verification', [
            'userName' => $userName,
            'otp'      => $otp,
            'purpose'  => $purpose,
            'expires'  => '10 minutes',
        ]);

        return $this->sendMail($toEmail, $userName, $subject, $html);
    }

    /**
     * Send Forgot Password Dual Recovery Email
     */
    public function sendPasswordResetOtpEmail(string $toEmail, string $userName, string $otp): array {
        $subject = "Password Reset Verification Code – Stayora";
        $html = $this->renderTemplate('forgot_password', [
            'userName' => $userName,
            'otp'      => $otp,
            'expires'  => '10 minutes',
        ]);

        return $this->sendMail($toEmail, $userName, $subject, $html);
    }
}
