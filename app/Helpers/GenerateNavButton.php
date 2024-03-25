<?php
use Illuminate\Support\Facades\Route;
    function renderNavButton($is_button=false, $class_button="", $href="" , $class_icon="", $title="", $button_id="", $no="", $svg=null, $type='submit') {
        $field = '<div class="col-auto">';

        $fieldButton = '
            <button id="btnSubmitForm" class="'. $class_button .'" type="'.$type.'"><i class="'. $class_icon .'"></i>' . $title .'</button>
        ';

        $fieldButton = '
            <button id="btnSubmitForm" class="'. $class_button .'" type="'.$type.'"><i class="'. $class_icon .'"></i>' . $title .'</button>
        ';

        if($svg != null)
        {
            $fieldButton = '
                <button class="'. $class_button .'" type="submit">'.$svg.'' . $title .'</button>
            ';
        }

        if($is_button == false)
        {
            $fieldButton = '
                <a href="'. $href .'" class="'. $class_button .'"><i class="'. $class_icon .'"></i> '. $title .'</a>
            ';
            if($button_id != "")
            {
                $fieldButton = '
                    <button class="'. $class_button .' '. $button_id .'" id="'. $button_id .'" type="button" data-id="'. $no .'"><i class="'. $class_icon .'"></i> '. $title .'</button>
                ';
            }
        }

        $field .= $fieldButton;
        $field .= '</div>';

        return $field;
     }

    function generateNavbutton($data, $features = "", $page = "", $no = [], $url = "", $param = "")
    {
        if($param == "")
        {
            $param = $url;
        }

        $navbutton = [];

        if (strstr($features, "save")) {
            $save_button = trans('save');
            $navbutton['save'] = renderNavButton(true, class_button:"btn btn-primary btn-xs", class_icon:"fas fa-floppy-o mr-2", title:"Save");
        }

        if (strstr($features, "edit")) {
            $edit_button = trans('edit');
            if ($page == "edit") {
                $navbutton['edit'] = renderNavButton(true, class_button:"btn btn-primary btn-xs", title:"Edit");
            }
            elseif($page == "show"){
                $route = route($url.'.edit', [$param => $data['id']]);
                $navbutton['edit'] = renderNavButton(false, "btn btn-warning btn-xs", $route, "fa fa-pencil mr-2", "Edit");
            }
        }

        if (strstr($features, "print")) {
            if (Route::has($url.'.print')) {
                $print_button = trans('print');
                if ($page == "edit") {
                    $navbutton['print'] = renderNavButton(true, class_button:"btn btn-primary btn-xs", title:$print_button);
                }elseif ($page == "show") {
                    if ($url != 'quotation') {
                        $route = route($url . '.print', [$param => $data['id']]);
                        $navbutton['print'] = renderNavButton(false, class_button:"btn btn-warning btn-xs", href:$route, title:$print_button);
                    }
                } elseif ($page == "print") {
                    $navbutton['print'] = renderNavButton(false, class_button:"btn btn-rounded btn-primary btn-xs", class_icon:"fas fa-print mr-2", title: $print_button, button_id:"btnPrint");
                }
            }
        }

        if (strstr($features, "delete")) {
            if ($page == "edit" || $page == "show") {
                $delete_button = trans('delete');
                $route = route($url.'.destroy', [$param => $data['id']]);
                $navbutton['delete'] = renderNavButton(false, class_button:"btn btn-primary btn-xs", class_icon:"fas fa-trash-can mr-2", title:"Delete", button_id:"btnDestroy", no:$data['id']);
            }
        }

        // yang ini untuk balik dari add atau edit ke info dan dari info ke index
        if (strstr($features, "back")) {
            if ($page == "edit") {
                $cancel_button = trans('cancel');
                $route = route($url.'.show', [$param => $data['id']]);
                $navbutton['cancel'] = renderNavButton(false, "btn btn-rounded btn-danger btn-xs", $route, "fas fa-arrow-left mr-2", $cancel_button);
            }
            $back_button = trans('back');
            $route = route($url.'.index');
            $navbutton['back'] = renderNavButton(false, "btn btn-rounded btn-dark btn-xs", $route, "fas fa-arrow-left mr-2", $back_button);
        }

        if (strstr($features, "reload")) {
            $reload_button = trans('reload');
            $route = route($url.'.index', []);
            $navbutton['reload'] = renderNavButton(false, "btn btn-rounded btn-dark btn-xs", $route, "fas fa-rotate-right mr-2", $reload_button);
        }

        if (strstr($features, "help")) {
            if (Route::has($url.'.help')) {
                $help_button = trans('help');
                $route = route($url.'.index', []);
                $navbutton['help'] = renderNavButton(false, "btn btn-rounded btn-secondary btn-xs", $route, "fas fa-question-circle mr-2", $help_button);
            }
        }

        if (strstr($features, "add")) {
            $add_button = trans('add');
            if($url == 'pre-order' || $url == 'delivery-note' || $url == 'documentation' || $url == 'order')
            {
                $navbutton['add'] = renderNavButton(false, class_button:"btn btn-primary btn-xs ", class_icon:"fas fa-plus mr-2", title:$add_button, button_id:'btnOpenFormRadio');
            }
            else
            {
                $route = route($url.'.create', []);
                $navbutton['add'] = renderNavButton(false, "btn btn-primary btn-xs", $route, "fas fa-plus mr-2", $add_button);
            }
        }

        if (strstr($features, "approve")) {
            if ($page == "show") {
                $approve = trans('approve');
                $navbutton['approve'] = renderNavButton(false, class_button:"btn btn-rounded btn-success btn-xs", class_icon:"fas fa-check mr-2", title: $approve, button_id:"btnApprove");
            }
        }

        if (strstr($features, "disapprove")) {
            if ($page == "show") {
                $disapprove = trans('disapprove');
                $navbutton['disapprove'] = renderNavButton(false, class_button:"btn btn-rounded btn-primary btn-xs", class_icon:"fas fa-xmark mr-2", title: $disapprove, button_id:"btnDisapprove");
            }
        }

        if (strstr($features, "copy")) {
            if (Route::has($url.'.copy')) {
                if ($url == "quotation" && $page == "show") {
                    $copy_button = trans('quotation')['copy'];
                    $route = route($url.'.copy', ['id' => $data['id']]);
                    $navbutton['copy'] = renderNavButton(false, "btn btn-rounded btn-secondary btn-xs", $route, "fas fa-copy mr-2", $copy_button);
                }
            }
        }

        return $navbutton;
    }
?>
