<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\Auth\InvitationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\Donor;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\Manager;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Staff;
use App\Http\Controllers\StaffApplicationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/* ---------- Public ---------- */
Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/about', [PublicController::class, 'about'])->name('about');
Route::get('/find-food', [PublicController::class, 'findFood'])->name('find-food');
Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:20,1')->name('contact.store');
Route::get('/become-staff', [StaffApplicationController::class, 'create'])->name('staff.apply');
Route::post('/become-staff', [StaffApplicationController::class, 'store'])->middleware('throttle:20,1')->name('staff.apply.store');

Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('divisions', [LocationController::class, 'divisions'])->name('divisions');
    Route::get('districts', [LocationController::class, 'districts'])->name('districts');
    Route::get('upazilas', [LocationController::class, 'upazilas'])->name('upazilas');
    Route::get('thanas', [LocationController::class, 'thanas'])->name('thanas');
    Route::get('chain', [LocationController::class, 'chain'])->name('chain');
});

/* ---------- Guest authentication ---------- */
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'chooser'])->name('login');
    Route::get('/login/{role}', [LoginController::class, 'show'])->whereIn('role', ['admin', 'manager', 'staff', 'donor'])->name('login.show');
    Route::post('/login/{role}', [LoginController::class, 'store'])->whereIn('role', ['admin', 'manager', 'staff', 'donor'])->middleware('throttle:60,1')->name('login.store');
    Route::get('/register', [RegisterController::class, 'show'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:20,1')->name('register.store');
    Route::get('/forgot-password', [PasswordController::class, 'forgotForm'])->name('password.forgot');
    Route::post('/forgot-password', [PasswordController::class, 'sendLink'])->middleware('throttle:10,1')->name('password.send');
    Route::get('/reset-password/{token}', [PasswordController::class, 'resetForm'])->name('password.reset');
    Route::post('/reset-password/{token}', [PasswordController::class, 'reset'])->name('password.update');
    Route::get('/staff-invitation/{token}', [InvitationController::class, 'show'])->name('invitation.show');
    Route::post('/staff-invitation/{token}', [InvitationController::class, 'store'])->name('invitation.store');
});
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

/* ---------- Any authenticated user ---------- */
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function (Request $request) {
        return redirect()->route(match ($request->user()->roleName()) {
            'ADMIN' => 'admin.dashboard',
            'AREA_MANAGER' => 'manager.dashboard',
            'STAFF' => 'staff.dashboard',
            default => 'donor.dashboard',
        });
    })->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/profile/photo', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'deletePhoto'])->name('profile.photo.delete');
    
    Route::put('/profile/password', [PasswordController::class, 'change'])->name('password.change');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'readAll'])->name('notifications.readAll');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'read'])->name('notifications.read');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    Route::get('/donations/{donation}/photo', [MediaController::class, 'donationPhoto'])->name('donations.photo');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
});

