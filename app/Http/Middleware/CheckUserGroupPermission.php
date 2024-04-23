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
                if(request()->ajax()) {
                    return response()->json(array('success' => false, 'message' => trans('not_authorized_action')));
                }
                // Redirect to a warning page or show a popup modal
                return redirect()->route('warning')->with('message', 'You do not have permission to access this page.');
            }
        }
        return $next($request);
    }

    private function hasPermission($user_group, $getListMenu, $getAccessMenu, $url, $route)
    {
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
            'show-access-menu' => ['open'],
            'set-access-menu' => ['edit'],
            'status-purchase' => ['approve', 'disapprove'],
            'status-sale' => ['approve', 'disapprove'],
            'status-outgoing-item' => ['approve', 'disapprove'],
            'status-incoming-item' => ['approve', 'disapprove'],
            // Add more permissions as needed
        ];
        
        if ($user_group['name'] === 'Admin') {
            return true;
        } else {
            foreach ($permissionsHierarchy as $permission => $requiredPermissions) {
                if (str_contains($route, '.' . $permission) || str_contains($route, $permission)) {
                    foreach ($requiredPermissions as $requiredPermission) {
                        foreach ($getAccessMenu as $accessMenu) {
                            foreach ($getListMenu as $listMenu) {
                                if ($user_group['id'] === $accessMenu['user_group_id'] && $accessMenu[$requiredPermission] && $accessMenu['menu_id'] === $listMenu['id'] && $this->isSamePath($url, $listMenu['url_menu'])) {
                                    return true;
                                }
                            }
                        }
                    }
                }
            }
        }

        return false;
    }

    function isSamePath($path1, $path2)
    {
        // Ensure both paths start with a slash and have no trailing slashes
        $path1 = '/' . trim($path1, '/');
        $path2 = '/' . trim($path2, '/');

        // Extract segments from the paths
        $segments1 = explode('/', $path1);
        $segments2 = explode('/', $path2);

        // Compare segments until a non-matching segment is found
        $minSegments = min(count($segments1), count($segments2));
        for ($i = 0; $i < $minSegments; $i++) {
            if ($segments1[$i] !== $segments2[$i]) {
                return false; // Non-matching segment found
            }
        }

        // If all segments matched or one path is a sub-path of the other, return true
        return true;
    }
}
