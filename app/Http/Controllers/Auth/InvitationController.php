<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\StaffApplication;
use App\Models\UserAccount;
use App\Rules\StrongPassword;
use App\Services\Audit;
use App\Services\Notifier;
use App\Services\PasswordPolicy;
use App\Services\TeamService;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** BR-22: staff accounts are created only from an approved application via a single-use, 72-hour invitation. */
class InvitationController extends Controller
{
    public function show(string $token)
    {
        $app = $this->find($token);

        return view('auth.invitation', ['app' => $app, 'token' => $token]);
    }

    public function store(Request $request, string $token, TeamService $team)
    {
        $app = $this->find($token);
        if (! $app) {
            return redirect()->route('login')->with('error', 'আমন্ত্রণ লিংকটি অবৈধ, ব্যবহৃত বা মেয়াদোত্তীর্ণ।');
        }
        $data = $request->validate(['password' => ['required', 'confirmed', new StrongPassword], 'terms' => 'accepted'],
            ['terms.accepted' => 'শর্তাবলি গ্রহণ করতে হবে।']);

        if (UserAccount::where('email', strtolower($app->email))->orWhere('phone', $app->phone)->exists()) {
            return redirect()->route('login')->with('error', 'এই ইমেইল বা মোবাইল নম্বর দিয়ে ইতোমধ্যে অ্যাকাউন্ট আছে।');
        }

        try {
            $user = DB::transaction(function () use ($app, $data, $team) {
                $manager = $app->area->manager;
                if (! $manager || $manager->status !== 'Active') {
                    throw new DomainException('এই এলাকায় বর্তমানে কোনো সক্রিয় এরিয়া ম্যানেজার নেই।');
                }
                $user = UserAccount::create([
                    'role_id' => Role::where('role_name', Role::STAFF)->value('role_id'),
                    'full_name' => $app->applicant_name, 'email' => strtolower($app->email), 'phone' => $app->phone,
                    'password_hash' => 'pending', 'status' => 'Active',
                ]);
                PasswordPolicy::set($user, $data['password']);
                $team->addStaff($manager, $user->user_id, $app->application_id);
                $app->update(['status' => 'Registered', 'invitation_token_hash' => null]);

                return $user;
            });
        } catch (DomainException $e) {
            return back()->with('error', $e->getMessage());
        }

        Audit::log('STAFF_REGISTER', 'UserAccount', $user->user_id, null, ['application' => $app->application_id], 'Success', $user->user_id);
        Notifier::to($app->area->manager->user_id, 'STAFF_JOINED', 'নতুন স্টাফ যোগ দিয়েছেন', "{$app->applicant_name} আপনার দলে যোগ দিয়েছেন।");

        return redirect()->route('login.show', 'staff')->with('success', 'অ্যাকাউন্ট তৈরি হয়েছে! এখন লগইন করুন।');
    }

    private function find(string $token): ?StaffApplication
    {
        $app = StaffApplication::with('area.manager')->where('invitation_token_hash', hash('sha256', $token))->first();
        if (! $app || $app->status !== 'Approved') {
            return null;
        }
        if ($app->invitation_expires_at->isPast()) {
            $app->update(['status' => 'Expired', 'invitation_token_hash' => null]);
            Audit::log('INVITATION_EXPIRED', 'StaffApplication', $app->application_id);

            return null;
        }

        return $app;
    }
}
