<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
 use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use App\Models\UserRolePermission;
use App\Models\SubAdmin;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
  

public function boot()
{
    View::composer('*', function ($view) {
        $sideMenuPermissions = collect();

        if (Auth::guard('subadmin')->check()) {
            $user = Auth::guard('subadmin')->user();

            // Load roles from pivot
            $role = $user->roles()->first(); // assumes 1 role per subadmin

            if ($role) {
                $roleId = $role->id;

                $sideMenuPermissions = UserRolePermission::with(['permission', 'sideMenue'])
                    ->where('role_id', $roleId)
                    ->get()
                    ->groupBy(function ($item) {
                        return $item->sideMenue->name ?? 'undefined';
                    })
                    ->map(function ($items) {
                        return $items->pluck('permission.name');
                    });
            }
        }

        $view->with('sideMenuPermissions', $sideMenuPermissions);
    });
}




}