/* ---------- Donor ---------- */
Route::middleware(['auth', 'role:DONOR'])->prefix('donor')->name('donor.')->group(function () {
    Route::get('/dashboard', [Donor\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/donations', [Donor\DonationController::class, 'index'])->name('donations');
    Route::get('/donate', [Donor\DonationController::class, 'create'])->name('donations.create');
    Route::post('/donate', [Donor\DonationController::class, 'store'])->name('donations.store');
    Route::get('/donations/{donation}', [Donor\DonationController::class, 'show'])->name('donations.show');
    Route::post('/donations/{donation}/cancel', [Donor\DonationController::class, 'cancel'])->name('donations.cancel');
});

/* ---------- Staff ---------- */
Route::middleware(['auth', 'role:STAFF'])->prefix('staff')->name('staff.')->group(function () {
    Route::get('/dashboard', [Staff\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/tasks', [Staff\TaskController::class, 'index'])->name('tasks');
    Route::get('/history', [Staff\TaskController::class, 'index'])->name('history');
    Route::get('/tasks/{donation}', [Staff\TaskController::class, 'show'])->name('tasks.show');
    Route::post('/tasks/{donation}/verify', [Staff\TaskController::class, 'verify'])->name('tasks.verify');
    Route::post('/tasks/{donation}/collect', [Staff\TaskController::class, 'collect'])->name('tasks.collect');
    Route::post('/tasks/{donation}/distributions', [Staff\TaskController::class, 'planDistribution'])->name('distributions.plan');
    Route::post('/tasks/{donation}/finish', [Staff\TaskController::class, 'finish'])->name('tasks.finish');
    Route::post('/distributions/{distribution}/complete', [Staff\TaskController::class, 'completeBatch'])->name('distributions.complete');
    Route::post('/distributions/{distribution}/cancel', [Staff\TaskController::class, 'cancelBatch'])->name('distributions.cancel');
});

/* ---------- Area Manager ---------- */
Route::middleware(['auth', 'role:AREA_MANAGER'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [Manager\DashboardController::class, 'index'])->name('dashboard');
    Route::get('/donations', [Manager\DonationController::class, 'index'])->name('donations');
    Route::get('/donations/{donation}', [Manager\DonationController::class, 'show'])->name('donations.show');
    Route::post('/donations/{donation}/assign', [Manager\DonationController::class, 'assign'])->name('donations.assign');
    Route::post('/donations/{donation}/decide', [Manager\DonationController::class, 'decide'])->name('donations.decide');
    Route::post('/donations/{donation}/close', [Manager\DonationController::class, 'close'])->name('donations.close');

    Route::get('/applications', [Manager\ApplicationController::class, 'index'])->name('applications');
    Route::get('/applications/{application}', [Manager\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/review', [Manager\ApplicationController::class, 'review'])->name('applications.review');
    Route::post('/applications/{application}/approve', [Manager\ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [Manager\ApplicationController::class, 'reject'])->name('applications.reject');
    Route::get('/applications/{application}/cv', [Manager\ApplicationController::class, 'cv'])->name('applications.cv');

    Route::get('/team', [Manager\TeamController::class, 'index'])->name('team');
    Route::post('/team/{staff}', [Manager\TeamController::class, 'update'])->name('team.update');
});

/* ---------- Admin ---------- */
Route::middleware(['auth', 'role:ADMIN'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [Admin\DashboardController::class, 'index'])->name('dashboard');

    Route::get('/users', [Admin\UserController::class, 'index'])->name('users');
    Route::get('/users/create/{type}', [Admin\UserController::class, 'create'])->name('users.create');
    Route::post('/users/create/{type}', [Admin\UserController::class, 'store'])->name('users.store');
    Route::post('/users/{user}/toggle', [Admin\UserController::class, 'toggle'])->name('users.toggle');
    Route::post('/users/{user}/unlock', [Admin\UserController::class, 'unlock'])->name('users.unlock');
    Route::get('/roles', [Admin\RoleController::class, 'index'])->name('roles');

    Route::get('/areas', [Admin\ServiceAreaController::class, 'index'])->name('areas');
    Route::get('/areas/create', [Admin\ServiceAreaController::class, 'create'])->name('areas.create');
    Route::post('/areas', [Admin\ServiceAreaController::class, 'store'])->name('areas.store');
    Route::get('/areas/{area}/edit', [Admin\ServiceAreaController::class, 'edit'])->name('areas.edit');
    Route::put('/areas/{area}', [Admin\ServiceAreaController::class, 'update'])->name('areas.update');

    Route::get('/locations', [Admin\LocationController::class, 'index'])->name('locations');
    Route::post('/locations/import', [Admin\LocationController::class, 'import'])->name('locations.import');
    Route::post('/locations/{level}', [Admin\LocationController::class, 'store'])->name('locations.store');
    Route::put('/locations/{level}/{id}', [Admin\LocationController::class, 'update'])->name('locations.update');
    Route::delete('/locations/{level}/{id}', [Admin\LocationController::class, 'destroy'])->name('locations.destroy');

    Route::get('/donations', [Admin\DonationController::class, 'index'])->name('donations');
    Route::get('/donations/{donation}', [Admin\DonationController::class, 'show'])->name('donations.show');
    Route::post('/donations/{donation}/assign', [Admin\DonationController::class, 'assign'])->name('donations.assign');
    Route::post('/donations/{donation}/decide', [Admin\DonationController::class, 'decide'])->name('donations.decide');
    Route::post('/donations/{donation}/close', [Admin\DonationController::class, 'close'])->name('donations.close');
    Route::post('/donations/{donation}/cancel', [Admin\DonationController::class, 'cancel'])->name('donations.cancel');

    Route::get('/applications', [Admin\ApplicationController::class, 'index'])->name('applications');
    Route::get('/applications/{application}', [Admin\ApplicationController::class, 'show'])->name('applications.show');
    Route::post('/applications/{application}/approve', [Admin\ApplicationController::class, 'approve'])->name('applications.approve');
    Route::post('/applications/{application}/reject', [Admin\ApplicationController::class, 'reject'])->name('applications.reject');
    Route::get('/applications/{application}/cv', [Admin\ApplicationController::class, 'cv'])->name('applications.cv');

    Route::get('/audit-logs', [Admin\AuditLogController::class, 'index'])->name('audit');
    Route::get('/settings', [Admin\SettingController::class, 'edit'])->name('settings');
    Route::put('/settings', [Admin\SettingController::class, 'update'])->name('settings.update');
    Route::get('/messages', [Admin\ContactMessageController::class, 'index'])->name('messages');
    Route::get('/messages/{message}', [Admin\ContactMessageController::class, 'show'])->name('messages.show');
    Route::put('/messages/{message}', [Admin\ContactMessageController::class, 'update'])->name('messages.update');
});


Route::get('/db-ssl-check', function () {
    $path = env('MYSQL_ATTR_SSL_CA');

    return response()->json([
        'ca_path' => $path,
        'file_exists' => $path ? file_exists($path) : false,
        'file_readable' => $path ? is_readable($path) : false,
        'pdo_mysql' => extension_loaded('pdo_mysql'),
    ]);
});