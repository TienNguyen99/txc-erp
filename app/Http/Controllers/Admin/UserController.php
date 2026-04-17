<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $data = User::when($request->search, fn($q, $s) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"))
                    ->latest()->paginate(15)->withQueryString();
        return view('admin.users.index', compact('data'));
    }

    public function create()
    {
        return view('admin.users.form');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
            'role'     => 'required|in:admin,staff,manager',
        ]);
        $role = $validated['role'];
        unset($validated['role']); // Không lưu vào fillable của Spatie
        $validated['password'] = bcrypt($validated['password']);
        $user = User::create($validated);
        $user->assignRole($role);  // Spatie
        return redirect()->route('admin.users.index')->with('success', 'Thêm user thành công.');
    }

    public function edit(User $user)
    {
        $user->load('roles.permissions'); // eager load để tránh N+1
        $modules = RolePermissionSeeder::$modules;
        $userDirectPermissions = $user->getDirectPermissions()->pluck('name')->toArray();
        return view('admin.users.form', compact('user', 'modules', 'userDirectPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:6|confirmed',
            'role'     => 'required|in:admin,staff,manager',
        ]);
        $role = $validated['role'];
        unset($validated['role']); // Không lưu vào fillable của Spatie
        if (!empty($validated['password'])) {
            $validated['password'] = bcrypt($validated['password']);
        } else {
            unset($validated['password']);
        }
        $user->update($validated);
        $user->syncRoles([$role]); // Spatie — xóa role cũ, gán role mới
        return redirect()->route('admin.users.index')->with('success', 'Cập nhật user thành công.');
    }

    /**
     * Lưu danh sách direct permissions của user (ghi đè riêng, ngoài role).
     */
    public function syncPermissions(Request $request, User $user)
    {
        $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // syncPermissions chỉ thay thế direct permissions, không đụng roles
        $user->syncPermissions($request->input('permissions', []));

        return redirect()
            ->route('admin.users.edit', $user)
            ->with('success', "Đã cập nhật quyền riêng cho {$user->name}.");
    }

    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('admin.users.index')->with('success', 'Xóa user thành công.');
    }
}
