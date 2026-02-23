<?php

namespace App\Http\Controllers;

use App\Mail\AdminCredentialsMail;
use App\Models\AuditLog;
use App\Models\Contract;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmployeesController extends Controller
{
    public function addEmployee(Request $request)
    {
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Create';
        $audit->entity_type='Employee';
        $audit->details='Creating employee ('.$request['emp_name'].') failed';
        $audit->save();
        $audit->record='EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        // Validate the incoming request data
        $fields = $request->validate([
            'emp_name' => 'required|string|max:255',
            'emp_email' => ['required', 'email', 'max:255'],
            'emp_phone' => ['required', 'string', 'max:30'],
            'emp_avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],

        ]);

        $rawPassword = Str::password(6);
        $fields['password'] = Hash::make($rawPassword);  
        $fields['created_by'] = auth()->id(); // Set the creator of the employee
        $fields['role']='admin';
        $fields['is_active']=true;
        $avatarPath = null;
        if ($request->hasFile('emp_avatar')) {
            $avatarPath = $request->file('emp_avatar')->store('avatars', 'public');
                $avatarPath='/storage/'.$avatarPath;
            }
        else{
            $avatarPath='/img/avatar-placeholder.png';
        }
        $fields['emp_avatar']=$avatarPath;
        $user=User::create([
            'name' => $fields['emp_name'],
            'email' => $fields['emp_email'],
            'phone' => $fields['emp_phone'],
            'avatar' => $fields['emp_avatar'],
            'password' => $fields['password'],
            'role' => $fields['role'],
            'is_active' => $fields['is_active'],
            'created_by' => $fields['created_by']
        ]);
        Mail::to($user->email)->send(
        new AdminCredentialsMail($user, $rawPassword)
    );
        // Redirect back with a success message
        $audit->details='Creating employee ('.$user->name.') succeeded';
        $audit->save();
        return redirect('/settings')->with('success', 'Employee added successfully!');
    }   
    public function deleteEmployee(User $user)
    {
        abort_if($user->id === auth()->id(), 403);
        abort_if($user->role === 'owner', 403);
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Delete';
        $audit->entity_type='Employee';
        $audit->details='Deleting employee ('.$user->name.') failed';
        $audit->save();
        $audit->record='EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        $contracts=Contract::where('created_by',$user->id)->get();
        $projects=Project::where('manager_user_id',$user->id)->get();
        $users=User::where('created_by',$user->id)->get();
        $audits=AuditLog::where('user_id',$user->id)->get();

        foreach($contracts as $contract){
            $contract->created_by = auth()->id();
            $contract->save();
        }
        foreach($projects as $project){
            $project->manager_user_id = auth()->id();
            $project->save();
        }
        foreach($users as $usr){
            $usr->created_by = auth()->id();
            $usr->save();
        }
        $user->delete();
        $audit->details='Deleting employee ('.$user->name.') succeeded';
        $audit->save();
        return response()->json(['message' => 'Employee deleted successfully']);
    }
    public function editEmployee(Request $request)
    {   

        $fields = $request->validate([
            'employee_id' => 'required|exists:users,id',
            'edit_emp_name' => 'required|string|max:255',
            'edit_emp_email' => ['required', 'email', 'max:255'],
            'edit_emp_phone' => ['required', 'string', 'max:30']
        ]);

        $user = User::findOrFail($fields['employee_id']);
        
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Employee';
        $audit->details='Updating employee ('.$user->name.') failed';
        $audit->save();
        $audit->record='EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        
        $user->name = $fields['edit_emp_name'];
        $user->email = $fields['edit_emp_email'];
        $user->phone = $fields['edit_emp_phone'];
        $user->save();
        $audit->details='Updating employee ('.$user->name.') succeeded';
        $audit->save();
        return redirect('/settings')->with('success', 'Employee updated successfully!');
}
public function editPassword(Request $request)
    {
        $base=[
            'employee_id' => 'required|exists:users,id',
            'new_password' => 'required|string|min:6|confirmed',
            'mode'         => ['required', 'in:self,admin'],
        ];
        if ($request->mode === 'self') {
        $base['old_password'] = ['required', 'string'];
        }
        $fields = $request->validate($base);
        
        $user = User::findOrFail($fields['employee_id']);
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Employee';
        $audit->details='Updating employee ('.$user->name.') password failed';
        $audit->save();
        $audit->record='EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        if ($fields['mode'] === 'self') {
        abort_unless($user->id === auth()->id(), 403);

        if (!Hash::check($fields['old_password'], $user->password)) {
            return redirect('/settings')->with('error', 'The old password is incorrect.');
        }
    } else {
        abort_unless(auth()->user()->role === 'owner', 403);
        
    }

    $user->update(['password' => Hash::make($fields['new_password'])]);
    $audit->details='Updating employee ('.$user->name.') password succeeded';
    $audit->save();
    return redirect('/settings')->with('success', 'Password updated successfully!');
}
public function editAvatar(Request $request)
    {
        abort_unless( auth()->id() === $request['profile_id'],403);
        $fields = $request->validate([
            'profile_id' => 'required|exists:users,id',
            'profile_avatar' => ['nullable','image','mimes:jpg,jpeg,png,webp','max:2048'],
        ]);

        $user = User::findOrFail($fields['profile_id']);
        
        $audit=new AuditLog();
        $audit->user_id=auth()->id();
        $audit->event='Update';
        $audit->entity_type='Profile Avatar';
        $audit->details='Updating profile ('.$user->name.') avatar failed';
        $audit->save();
        $audit->record='EMP-'.str_pad(auth()->id(), 5, '0', STR_PAD_LEFT).'-'.$audit->id;
        $audit->save();
        
        if ($request->hasFile('profile_avatar')) {
            $avatarPath = $request->file('profile_avatar')->store('avatars', 'public');
            $user->avatar = '/storage/'.$avatarPath;
            $user->save();
        }
        $audit->details='Updating profile ('.$user->name.') avatar succeeded';
        $audit->save();
        return redirect('/settings')->with('success', 'Avatar updated successfully!');
    }
}