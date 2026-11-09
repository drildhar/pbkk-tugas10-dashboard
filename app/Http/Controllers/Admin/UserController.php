<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ResolvesTableQuery;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRoleRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\View\View;

class UserController extends Controller implements HasMiddleware
{
    use ResolvesTableQuery;

    /**
     * Manajemen pengguna hanya untuk admin.
     *
     * @return array<int, Middleware>
     */
    public static function middleware(): array
    {
        return [
            new Middleware('role:admin'),
            new Middleware('permission:kelola-pengguna'),
        ];
    }

    /**
     * Daftar pengguna beserta role-nya (filter role + pencarian nama/email).
     */
    public function index(Request $request): View
    {
        $filters = [
            'search' => $this->cleanSearch($request->input('search')),
            'role' => in_array($request->input('role'), ['admin', 'staf', 'umum'], true)
                ? (string) $request->input('role')
                : null,
        ];

        $users = User::query()
            ->with('roles')
            ->when($filters['search'], function (Builder $query, string $search): Builder {
                return $query->where(function (Builder $sub) use ($search): void {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] === 'umum', function (Builder $query): Builder {
                return $query->whereDoesntHave('roles');
            })
            ->when(in_array($filters['role'], ['admin', 'staf'], true), function (Builder $query) use ($filters): Builder {
                return $query->whereHas('roles', function (Builder $role) use ($filters): void {
                    $role->where('name', $filters['role']);
                });
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'filters' => $filters,
        ]);
    }

    /**
     * Mengubah role pengguna (khusus admin, tanpa hak mengubah role sendiri).
     */
    public function updateRole(UserRoleRequest $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Anda tidak diperbolehkan mengubah role akun sendiri.');
        }

        $user->syncRoles([$request->validated('role')]);

        return back()->with(
            'success',
            "Role pengguna {$user->name} berhasil diubah menjadi {$request->validated('role')}."
        );
    }
}
