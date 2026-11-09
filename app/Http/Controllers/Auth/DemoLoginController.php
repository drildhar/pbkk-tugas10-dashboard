<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DemoLoginController extends Controller
{
    /**
     * Auto-login instan untuk akun demo (whitelist dari config/demo.php).
     */
    public function __invoke(Request $request, string $panel): RedirectResponse
    {
        // Fitur demo bisa dimatikan total lewat env DEMO_LOGIN_ENABLED=false.
        abort_unless((bool) config('demo.enabled'), 404);

        $accounts = config('demo.accounts');
        abort_unless(is_array($accounts) && array_key_exists($panel, $accounts), 404);

        $email = (string) ($accounts[$panel]['email'] ?? '');

        /** @var User|null $user */
        $user = User::query()->where('email', $email)->first();

        abort_if($user === null, 404, 'Akun demo belum tersedia. Jalankan: php artisan migrate:fresh --seed');

        Auth::login($user, remember: false);
        $request->session()->regenerate();

        return redirect()
            ->intended(route($user->homeRouteName(), absolute: false))
            ->with('success', "Quick login berhasil sebagai {$accounts[$panel]['label']}.");
    }
}
