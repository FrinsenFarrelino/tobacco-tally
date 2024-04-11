<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CheckUserGroupPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user(); // Get the authenticated user
        $url = $request->getRequestUri(); // Get the requested URL
        $route = $request->route()->getName();
        $user_group = Session::get('user_group'); // Get the authenticated user
        $getListMenu = Session::get('list_menu');
        $getAccessMenu = Session::get('access_menu');
        if (auth()->check()) {
            if (!$this->hasPermission($user_group, $getListMenu, $getAccessMenu, $url, $route)) {
                // Redirect to a warning page or show a popup modal
                return redirect()->route('warning')->with('message', 'You do not have permission to access this page.');
            }
        }
        return $next($request);
    }

    private function hasPermission($user_group, $getListMenu, $getAccessMenu, $url, $route)
    {
        // Check user's group and permissions here

        // Define the permission hierarchy
        $permissionsHierarchy = [
            'index' => ['open'],
            'create' => ['add'],
            'store' => ['add'],
            'show' => ['open'],
            'edit' => ['edit'],
            'update' => ['edit'],
            'delete' => ['delete'],
            'destroy' => ['delete'],
            'print' => ['open'],
            // Add more permissions as needed
        ];

        if ($user_group['name'] === 'Admin') {
            // Admin has access to all URLs
            return true;
        } else {
            foreach ($permissionsHierarchy as $permission => $requiredPermissions) {
                if (str_contains($route, '.' . $permission) || str_contains($route, $permission)) {
                    foreach ($requiredPermissions as $requiredPermission) {
                        foreach ($getAccessMenu as $accessMenu) {
                            if ($user_group['id'] === $accessMenu['user_group_id'] && $accessMenu[$requiredPermission]) {
                                return true;
                            }
                        }
                    }
                }
            }
        }

        return false;
    }
}
