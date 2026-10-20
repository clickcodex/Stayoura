<?php
namespace App\Services;

use Throwable;

class Msg91Service {

    private string $authKey = '';
    private string $templateId = '';
    private string $senderId = 'FRMLEL';
    private int $otpExpiry = 10;
    private int $otpLength = 6;

    public function __construct() {
        $config = require __DIR__ . '/../Config/sms.php';
        $msgConfig = $config['drivers']['msg91'] ?? [];

        $this->authKey    = trim($msgConfig['auth_key'] ?? '');
        $this->templateId = trim($msgConfig['template_id'] ?? '');
        $this->senderId   = trim($msgConfig['sender_id'] ?? 'FRMLEL');
        $this->otpExpiry  = (int)($msgConfig['otp_expiry'] ?? 10);
        $this->otpLength  = (int)($msgConfig['otp_length'] ?? 6);
    }

    /**
     * Check if MSG91 is configured
     */
    public function isConfigured(): bool {
        return !empty($this->authKey);
    }

    /**
     * Format phone number to Indian standard expected by MSG91: 91XXXXXXXXXX (no plus)
     */
    public function formatPhoneNumber(string $phone): string {
        $clean = preg_replace('/[^0-9]/', '', trim($phone));

        // If 10 digits, prepend 91
        if (strlen($clean) === 10) {
            return '91' . $clean;
        }

        // If 12 digits starting with 91, return as is
        if (strlen($clean) === 12 && str_starts_with($clean, '91')) {
            return $clean;
        }

        // Fallback: strip leading 0 if present and check 10 digits
        if (strlen($clean) === 11 && str_starts_with($clean, '0')) {
            return '91' . substr($clean, 1);
        }

        return $clean;
    }

    /**
     * Send OTP SMS via MSG91 OTP API v5
     *
     * @param string $phone Recipient mobile number
     * @param string $otp 6-digit verification code
     * @param string $purpose Context ('registration', 'phone_change', 'forgot_password_sms')
     * @return array ['success' => bool, 'message_id' => string|null, 'error' => string|null, 'is_simulated' => bool]
     */
    public function sendOtpSms(string $phone, string $otp, string $purpose = 'verification'): array {
        $formattedPhone = $this->formatPhoneNumber($phone);

        // Simulation mode if auth key is not configured
        if (!$this->isConfigured()) {
            $logMsg = "[MSG91 Simulation] Auth key not set in .env. Message to {$formattedPhone} simulated with OTP: {$otp} (Purpose: {$purpose})";
            error_log($logMsg);
            return [
                'success'      => true,
                'message_id'   => 'SIMULATED_' . uniqid(),
                'error'        => null,
                'is_simulated' => true,
                'info'         => 'Simulated mode (MSG91 credentials not set)',
            ];
        }

        $purposeLabel = match ($purpose) {
            'registration'        => 'Stayora Account Registration',
            'phone_change'        => 'Stayora Mobile Number Update',
            'forgot_password_sms' => 'Stayora Password Recovery',
            default               => 'Stayora Verification',
        };

        // Build MSG91 v5 OTP endpoint query
        $queryParams = [
            'authkey'    => $this->authKey,
            'mobile'     => $formattedPhone,
            'otp'        => $otp,
            'otp_expiry' => $this->otpExpiry,
            'otp_length' => $this->otpLength,
        ];

        if (!empty($this->templateId)) {
            $queryParams['template_id'] = $this->templateId;
        }

        $url = 'https://control.msg91.com/api/v5/otp?' . http_build_query($queryParams);

        $payload = [
            'otp'          => $otp,
            'purpose'      => $purposeLabel,
            'company_name' => 'Stayora',
        ];

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'Accept: application/json',
                ],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlError) {
                error_log("MSG91 cURL Error: " . $curlError);
                return [
                    'success'      => false,
                    'message_id'   => null,
                    'error'        => "Connection error to SMS gateway: {$curlError}",
                    'is_simulated' => false,
                ];
            }

            $decoded = json_decode($response, true);

            // MSG91 returns {"type":"success","message":"..."} or error object
            if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['type']) && strtolower($decoded['type']) === 'success') {
                return [
                    'success'      => true,
                    'message_id'   => $decoded['message'] ?? ('MSG91_' . uniqid()),
                    'error'        => null,
                    'is_simulated' => false,
                ];
            }

            $errMsg = $decoded['message'] ?? ($decoded['error'] ?? "MSG91 returned status {$httpCode}");
            error_log("MSG91 API Error: " . $errMsg);

            return [
                'success'      => false,
                'message_id'   => null,
                'error'        => $errMsg,
                'is_simulated' => false,
            ];
        } catch (Throwable $e) {
            error_log("MSG91 Exception: " . $e->getMessage());
            return [
                'success'      => false,
                'message_id'   => null,
                'error'        => $e->getMessage(),
                'is_simulated' => false,
            ];
        }
    }

    /**
     * Resend OTP via MSG91 Retry API
     */
    public function resendOtp(string $phone): array {
        $formattedPhone = $this->formatPhoneNumber($phone);

        if (!$this->isConfigured()) {
            return [
                'success'      => true,
                'is_simulated' => true,
            ];
        }

        $url = 'https://control.msg91.com/api/v5/otp/retry?' . http_build_query([
            'authkey'   => $this->authKey,
            'mobile'    => $formattedPhone,
            'retrytype' => 'text',
        ]);

        try {
            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_HTTPHEADER     => ['Accept: application/json'],
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            curl_close($ch);

            $decoded = json_decode($response, true);
            if (isset($decoded['type']) && strtolower($decoded['type']) === 'success') {
                return ['success' => true, 'message' => $decoded['message'] ?? 'OTP resent successfully'];
            }

            return ['success' => false, 'error' => $decoded['message'] ?? 'Failed to resend OTP'];
        } catch (Throwable $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
