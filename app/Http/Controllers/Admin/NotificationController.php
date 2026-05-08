<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ErpNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Danh sách thông báo + auto-check tồn kho */
    public function index()
    {
        ErpNotification::checkLowStock();
        $notifications = ErpNotification::latest()->paginate(30);
        return view('admin.notifications.index', compact('notifications'));
    }

    /** Đánh dấu đã đọc (AJAX) */
    public function markRead(Request $request)
    {
        $ids = $request->ids ?? [];
        if ($ids === 'all') {
            ErpNotification::where('is_read', false)->update(['is_read' => true]);
        } else {
            ErpNotification::whereIn('id', (array) $ids)->update(['is_read' => true]);
        }
        return response()->json(['ok' => true, 'unread' => ErpNotification::unread()->count()]);
    }

    /** Số thông báo chưa đọc (dùng cho bell icon) */
    public function unreadCount()
    {
        return response()->json(['count' => ErpNotification::unread()->count()]);
    }

    public function destroy(ErpNotification $notification)
    {
        $notification->delete();
        return redirect()->back()->with('success', 'Đã xóa thông báo.');
    }
}
