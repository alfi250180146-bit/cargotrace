<?php

namespace App\Http\Controllers;

use App\Models\Shipment;
use App\Models\ShipmentException;
use App\Models\TrackingEvent;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = [User::ROLE_ADMIN, User::ROLE_OPERATOR, User::ROLE_CUSTOMER];

    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', Rule::in(self::ROLES)],
        ]);

        $users = User::query()
            ->when($filters['q'] ?? null, fn ($query, $value) => $query->where(function ($inner) use ($value) {
                $inner->where('name', 'like', '%'.$value.'%')
                    ->orWhere('email', 'like', '%'.$value.'%');
            }))
            ->when($filters['role'] ?? null, fn ($query, $value) => $query->where('role', $value))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', [
            'users' => $users,
            'filters' => $filters,
            'roles' => self::ROLES,
        ]);
    }

    public function create()
    {
        return view('users.form', ['user' => new User(), 'roles' => self::ROLES]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        // role tidak masuk $fillable, jadi diisi langsung dari data yang sudah divalidasi
        $user = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
        $user->role = $data['role'];
        $user->email_verified_at = now();
        $user->save();

        return redirect()->route('users.index')->with('status', 'User created.');
    }

    public function edit(User $user)
    {
        return view('users.form', ['user' => $user, 'roles' => self::ROLES]);
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in(self::ROLES)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ]);

        // Mencegah admin mengunci dirinya sendiri
        if ($user->is($request->user()) && $data['role'] !== $user->role) {
            return back()->withErrors(['role' => 'You cannot change your own role.'])->withInput();
        }

        $user->fill(['name' => $data['name'], 'email' => $data['email']]);

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->role = $data['role'];
        $user->save();

        return redirect()->route('users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot delete your own account.']);
        }

        $inUse = Shipment::where('customer_id', $user->id)->orWhere('created_by', $user->id)->exists()
            || TrackingEvent::where('created_by', $user->id)->exists()
            || ShipmentException::where('created_by', $user->id)->exists();

        if ($inUse) {
            return back()->withErrors([
                'user' => 'This user is linked to shipments or tracking history and cannot be deleted.',
            ]);
        }

        $user->tokens()->delete();
        $user->notifications()->delete();
        $user->delete();

        return redirect()->route('users.index')->with('status', 'User deleted.');
    }
}