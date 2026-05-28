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
        ErpNotification::syncOperationalChecks();
        $category = request('category', 'all');
        $status = request('status', ErpNotification::STATUS_OPEN);
        $categoryLabels = ErpNotification::categoryLabels();
        $statusLabels = ErpNotification::statusLabels();

        $baseQuery = ErpNotification::query();
        $notifications = (clone $baseQuery)
            ->when($category !== 'all', fn($q) => $q->where('category', $category))
            ->when($status !== 'all', fn($q) => $q->where('status', $status))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        $categoryCounts = (clone $baseQuery)
            ->where('status', ErpNotification::STATUS_OPEN)
            ->selectRaw('category, COUNT(*) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        $statusCounts = (clone $baseQuery)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.notifications.index', compact(
            'notifications',
            'category',
            'status',
            'categoryLabels',
            'statusLabels',
            'categoryCounts',
            'statusCounts'
        ));
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
        return response()->json(['ok' => true, 'unread' => ErpNotification::open()->count()]);
    }

    /** Số thông báo chưa đọc (dùng cho bell icon) */
    public function unreadCount()
    {
        ErpNotification::syncOperationalChecks();
        return response()->json(['count' => ErpNotification::open()->count()]);
    }

    public function updateStatus(Request $request, ErpNotification $notification)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', array_keys(ErpNotification::statusLabels()))],
        ]);

        $notification->markStatus($data['status']);

        return back()->with('success', 'Đã cập nhật trạng thái thông báo.');
    }

    public function destroy(ErpNotification $notification)
    {
        $notification->delete();
        return redirect()->back()->with('success', 'Đã xóa thông báo.');
    }
}
