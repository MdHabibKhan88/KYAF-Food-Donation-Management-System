<?php

namespace App\Http\Controllers;

use App\Models\UserNotification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $items = UserNotification::where('user_id', $request->user()->user_id)->where('channel', 'InApp')
            ->latest('notification_id')->paginate(15);

        return view('shared.notifications', ['items' => $items]);
    }

    public function read(Request $request, UserNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->user_id, 403);
        $notification->update(['is_read' => true, 'read_at' => now()]);

        return back();
    }

    public function readAll(Request $request)
    {
        UserNotification::where('user_id', $request->user()->user_id)->where('is_read', false)->update(['is_read' => true, 'read_at' => now()]);

        return back()->with('success', 'সব বিজ্ঞপ্তি পঠিত হিসেবে চিহ্নিত হয়েছে।');
    }

    public function destroy(Request $request, UserNotification $notification)
    {
        abort_unless($notification->user_id === $request->user()->user_id, 403);
        $notification->delete();

        return back()->with('success', 'বিজ্ঞপ্তিটি মুছে ফেলা হয়েছে।');
    }
}
