<?php

use Illuminate\Support\Facades\Session;

function generateMenu($data, $parent = 0)
{
    $html = '';
    if (!empty($data)) {
        foreach ($data as $item) {
            if ($item['id_menu'] == $parent) {
                //         // Check if the menu item should be displayed based on the 'open' column in access_menus
                //         $menuId = $item['id']; // Assuming 'id' represents the menu item ID
                //         $openAccess = false;

                //         // Check if the user is allowed to access the menu item
                //         foreach (Session::get('user')['user_group'] as $user_group) {
                //             if($user_group['name'] == 'Admin')
                //             {
                //                 $openAccess = true;
                //                 break;
                //             }
                //             else{
                //                 foreach ($accessData as $value) {
                //                     if($value['menu_id'] == $menuId)
                //                     {
                //                         $openAccess = $value['open'];
                //                         break;
                //                     }
                //                 }
                //                 break;
                //             }
                //         }


                // if ($openAccess) {
                switch ($item['type']) {
                    case 'menu-header':
                        $html .= '<li class="menu-header">' . $item['name'] . '</li>';
                        break;
                    case 'dropdown1':
                        $html .= '<li class="dropdown">';
                        $html .= '<a href="#" class="nav-link has-dropdown" data-toggle="dropdown">';
                        break;
                    default:
                        $html .= '<a class="nav-link" href= "' . $item['url_menu'] . '">';
                        break;
                }
                if (!empty($item['icon'])) {
                    $html .= '<i class="' . $item['icon'] . '"></i> ';
                    $html .= '<span>' . $item['title'] . '</span>';
                } elseif ($item['type'] !== 'menu-header') {
                    $html .= $item['title'];
                }
                if ($item['type'] !== 'dropdown1') {
                    $childHtml = generateMenu($data, $item['id']);
                    if ($childHtml) {
                        $html .= '<li>';
                        $html .= $childHtml;
                        $html .= '</li>';
                    }
                }
                $html .= '</a>';
                if ($item['type'] === 'dropdown1') {
                    $html .= '<ul class="dropdown-menu">';
                    $childHtml = generateMenu($data, $item['id']);
                    if ($childHtml) {
                        $html .= '<li>';
                        $html .= $childHtml;
                        $html .= '</li>';
                    }
                    $html .= '</ul>';
                    $html .= '</li>';
                }
            }
        }
    }
    return $html;
}
