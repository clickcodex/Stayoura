<?php
namespace App\Services;

use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;
use Throwable;

class TwilioService {

    private string $accountSid = '';
    private string $authToken = '';
    private string $apiKey = '';
    private string $apiSecret = '';
    private string $fromNumber = '+17372212163';
    private ?Client $client = null;

    public function __construct() {
        $config = require __DIR__ . '/../Config/sms.php';
        $twilioConfig = $config['drivers']['twilio'] ?? [];

        $this->accountSid = trim($twilioConfig['account_sid'] ?? ($twilioConfig['sid'] ?? ''));
        $this->authToken  = trim($twilioConfig['auth_token']  ?? ($twilioConfig['token'] ?? ''));
        $this->apiKey     = trim($twilioConfig['api_key']     ?? '');
        $this->apiSecret  = trim($twilioConfig['api_secret']  ?? '');
        $this->fromNumber = trim($twilioConfig['from']        ?? '+17372212163');

        // Check if SID is an API Key (starts with SK)
        if (str_starts_with($this->accountSid, 'SK') && empty($this->apiKey)) {
            $this->apiKey = $this->accountSid;
            $this->apiSecret = $this->authToken;
            $this->accountSid = '';
        }

        try {
            if (!empty($this->apiKey) && !empty($this->apiSecret) && !empty($this->accountSid)) {
                // Initialized with API Key + Secret + Account SID
                $this->client = new Client($this->apiKey, $this->apiSecret, $this->accountSid);
            } elseif (!empty($this->accountSid) && !empty($this->authToken)) {
                // Initialized with Account SID + Auth Token
                $this->client = new Client($this->accountSid, $this->authToken);
            }
        } catch (Throwable $e) {
            error_log("Twilio Client Initialization Warning: " . $e->getMessage());
            $this->client = null;
        }
    }

    /**
     * Check if Twilio credentials are fully configured
     */
    public function isConfigured(): bool {
        return $this->client !== null;
    }

    /**
     * Format Indian phone number into E.164 (+91XXXXXXXXXX)
     */
    public function formatPhoneNumber(string $phone): string {
        $clean = preg_replace('/[^0-9+]/', '', trim($phone));

        if (str_starts_with($clean, '+')) {
            return $clean;
        }

        // If 10 digits, assume Indian number (+91)
        if (preg_match('/^[0-9]{10}$/', $clean)) {
            return '+91' . $clean;
        }

        // If 12 digits starting with 91
        if (preg_match('/^91[0-9]{10}$/', $clean)) {
            return '+' . $clean;
        }

        // Fallback
        return '+' . $clean;
    }

    /**
     * Send an SMS message using Twilio
     *
     * @param string $to Recipient phone number
     * @param string $body SMS message content
     * @return array ['success' => bool, 'sid' => string|null, 'error' => string|null, 'is_simulated' => bool]
     */
    public function sendSms(string $to, string $body): array {
        $formattedTo = $this->formatPhoneNumber($to);

        // If Twilio credentials are not yet set in .env
        if (!$this->isConfigured()) {
            $msg = "Twilio credentials not configured in .env. Message to {$formattedTo} simulated: {$body}";
            error_log("[Twilio Simulation] " . $msg);
            return [
                'success'      => true,
                'sid'          => 'SIMULATED_' . uniqid(),
                'error'        => null,
                'is_simulated' => true,
                'info'         => 'Simulated mode (credentials not configured)',
            ];
        }

        try {
            $message = $this->client->messages->create(
                $formattedTo,
                [
                    'from' => $this->fromNumber,
                    'body' => $body,
                ]
            );

            return [
                'success'      => true,
                'sid'          => $message->sid,
                'error'        => null,
                'is_simulated' => false,
            ];
        } catch (TwilioException $e) {
            $rawMsg = $e->getMessage();
            // If Twilio Trial account restriction for India
            if (str_contains($rawMsg, "Invalid template name") || str_contains($rawMsg, "Trial accounts can only use predefined SMS templates")) {
                try {
                    $fallbackMsg = $this->client->messages->create(
                        $formattedTo,
                        [
                            'from' => $this->fromNumber,
                            'body' => 'sms_appointment_reminders',
                        ]
                    );
                    return [
                        'success'           => true,
                        'sid'               => $fallbackMsg->sid,
                        'error'             => null,
                        'is_simulated'      => false,
                        'is_trial_template' => true,
                    ];
                } catch (Throwable $e2) {
                    error_log("Twilio Trial fallback failed: " . $e2->getMessage());
                }
            }

            $friendlyErr = $rawMsg;
            if (str_contains($rawMsg, "actor doesn't have any assertions")) {
                $friendlyErr = "Your Twilio API Key is 'Restricted' without SMS permissions. Please use your 32-character Primary Auth Token or create a 'Standard' API Key.";
            } elseif (str_contains($rawMsg, "Authenticate") && str_contains($rawMsg, "401")) {
                $friendlyErr = "Twilio authentication failed. Please verify your Account SID and 32-character Auth Token.";
            }
            error_log("Twilio API Error: " . $rawMsg);
            return [
                'success'      => false,
                'sid'          => null,
                'error'        => $friendlyErr,
                'is_simulated' => false,
            ];
        } catch (Throwable $e) {
            $err = "Twilio Unexpected Error: " . $e->getMessage();
            error_log($err);
            return [
                'success'      => false,
                'sid'          => null,
                'error'        => $e->getMessage(),
                'is_simulated' => false,
            ];
        }
    }

    /**
     * Send an OTP SMS with standard Stayora branding
     */
    public function sendOtpSms(string $phone, string $otp, string $purpose = 'verification'): array {
        $purposeLabel = match ($purpose) {
            'registration'        => 'Stayora account registration',
            'phone_change'        => 'updating your mobile number',
            'forgot_password_sms' => 'password reset request',
            default               => 'verification',
        };

        $body = "Your Stayora verification code for {$purposeLabel} is {$otp}. Valid for 10 minutes. Please do not share this code with anyone.";

        return $this->sendSms($phone, $body);
    }
}
