<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'error' => 'Unauthenticated'], 401);
        }

        $notifications = Notification::with(['borrow.barang'])
            ->where('user_id', $user->id)
            ->withoutTrashed()
            ->select('id', 'user_id', 'borrow_id', 'title', 'message', 'type', 'tanggal_notif', 'is_read')
            ->orderBy('tanggal_notif', 'desc')
            ->paginate($request->input('per_page', 10));

        return response()->json([
            'status' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'user_id' => $notification->user_id,
                    'borrow_id' => $notification->borrow_id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type,
                    'tanggal_notif' => Carbon::parse($notification->tanggal_notif)->format('Y-m-d H:i:s'),
                    'is_read' => $notification->is_read,
                    'barang' => $notification->borrow && $notification->borrow->barang ? [
                        'id' => $notification->borrow->barang->id,
                        'nama' => $notification->borrow->barang->nama,
                    ] : null,
                ];
            })->toArray(),
            'pagination' => [
                'current_page' => $notifications->currentPage(),
                'total_pages' => $notifications->lastPage(),
                'total_items' => $notifications->total(),
                'per_page' => $notifications->perPage(),
            ]
        ]);
    }

    public function markAsRead(Request $request, int $id)
    {
        $user = auth('sanctum')->user();
        if (!$user) {
            return response()->json(['status' => false, 'error' => 'Unauthenticated'], 401);
        }

        if ($id <= 0) {
            return response()->json(['status' => false, 'error' => 'Invalid notification ID'], 400);
        }

        $notification = Notification::where('id', $id)
            ->where('user_id', $user->id)
            ->withoutTrashed()
            ->first();

        if (!$notification) {
            return response()->json(['status' => false, 'error' => 'Notification not found'], 404);
        }

        $notification->is_read = true;
        $notification->save();

        return response()->json(['status' => true, 'message' => 'Notification marked as read']);
    }
}
