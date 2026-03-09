<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class WorkerPortalController extends Controller
{
    public function home()
    {
        $worker = auth()->user();
        $contracts = $worker->workerContracts()->with('payments', 'project')->get();
        return view('worker.home', compact('worker', 'contracts'));
    }

    public function contractsList()
    {
        $worker    = auth()->user();
        $contracts = $worker->workerContracts()->with('payments', 'project')->orderByDesc('contract_date')->get();
        return view('worker.contracts', compact('contracts'));
    }

    public function contractDetail(\App\Models\WorkerContract $contract)
    {
        abort_unless($contract->worker_user_id === auth()->id(), 403);
        $contract->load('payments', 'project');
        return view('worker.contract-detail', compact('contract'));
    }

    public function paymentsList()
    {
        $worker   = auth()->user();
        $payments = \App\Models\WorkerPayment::whereHas('contract', function ($q) use ($worker) {
            $q->where('worker_user_id', $worker->id);
        })->with('contract.project')->orderBy('due_date')->get();

        $totalPaid    = $payments->where('status', 'paid')->sum('amount');
        $totalPending = $payments->where('status', 'pending')->sum('amount');

        return view('worker.payments', compact('payments', 'totalPaid', 'totalPending'));
    }

    public function viewContractPdf(\App\Models\WorkerContract $contract)
    {
        abort_unless($contract->worker_user_id === auth()->id(), 403);
        abort_unless($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path), 404);
        return response()->file(Storage::disk('public')->path($contract->pdf_path));
    }

    public function downloadContractPdf(\App\Models\WorkerContract $contract)
    {
        abort_unless($contract->worker_user_id === auth()->id(), 403);
        abort_unless($contract->pdf_path && Storage::disk('public')->exists($contract->pdf_path), 404);
        return response()->download(Storage::disk('public')->path($contract->pdf_path), "Contract-{$contract->id}.pdf");
    }

    public function downloadReceipt(\App\Models\WorkerPayment $payment)
    {
        abort_unless($payment->contract->worker_user_id === auth()->id(), 403);
        abort_unless($payment->receipt_path && Storage::disk('public')->exists($payment->receipt_path), 404);
        return response()->download(Storage::disk('public')->path($payment->receipt_path), "Receipt-{$payment->payment_number}.pdf");
    }

    public function settings()
    {
        return view('worker.settings', ['worker' => auth()->user()]);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
        ]);
        $user->update($data);

        $a = new AuditLog();
        $a->user_id = $user->id; $a->event = 'Update';
        $a->entity_type = 'Worker Profile';
        $a->details = "Worker {$user->name} updated their profile.";
        $a->save(); $a->record = 'WRK-' . str_pad($user->id, 5, '0', STR_PAD_LEFT) . '-' . $a->id; $a->save();

        return back()->with('success', 'Profile updated.');
    }

    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password'         => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (!Hash::check($data['current_password'], $user->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }
        $user->password = $data['password'];
        $user->save();

        $a = new AuditLog();
        $a->user_id = $user->id; $a->event = 'Update';
        $a->entity_type = 'Worker Password';
        $a->details = "Worker {$user->name} changed their password.";
        $a->save(); $a->record = 'WRK-' . str_pad($user->id, 5, '0', STR_PAD_LEFT) . '-' . $a->id; $a->save();

        return back()->with('success', 'Password updated.');
    }

    public function updateAvatar(Request $request)
    {
        $user = auth()->user();
        $data = $request->validate(['avatar' => ['required', 'image', 'max:2048']]);

        if ($user->avatar && str_starts_with($user->avatar, '/storage/')) {
            $old = ltrim(str_replace('/storage/', '', $user->avatar), '/');
            if ($old) Storage::disk('public')->delete($old);
        }

        $path = $data['avatar']->store('avatars', 'public');
        $user->avatar = '/storage/' . $path;
        $user->save();

        $a = new AuditLog();
        $a->user_id = $user->id; $a->event = 'Update';
        $a->entity_type = 'Worker Avatar';
        $a->details = "Worker {$user->name} updated their avatar.";
        $a->save(); $a->record = 'WRK-' . str_pad($user->id, 5, '0', STR_PAD_LEFT) . '-' . $a->id; $a->save();

        return back()->with('success', 'Avatar updated.');
    }

    public function markNotificationRead(\App\Models\UserNotification $notification)
    {
        abort_unless($notification->user_id === auth()->id(), 403);
        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }
        return back();
    }
}