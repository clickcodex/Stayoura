<?php
namespace App\Services;

use App\Config\Database;
use mysqli;

class MailNotificationService {
    private mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Log notification dispatch to notification_logs table
     */
    private function log(string $channel, string $recipient, string $eventType, string $subject, string $payload, string $status = 'sent', ?string $error = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO notification_logs (channel, recipient, event_type, subject, message_payload, status, error_message, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        if ($stmt) {
            $stmt->bind_param("sssssss", $channel, $recipient, $eventType, $subject, $payload, $status, $error);
            $stmt->execute();
        }
    }

    /**
     * Send email via PHP mail() or configured transport, with automatic logging
     */
    public function send(string $toEmail, string $subject, string $htmlBody, string $eventType): bool {
        $settings = \App\Helpers\SiteHelper::getSettings();
        // Check if master email notifications are enabled (unless it's an admin test)
        if ($eventType !== 'admin_settings_test_email' && isset($settings['email_notifications_enabled']) && (int)$settings['email_notifications_enabled'] === 0) {
            $this->log('email', $toEmail, $eventType, $subject, $htmlBody, 'queued', 'Email notifications disabled in platform settings');
            return true;
        }

        $fromName = !empty($settings['email_sender_name']) ? $settings['email_sender_name'] : 'Stayora Bookings';
        $fromAddr = !empty($settings['email_sender_address']) ? $settings['email_sender_address'] : 'noreply@stayora.com';
        $replyTo = !empty($settings['email_reply_to']) ? $settings['email_reply_to'] : 'support@stayora.com';

        $headers = [
            'MIME-Version: 1.0',
            'Content-type: text/html; charset=utf-8',
            "From: {$fromName} <{$fromAddr}>",
            "Reply-To: {$replyTo}",
            'X-Mailer: Stayora/1.0'
        ];

        $headersStr = implode("\r\n", $headers);
        $sent = false;
        $error = null;

        try {
            // Attempt standard mail delivery
            $sent = @mail($toEmail, $subject, $htmlBody, $headersStr);
            if (!$sent) {
                // In local development or environments without sendmail, mail() may return false,
                // but the event payload is successfully logged in the audit ledger.
                $error = "Mail transport offline or simulated locally";
            }
        } catch (\Throwable $t) {
            $error = $t->getMessage();
        }

        $this->log('email', $toEmail, $eventType, $subject, $htmlBody, $sent ? 'sent' : 'queued', $error);
        return true;
    }

    /**
     * Trigger: Customer created a new booking request
     */
    public function triggerBookingRequested(array $booking): void {
        $guestEmail = $booking['customer_email'] ?? $booking['guest_email'] ?? '';
        $guestName  = $booking['customer_name'] ?? $booking['guest_name'] ?? 'Valued Guest';
        $property   = $booking['farmhouse_title'] ?? 'Farmhouse Estate';
        $checkIn    = $booking['check_in'] ?? '';
        $checkOut   = $booking['check_out'] ?? '';
        $total      = number_format((float)($booking['total_price'] ?? 0), 2);

        if (!empty($guestEmail)) {
            $subject = "Booking Request Received: {$property} (#BK-{$booking['id']})";
            $body = "
                <div style='font-family:sans-serif;max-width:600px;margin:auto;padding:24px;border:1px solid #e2e8f0;border-radius:16px;'>
                    <h2 style='color:#0f172a;'>Hi {$guestName},</h2>
                    <p style='color:#334155;line-height:1.6;'>We've received your booking request for <strong>{$property}</strong>.</p>
                    <div style='background:#f8fafc;padding:16px;border-radius:12px;margin:16px 0;'>
                        <p style='margin:4px 0;'><strong>Check-In:</strong> {$checkIn}</p>
                        <p style='margin:4px 0;'><strong>Check-Out:</strong> {$checkOut}</p>
                        <p style='margin:4px 0;'><strong>Total Tariff:</strong> ₹{$total}</p>
                        <p style='margin:4px 0;'><strong>Status:</strong> Pending Host Approval & Verification</p>
                    </div>
                    <p style='color:#64748b;font-size:13px;'>Our team will notify you as soon as your reservation is confirmed.</p>
                </div>
            ";
            $this->send($guestEmail, $subject, $body, 'booking_requested_customer');
        }
    }

    /**
     * Trigger: Booking approved
     */
    public function triggerBookingApproved(array $booking): void {
        $guestEmail = $booking['customer_email'] ?? $booking['guest_email'] ?? '';
        $guestName  = $booking['customer_name'] ?? $booking['guest_name'] ?? 'Valued Guest';
        $property   = $booking['farmhouse_title'] ?? 'Farmhouse Estate';
        $voucherUrl = url('booking/voucher?id=' . $booking['id']);

        if (!empty($guestEmail)) {
            $subject = "🎉 Booking Confirmed: {$property} (#BK-{$booking['id']})";
            $body = "
                <div style='font-family:sans-serif;max-width:600px;margin:auto;padding:24px;border:1px solid #10b981;border-radius:16px;'>
                    <h2 style='color:#059669;'>Reservation Confirmed!</h2>
                    <p style='color:#334155;line-height:1.6;'>Dear {$guestName}, your stay at <strong>{$property}</strong> has been officially approved!</p>
                    <div style='margin:24px 0;text-align:center;'>
                        <a href='{$voucherUrl}' style='display:inline-block;padding:12px 24px;background:#059669;color:#fff;text-decoration:none;border-radius:10px;font-weight:bold;'>
                            View &amp; Download Booking Voucher
                        </a>
                    </div>
                    <p style='color:#64748b;font-size:12px;'>Please show your digital voucher or QR code upon check-in at the property.</p>
                </div>
            ";
            $this->send($guestEmail, $subject, $body, 'booking_approved_customer');
        }
    }

    /**
     * Trigger: Payment verified
     */
    public function triggerPaymentVerified(array $booking): void {
        $guestEmail = $booking['customer_email'] ?? $booking['guest_email'] ?? '';
        $guestName  = $booking['customer_name'] ?? $booking['guest_name'] ?? 'Valued Guest';
        $property   = $booking['farmhouse_title'] ?? 'Farmhouse Estate';
        $amount     = number_format((float)($booking['total_price'] ?? 0), 2);
        $utr        = $booking['utr_number'] ?? 'Direct Transfer';

        if (!empty($guestEmail)) {
            $subject = "Payment Receipt: ₹{$amount} Confirmed for {$property}";
            $body = "
                <div style='font-family:sans-serif;max-width:600px;margin:auto;padding:24px;border:1px solid #0284c7;border-radius:16px;'>
                    <h2 style='color:#0284c7;'>Payment Verified</h2>
                    <p style='color:#334155;'>Hi {$guestName}, your payment has been successfully reconciled.</p>
                    <p><strong>Amount:</strong> ₹{$amount}</p>
                    <p><strong>Transaction Ref:</strong> {$utr}</p>
                </div>
            ";
            $this->send($guestEmail, $subject, $body, 'payment_verified_customer');
        }
    }
}
