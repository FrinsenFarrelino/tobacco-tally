<?php

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;

function generateMenu($data, $parent = 0)
{
    $html = '';

    if (!empty($data)) {
        foreach ($data as $item) {
            if ($item['id_menu'] === $parent && checkAccess($item)) {

                switch ($item['type']) {
                    case 'menu-header':
                        $html .= '<li class="menu-header">' . $item['name'] . '</li>';
                        break;
                    case 'dropdown1':
                        $html .= '<li class="dropdown">';
                        $html .= '<a href="#" class="nav-link has-dropdown" data-toggle="dropdown">';
                        break;
                    default:
                        $html .= '<li>';
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
                        $html .= $childHtml;
                    }
                }
                $html .= '</a>';
                if ($item['type'] !== 'dropdown1') {
                    $html .= '</li>';
                }
                if ($item['type'] === 'dropdown1') {
                    $html .= '<ul class="dropdown-menu">';
                    $childHtml = generateMenu($data, $item['id']);
                    if ($childHtml) {
                        $html .= $childHtml;
                    }
                    $html .= '</li>';
                    $html .= '</ul>';
                    $html .= '</li>';
                }
            }
        }
    }
    return $html;
}


function checkAccess($menuItem)
{
    if (Session::get('user_group')['name'] === 'Admin') {
        return true;
    } else {
        foreach (Session::get('access_menu') as $value) {
            if ($value['menu_id'] == $menuItem['id'] && $value['open'] == true) {
                return true;
            }
        }
    }

    return false;
}
