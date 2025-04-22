<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BorrowNotification;

class BorrowNotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        $account = $user->account;

        return $account->loggedBorrowNotifications()->where('is_read', 0)->orderBy('created_at', 'desc')->paginate(10000);
    }

    public function unreadCount(Request $request)
    {
        $user = Auth::user();
        
        $account = $user->account;

        return $account->loggedBorrowNotifications()->where('is_read', 0)->count();
    }

    public function markAsRead(Request $request, $id)
    {
        $user = Auth::user();
        BorrowNotification::markAsReadByUser($user->id);

        return response()->json(['message' => 'Notifications marked as read']);
    }

    public function markAllAsRead(Request $request)
    {
        $user = Auth::user();
        $account = $user->account;

        $account->loggedBorrowNotifications()
        ->where('is_read', 0)
        ->update([
            'is_read' => 1,
        ]);

        return response()->json(['message' => 'All notifications marked as read.']);
    }
}
