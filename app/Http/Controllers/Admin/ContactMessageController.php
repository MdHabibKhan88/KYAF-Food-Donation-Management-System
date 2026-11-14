<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\Audit;
use Illuminate\Http\Request;

class ContactMessageController extends Controller
{
    public function index(Request $request)
    {
        $q = ContactMessage::with('handler');
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $t = '%'.$request->q.'%';
            $q->where(fn ($w) => $w->where('sender_name', 'like', $t)->orWhere('subject', 'like', $t)->orWhere('email', 'like', $t));
        }

        return view('admin.messages', ['messages' => $q->latest('message_id')->paginate(15)->withQueryString()]);
    }

    public function show(ContactMessage $message)
    {
        return view('admin.message-show', ['m' => $message->load('handler')]);
    }

    public function update(Request $request, ContactMessage $message)
    {
        $d = $request->validate(['status' => 'required|in:New,InProgress,Resolved']);
        $message->update(['status' => $d['status'], 'handled_by' => $request->user()->user_id]);
        Audit::log('CONTACT_UPDATE', 'ContactMessage', $message->message_id, null, ['status' => $d['status']]);

        return back()->with('success', 'বার্তার অবস্থা হালনাগাদ হয়েছে।');
    }
}
