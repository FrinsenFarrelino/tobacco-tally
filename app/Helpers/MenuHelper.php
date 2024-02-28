<?php

use Illuminate\Support\Facades\Session;

    function generateMenu($data, $accessData, $parent = 0)
    {
        $html = '';
        if (!empty($data)) {
            foreach ($data as $item) {
                if ($item['id_menu'] == $parent) {
                    // Check if the menu item should be displayed based on the 'open' column in access_menus
                    $menuId = $item['id']; // Assuming 'id' represents the menu item ID
                    $openAccess = false;

                    // Check if the user is allowed to access the menu item
                    foreach (Session::get('user')['user_group'] as $user_group) {
                        if($user_group['name'] == 'Admin')
                        {
                            $openAccess = true;
                            break;
                        }
                        else{
                            foreach ($accessData as $value) {
                                if($value['menu_id'] == $menuId)
                                {
                                    $openAccess = $value['open'];
                                    break;
                                }
                            }
                            break;
                        }
                    }


                    if ($openAccess) {
                        $html .= '<li>';
                        switch ($item['type']) {
                            case 'dropdown1':
                                $html .= '<a class="has-arrow ai-icon" href="javascript:void()" aria-expanded="false">';
                                break;
                            case 'dropdown2':
                                $html .= '<a class="has-arrow" href="javascript:void()" aria-expanded="false">';
                                break;
                            default:
                                $html .= '<a href= "'. $item['url_menu'] .'">';
                                break;
                        }
                        if (!empty($item['icon'])) {
                            $html .= '<i class="' . $item['icon'] . '"></i> ';
                            $html .= '<span class="nav-text">' . $item['title'] . '</span>';
                        }
                        else
                        {
                            $html .= $item['title'];
                        }

                        $html .= '</a>';

                        if($item['is_sidebar'] != true)
                        {
                            $childHtml = generateMenu($data, $accessData, $item['id']);

                            if ($childHtml) {
                                $html .= '<ul aria-expanded="false">';
                                $html .= $childHtml;
                                $html .= '</ul>';
                            }
                        }

                        $html .= '</li>';
                    }
                }
            }
        }
        return $html;
    }
?>