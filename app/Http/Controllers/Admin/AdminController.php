<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Mail\ResetPasswordMail;
use App\Models\Admin;
use App\Models\SideMenu;
use App\Models\SubAdmin;
use App\Models\SubAdminPermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

    public function getdashboard()
    {
       
        return view('admin.index');
    }

   public function getProfile()
{
    if (Auth::guard('admin')->check()) {
        $data = Admin::find(Auth::guard('admin')->id());
    } elseif (Auth::guard('subadmin')->check()) {
        $data = SubAdmin::find(Auth::guard('subadmin')->id());
    } else {
        // Not authenticated — optionally redirect to login
        return redirect()->route('login')->with('error', 'Unauthorized access.');
    }

    return view('admin.auth.profile', compact('data'));
}

    // public function update_profile(Request $request)

    // {


    //     $data = $request->only(['name', 'email', 'phone']);



    //     if ($request->hasFile('image')) {

    //         $file = $request->file('image');

    //         $extension = $file->getClientOriginalExtension();

    //         $filename = time() . '.' . $extension;

    //         $file->move('public/admin/assets/images/admin', $filename);

    //         $data['image'] = 'public/admin/assets/images/admin/' . $filename;

    //     }

    //     if (Auth::guard('admin')->check()) {

    //         Admin::find(Auth::guard('admin')->id())->update($data);

    //     } else {

    //         SubAdmin::find(Auth::guard('subadmin')->id())->update($data);

    //     }

    //     return back()->with('success', 'Profile updated successfully');

    // }

public function update_profile(Request $request)
{
    $data = $request->only(['name', 'email', 'phone']);

    if ($request->hasFile('image')) {
        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();
        $filename = time() . '.' . $extension;
        $file->move('public/admin/assets/images/admin', $filename);
        $data['image'] = 'public/admin/assets/images/admin/' . $filename;
    }

    // Current logged in user
    $user = Auth::guard('admin')->check()
        ? Admin::find(Auth::guard('admin')->id())
        : SubAdmin::find(Auth::guard('subadmin')->id());

    // ✅ Password Change Logic (only if any password field is filled)
    if ($request->filled('old_password') || $request->filled('new_password') || $request->filled('confirm_password')) {
        $request->validate([
            'old_password' => 'required',
            'new_password' => 'required|min:8',
            'confirm_password' => 'required|same:new_password',
        ]);

        // Check old password
        if (!Hash::check($request->old_password, $user->password)) {
            return back()
                ->withErrors(['old_password' => 'Old password is incorrect'])
                ->withInput();
        }

        // ✅ Prevent reusing old password
        if (Hash::check($request->new_password, $user->password)) {
            return back()
                ->withErrors(['new_password' => 'This is your current password. Please enter a different password.'])
                ->withInput();
        }

        // Update new password
        $user->password = Hash::make($request->new_password);
    }

    // Update other profile data
    $user->update($data);

    return back()->with('success', 'Profile updated successfully');
}



    public function forgetPassword()
    {
        return view('admin.auth.forgetPassword');
    }
    public function adminResetPasswordLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $adminExists = DB::table('admins')->where('email', $request->email)->first();

        if (!$adminExists) {
            $subAdminExists = DB::table('sub_admins')->where('email', $request->email)->first();
        }

        if (!$adminExists && !$subAdminExists) {
            return back()->withErrors(['email' => 'The email address is not registered with any admin or subadmin.']);
        }

        $emailToUse = $adminExists ? $adminExists->email : $subAdminExists->email;

        $exists = DB::table('password_resets')->where('email', $emailToUse)->first();

        // $exists = DB::table('password_resets')->where('email', $request->email)->first();
        if ($exists) {
            return back()->with('error', 'Reset Password link has been already sent');
            // dd($subAdminExists);
        } else {
            $token = Str::random(30);
            DB::table('password_resets')->insert([
                'email' => $emailToUse,
                'token' => $token,
            ]);

            $data['url'] = url('change_password', $token);
            // Mail::to($emailToUse)->send(new ResetPasswordMail($data));
            return back()->with('success', 'Reset Password Link Send Successfully');
        }
    }
    public function change_password($id)
    {

        $user = DB::table('password_resets')->where('token', $id)->first();

        if (isset($user)) {
            return view('admin.auth.chnagePassword', compact('user'));
        }
    }

    public function resetPassword(Request $request)
    {

        $request->validate([
            'password' => 'required|min:8|unique:admins,password|unique:sub_admins,password',
            'confirmPassword' => 'required',

        ]);
        // return $request;
        if ($request->password != $request->confirmPassword) {
        // return $request;
                       return back()->with('error', 'Password and confirm password do not match');

        }
        $password = bcrypt($request->password);
        $adminExists = Admin::where('email', $request->email)->first();

        if (!$adminExists) {
            $subAdminExists = SubAdmin::where('email', $request->email)->first();
        }

        if (!$adminExists && !$subAdminExists) {
                       return back()->with('error', 'Email address not found');

        }

        if ($adminExists) {
            $adminExists->update(['password' => $password]);
        } elseif ($subAdminExists) {
            $subAdminExists->update(['password' => $password]);
        }

        DB::table('password_resets')->where('email', $request->email)->delete();

            return redirect('/admin')->with('success', 'Password updated successfully');

    }


    public function logout()
    {
        $adminExists = Auth::guard('admin')->logout();
        // dd($adminExists);
        if (!$adminExists) {
            Auth::guard('subadmin')->logout();
        }
        return redirect('admin')->with('success', 'Logged Out Successfully');
    }


    public function getSubAdminPermissions()
    {
        $subadmin = Auth::guard('subadmin')->user();

        // Fetch sub-admin permissions with associated side menus
        $sidemenu_permission = SubAdminPermission::where('sub_admin_id', $subadmin->id)
            ->whereIn('permissions', ['view', 'create', 'edit', 'delete'])
            ->with('side_menu')
            ->get();

        // Extract unique side menu names
        $sideMenuName = $sidemenu_permission->pluck('side_menu.name')->unique();

        // Group and map permissions by side menu name
        $sideMenuPermissions = $sidemenu_permission
            ->groupBy(fn($permission) => $permission->side_menu->name) // Group by side menu name
            ->map(function ($group, $sideMenuName) {
                return [
                    'side_menu_name' => $sideMenuName,
                    'permissions' => $group->pluck('permissions')->unique(),
                ];
            });

        return [
            'sideMenuPermissions' => $sideMenuPermissions,
            'sideMenuName' => $sideMenuName,
        ];
    }
}
