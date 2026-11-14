<?php

namespace App\Http\Controllers;

use App\Models\Donation;
use App\Services\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/** Donation photos live on the private disk and are served only to people who may see the donation. */
class MediaController extends Controller
{
    public function donationPhoto(Request $request, Donation $donation)
    {
        $user = $request->user();
        $allowed = match ($user->roleName()) {
            'ADMIN' => true,
            'DONOR' => optional($donation->donor)->user_id === $user->user_id,
            'AREA_MANAGER' => optional(optional($user->manager)->serviceArea)->area_id === $donation->area_id,
            'STAFF' => optional($user->staff)->staff_id !== null && $donation->assigned_staff_id === $user->staff->staff_id,
            default => false,
        };
        if (! $allowed) {
            Audit::log('ACCESS_DENIED', 'Donation', $donation->donation_id, null, ['what' => 'photo'], 'Failure');
            abort(403);
        }
        abort_unless($donation->photo_path && Storage::disk('local')->exists($donation->photo_path), 404);

        return Storage::disk('local')->response($donation->photo_path);
    }
}
