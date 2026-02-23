@extends('client.layout')

@section('title', 'Client • Settings')

@section('content')
<section class="client-page" aria-label="Client settings">
    <div class="client-page__header">
        <h2>Settings</h2>
        <a class="client-back" href="{{ route('client.contracts') }}">← Contracts</a>
    </div>

    <div class="form-card" style="margin-bottom:16px;">
        <div style="display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap;align-items:center;margin-bottom:12px;">
            <div>
                <div style="font-weight:900; font-size:16px;">Personal Information</div>
                <div style="opacity:.85; font-size:13px;">Update your name, phone, email, and avatar.</div>
            </div>
            <div style="display:flex; gap:10px; align-items:center;">
                <img src="{{ $user->avatar }}" alt="" style="width:44px;height:44px;border-radius:999px;object-fit:cover; border:2px solid rgba(15,23,42,.15);" />
                <span style="font-weight:900;">{{ $user->name }}</span>
            </div>
        </div>

        <form method="POST" action="{{ route('client.settings.profile.update') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label for="name">Full name</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required>
                </div>
                <div class="field">
                    <label for="phone">Phone</label>
                    <input id="phone" name="phone" type="text" value="{{ old('phone', $user->phone) }}" required>
                </div>
                <div class="field" style="grid-column: span 2;">
                    <label for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                </div>
            </div>
            <div style="margin-top:12px;">
                <button class="btn btn-primary" type="submit">Save Changes</button>
            </div>
        </form>
    </div>

    <div class="form-card" style="margin-bottom:16px;">
        <div style="font-weight:900; font-size:16px; margin-bottom:10px;">Avatar</div>
        <form method="POST" action="{{ route('client.settings.avatar.update') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-grid">
                <div class="field" style="grid-column: span 2;">
                    <label for="avatar">Upload new avatar</label>
                    <input id="avatar" name="avatar" type="file" accept="image/*" required>
                </div>
            </div>
            <div style="margin-top:12px;">
                <button class="btn btn-primary" type="submit">Update Avatar</button>
            </div>
        </form>
    </div>

    <div class="form-card">
        <div style="font-weight:900; font-size:16px;">Security</div>
        <div style="opacity:.85; font-size:13px; margin-bottom:12px;">Change your password.</div>
        <form method="POST" action="{{ route('client.settings.password.update') }}">
            @csrf
            <div class="form-grid">
                <div class="field">
                    <label for="current_password">Current password</label>
                    <input id="current_password" name="current_password" type="password" required>
                </div>
                <div class="field">
                    <label for="password">New password</label>
                    <input id="password" name="password" type="password" minlength="8" required>
                </div>
                <div class="field" style="grid-column: span 2;">
                    <label for="password_confirmation">Confirm new password</label>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required>
                </div>
            </div>
            <div style="margin-top:12px;">
                <button class="btn btn-primary" type="submit">Update Password</button>
            </div>
        </form>
    </div>
</section>
@endsection
