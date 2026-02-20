<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    public function login(Request $request)
    {
        $validated = $request->validate([
            'id' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['nullable'],
        ]);

        $loginValue = trim($validated['id']);
        $password   = $validated['password'];
        $remember   = (bool) $request->boolean('remember');

        // 1) If user typed a numeric ID, treat it as users.id (primary key)
        if (ctype_digit($loginValue)) {
            $user = User::where('id', (int) $loginValue)->first();

            if ($user && Hash::check($password, $user->password)) {
                Auth::login($user, $remember);
                $request->session()->regenerate();
                $audit = new AuditLog();
                $audit->user_id = $user->id;
                $audit->event = 'Login';
                $audit->entity_type = 'User';
                $audit->details = 'Successful login';
                $audit->save();
                $audit->record='LOG-'.str_pad($user->id, 5, '0', STR_PAD_LEFT).'-'.$audit->id;
                $audit->save(); 
                return redirect()->intended($this->redirectByRole($user));
            }
        }
        throw ValidationException::withMessages([
            'id' => 'Invalid credentials.',
        ]);
    }

    public function logout()
    {
        $user = Auth::user();
         $audit = new AuditLog();
                $audit->user_id = $user->id;
                $audit->event = 'Logout';
                $audit->entity_type = 'User';
                $audit->details = 'Successful logout';
                $audit->save();
                $audit->record='LOG-'.str_pad($user->id, 5, '0', STR_PAD_LEFT).'-'.$audit->id;
                $audit->save();

        Auth::logout();
        return redirect()->route('login');
    }

    private function redirectByRole(?User $user): string
    {
        if (!$user) return '/login';

        return match ($user->role) {
            'owner'  => route('dashboard'),
            'admin'  => route('dashboard'),
            'client' => route('client.contracts'), // change this to your client landing route later
            default  => route('dashboard'),
        };
    }
}