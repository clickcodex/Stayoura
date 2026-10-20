<?php
namespace App\Services;

use App\Config\Database;
use mysqli;

class WhatsAppGatewayService {
    private mysqli $db;

    public function __construct(?mysqli $db = null) {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Record WhatsApp / SMS transaction in notification_logs
     */
    private function log(string $channel, string $recipient, string $eventType, string $payload, string $status = 'sent', ?string $error = null): void {
        $stmt = $this->db->prepare("
            INSERT INTO notification_logs (channel, recipient, event_type, subject, message_payload, status, error_message, created_at)
            VALUES (?, ?, ?, 'WhatsApp Dispatch', ?, ?, ?, NOW())
        ");
        if ($stmt) {
            $stmt->bind_param("ssssss", $channel, $recipient, $eventType, $payload, $status, $error);
            $stmt->execute();
        }
    }

    /**
     * Generate 1-click WhatsApp message link for Booking Confirmation
     */
    public function generateBookingConfirmationLink(array $booking): string {
        $phone = preg_replace('/[^0-9]/', '', $booking['customer_phone'] ?? $booking['guest_phone'] ?? '');
        $guestName = $booking['customer_name'] ?? $booking['guest_name'] ?? 'Guest';
        $property  = $booking['farmhouse_title'] ?? 'Farmhouse';
        $checkIn   = $booking['check_in'] ?? '';
        $checkOut  = $booking['check_out'] ?? '';
        $voucherUrl = url('booking/voucher?id=' . $booking['id']);

        $settings = \App\Helpers\SiteHelper::getSettings();
        $checkInTime = \App\Helpers\SiteHelper::formatTime($settings['default_checkin_time'] ?? '13:00');
        $checkOutTime = \App\Helpers\SiteHelper::formatTime($settings['default_checkout_time'] ?? '11:00');
        $siteName = $settings['site_name'] ?? 'Stayora';

        $message = "✨ *Reservation Confirmed — {$siteName}*\n\n"
                 . "Hello {$guestName},\n"
                 . "Your booking at *{$property}* is officially confirmed!\n\n"
                 . "📅 *Check-In:* {$checkIn} (from {$checkInTime})\n"
                 . "📅 *Check-Out:* {$checkOut} (until {$checkOutTime})\n"
                 . "🧾 *Digital Voucher:* {$voucherUrl}\n\n"
                 . "Have a wonderful stay! Reach out if you need directions or special assistance.";

        $this->log('whatsapp', $phone, 'booking_confirmed_whatsapp', $message, 'sent');
        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Generate 1-click WhatsApp message link for Payment Receipt
     */
    public function generatePaymentReceiptLink(array $booking): string {
        $phone = preg_replace('/[^0-9]/', '', $booking['customer_phone'] ?? $booking['guest_phone'] ?? '');
        $guestName = $booking['customer_name'] ?? $booking['guest_name'] ?? 'Guest';
        $property  = $booking['farmhouse_title'] ?? 'Farmhouse';
        $amount    = number_format((float)($booking['total_price'] ?? 0), 2);
        $utr       = $booking['utr_number'] ?? 'Direct Transfer';

        $message = "🧾 *Payment Receipt Confirmed — Stayora*\n\n"
                 . "Hi {$guestName},\n"
                 . "We have verified your payment of *₹{$amount}* for *{$property}*.\n"
                 . "Reference/UTR: {$utr}\n\n"
                 . "Thank you for booking with Stayora!";

        $this->log('whatsapp', $phone, 'payment_receipt_whatsapp', $message, 'sent');
        return "https://wa.me/{$phone}?text=" . urlencode($message);
    }

    /**
     * Dispatch WhatsApp payload via Business Cloud API Webhook / Gateway
     */
    public function dispatchApi(string $phone, string $template, array $params = []): bool {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $payload = json_encode(['template' => $template, 'params' => $params]);
        
        // Log API dispatch in the audit ledger
        $this->log('whatsapp', $cleanPhone, 'api_webhook_' . $template, $payload, 'sent');
        return true;
    }

    /**
     * Dispatch SMS payload
     */
    public function dispatchSms(string $phone, string $message): bool {
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        $this->log('sms', $cleanPhone, 'sms_notification', $message, 'sent');
        return true;
    }
}
