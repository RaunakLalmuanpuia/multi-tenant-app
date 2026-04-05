<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;

// ── Welcome ───────────────────────────────────────────────────────────
Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin'       => Route::has('login'),
        'canRegister'    => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion'     => PHP_VERSION,
    ]);
});

// ── Auth (guest) ──────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {

    Route::get('login', fn () => Inertia::render('Auth/Login', [
        'canResetPassword' => true,
        'status'           => session('status'),
    ]))->name('login');

    Route::post('login', function (LoginRequest $request) {
        $request->authenticate();
        $request->session()->regenerate();
        $user = $request->user();

        if ($user->hasRole('admin')) {
            return redirect()->intended(route('admin.dashboard'));
        }
        $business = $user->businesses()->first();
        return redirect()->intended(
            $business ? route('dashboard', ['business' => $business->id]) : route('profile.edit')
        );
    })->name('login.store');

    Route::get('register', fn () => Inertia::render('Auth/Register'))->name('register');

    Route::post('register', function (Request $request) {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|lowercase|email|max:255|unique:' . User::class,
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));
        Auth::login($user);

        $business = $user->businesses()->first();
        return redirect(
            $business ? route('dashboard', ['business' => $business->id]) : route('profile.edit')
        );
    });

});

// ── Auth (authenticated) ──────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    Route::post('logout', function (Request $request) {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    })->name('logout');

});

// ── Admin (no business context) ───────────────────────────────────────
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'adminIndex'])->name('admin.dashboard');
});

// ── Business-scoped ───────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'member'])
    ->prefix('b/{business}')
    ->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/settings/team', [TeamMemberController::class, 'index'])->name('team.index');
        Route::put('/settings/team/{user}', [TeamMemberController::class, 'update'])->name('team.update');
        Route::delete('/settings/team/{user}', [TeamMemberController::class, 'destroy'])->name('team.destroy');

        Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::delete('/roles/{role}', [RoleController::class, 'destroy'])->name('roles.destroy');

    });

// ── Profile (auth only, no business required) ─────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
