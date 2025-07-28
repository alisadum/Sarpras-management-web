<?php

namespace App\Traits;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

trait NotificationTrait
{
    /**
     * Send a notification to a user.
     *
     * @param int $userId
     * @param string $title
     * @param string $message
     * @param string $type
     * @param int|null $borrowId
     * @return void
     */
    protected function sendNotification($userId, $title, $message, $type, $borrowId = null)
    {
        try {
            Notification::create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'borrow_id' => $borrowId,
                'tanggal_notif' => now(),
                'is_read' => false,
            ]);
            Log::info("Notifikasi dikirim ke user ID {$userId}: {$title} - {$message}");
        } catch (\Exception $e) {
            Log::error("Gagal mengirim notifikasi ke user ID {$userId}: {$e->getMessage()}");
        }
    }
}
