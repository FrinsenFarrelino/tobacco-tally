<?php
function renderModelHeaderForm($title)
{
    $field = '
            <h5 class="modal-title">' . $title . '</h5>
                <button type="button" class="btn btn-icon" data-bs-dismiss="modal"><i class="fas fa-times"></i>
            </button>
        ';
    return $field;
};

function renderBodyModalForm($action, $method, $enctype, $render_fields, $div, $label, $cancel, $confirm, $class_cancel, $class_confirm)
{

    $input = '';

    $field = '<div class="row">';

    $field .= '<form action="' . $action . '" method="' . $method . '" enctype="' . $enctype . '">';

    if ($label != '') {
        $field .= '<label>' . $label . '</label>';
        $field .= '<div class="mt-3 row">';
    }
    if ($div == 'yes') {
        $renderDiv1 = [];
        $renderDiv2 = [];
        $count = 1;
        foreach ($render_fields as $value) {
            if ($count == 1) {
                array_push($renderDiv1, $value);
                $count = 2;
            } else {
                array_push($renderDiv2, $value);
                $count = 1;
            }
        }
        $field .= "<div class='col-xl-6'>";
        foreach ($renderDiv1 as $valueArray) {
            $field .= $valueArray;
        }
        $field .= "</div>";

        $field .= "<div class='col-xl-6'>";
        foreach ($renderDiv2 as $valueArray) {
            $field .= $valueArray;
        }
        $field .= "</div>";
    } else {
        $field .= '<div class="col-sm-12 override-body-content text-start">';
        foreach ($render_fields as $value) {
            $field .= $value;
        }
        $field .= '</div">';
    }

    $field .= "<div class='col-xl-7 mt-2'></div>";
    if ($cancel != '') {
        $field .= "<div class='col-xl-5 mt-2'>";
        $field .= '<button type="button" class="' . $class_cancel . '" id="space-one-button" data-bs-dismiss="modal">' . $cancel . '</button>';
        $field .= '<button type="submit" class="' . $class_confirm . '">' . $confirm . '</button>';
        $field .= '</div>';
    }

    $field .= '</form>';

    if ($label != '') {
        $field .= '</div>';
    }
    $field .= '</div>';
    return $field;
};

function renderModelBodyAlert($class, $color, $title, $message)
{
    $field = '
            <div class="row">
                <div class="col-md-2 mt-2"><i class="' . $class . '" style="color: ' . $color . ';font-size:48px;"></i></div>
                <div class="col-md-10 ms-auto">
                    <h3>' . $title . '</h3>
                    <p>' . $message . '</p>
                </div>
            </div>
        ';
    return $field;
};

function renderModelBodyConfirmation($class, $color, $title, $message)
{
    $field = '
            <div class="container">
                <div class="row">
                    <div class="col-sm-12 override-body-content">
                        <div class="col-12 mb-4">
                            <i class="' . $class . '" style="color: ' . $color . ';font-size:48px;"></i>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <h3>' . $title . '</h3>
                                <p>' . $message . '</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        ';
    return $field;
};

function renderModelFooter($cancel, $confirm, $class_cancel, $class_confirm, $get_type = '')
{
    $field = '<button type="button" class="' . $class_cancel . '" id="space-one-button" data-bs-dismiss="modal">' . $cancel . '</button>';
    if ($get_type == 'form') {
        $field .= '<button type="submit" class="' . $class_confirm . '">' . $confirm . '</button>';
    } else {
        $field .= '<a href="/" class="' . $class_confirm . '">' . $confirm . '</a>';
    }

    return $field;
};

function renderModelFooterMethodConfirmation($cancel, $confirm, $class_cancel, $class_confirm, $id = '', $url = '', $param = '', $method = 'delete', $name = 'id')
{
    $cancel = trans('script_modal')[$cancel];
    $confirm = trans('script_modal')[$confirm];

    $field = '<button type="button" class="' . $class_cancel . '" id="space-one-button" data-bs-dismiss="modal">' . $cancel . '</button>';
    if ($method == 'post') {
        $route = route($url . '.store', []);
        $field .= '<form action="' . $route . '" method="POST">';
        $field .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
    } elseif ($method == 'put') {
        $route = route($url . '.update', [$param => $id]);
        $field .= '<form action="' . $route . '" method="POST">';
        $field .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        $field .= '<input type="hidden" name="_method" value="PUT">';
    } else {
        if ($url == 'delete-file-db') {
            $route = route($url, [$param => $id]);
        } else {
            $route = route($url . '.destroy', [$param => $id]);
        }
        $field .= '<form action="' . $route . '" method="POST">';
        $field .= '<input type="hidden" name="_token" value="' . csrf_token() . '">';
        $field .= '<input type="hidden" name="_method" value="DELETE">';
    }

    $field .= '<input type="hidden" id="' . $name . '" name="' . $name . '" value="' . $id . '">';
    $field .= '<button type="submit" class="' . $class_confirm . '">' . $confirm . '</button>';
    $field .= '</form>';

    return $field;
};

function renderBaseModel()
{
    $field = '
            <div class="modal fade" id="baseModel" tabindex="-1" role="dialog" aria-labelledby="modalWarningLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <div class="modal-header" id="customModalHeader">
                            <!-- Custom content for modal header goes here -->
                        </div>
                        <div class="modal-body" id="modalContent">
                            <!-- Custom content for modal body goes here -->
                        </div>
                        <div class="modal-footer override-footer" id="customModalFooter">
                            <!-- Custom content for modal footer goes here -->
                        </div>
                    </div>
                </div>
            </div>
        ';
    return $field;
};

function renderLargeModel()
{
    $field = '
            <div class="modal fade bd-example-modal-lg" id="baseModel" tabindex="-1" role="dialog" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header" id="customModalHeader">
                            <!-- Custom content for modal header goes here -->
                        </div>
                        <div class="modal-body" id="modalContent">
                            <!-- Custom content for modal body goes here -->
                        </div>
                        <div class="modal-footer override-footer" id="customModalFooter">
                            <!-- Custom content for modal footer goes here -->
                        </div>
                    </div>
                </div>
            </div>
        ';
    return $field;
};



function renderOpenModal()
{
    $renderOpenModal = "function openModal(modalTitle, modalBody, modalFooter) {";
    $renderOpenModal .= "console.log('test');";
    $renderOpenModal .= "if(modalTitle==''){";
    $renderOpenModal .= "$('#customModalHeader').remove();}else{";
    $renderOpenModal .= "$('#customModalHeader').html(modalTitle);}";
    $renderOpenModal .= "$('#modalContent').html(modalBody);";
    $renderOpenModal .= "$('#customModalFooter').html(modalFooter);";
    $renderOpenModal .= "$('#baseModel').modal('show');}";
    return $renderOpenModal;
}

function renderScriptButtonTable($class, $type, $icon, $color, $title, $text, $route = '', $param = '', $methods = 'delete', $name = 'id', $table_id = 'myTable')
{
    $title = trans('script_modal')[$title];
    $text = trans('script_modal')[$text];


    $renderScriptButton = "$('#" . $table_id . "').on('click', '." . $class . "', function() {";
    $renderScriptButton .= "var id = $(this).data('id');";
    $renderScriptButton .= "var modalHeader = '';";
    $renderScriptButton .= "var modalBody = '';";
    $renderScriptButton .= "var modalFooter = '';";
    $renderScriptButton .= "$.ajax({";
    $renderScriptButton .= "url: '" . route('call-helper-function') . "',";
    $renderScriptButton .= "method: 'GET',";
    $renderScriptButton .= "dataType: 'json',";
    $renderScriptButton .= "data:{
            type: '" . $type . "',
            class_icon: '" . $icon . "',
            color: '" . $color . "',
            title: '" . $title . "',
            text: '" . $text . "',
            id: id,
            route: '" . $route . "',
            param: '" . $param . "',
            'method': '" . $methods . "',
            'name': '" . $name . "'
        },";
    $renderScriptButton .= "success: function(response) {
            console.log(response);

            modalBody = response.body_content;
            modalFooter = response.footer;

            openModal(modalHeader, modalBody, modalFooter);
        },";
    $renderScriptButton .= "error: function(xhr, status, error) {
            console.error(error);
            alert('Error calling helper function.');
        }});";
    $renderScriptButton .= "});";

    return $renderScriptButton;
}

function renderScriptButtonForm($class, $type, $icon, $color, $title, $text, $route = '', $param = '', $methods = 'delete', $name = 'id')
{
    $title = trans('script_modal')[$title];
    $text = trans('script_modal')[$text];

    $renderScriptButton = "$('." . $class . "').click(function() {";

    $renderScriptButton .= "var id = $(this).data('id');";
    $renderScriptButton .= "var modalHeader = '';";
    $renderScriptButton .= "var modalBody = '';";
    $renderScriptButton .= "var modalFooter = '';";
    $renderScriptButton .= "$.ajax({";
    $renderScriptButton .= "url: '" . route('call-helper-function') . "',";
    $renderScriptButton .= "method: 'GET',";
    $renderScriptButton .= "dataType: 'json',";
    $renderScriptButton .= "data:{
            type: '" . $type . "',
            class_icon: '" . $icon . "',
            color: '" . $color . "',
            title: '" . $title . "',
            text: '" . $text . "',
            id: id,
            route: '" . $route . "',
            param: '" . $param . "',
            'method': '" . $methods . "',
            'name': '" . $name . "'
        },";
    $renderScriptButton .= "success: function(response) {
            // Handle the response from the server

            modalBody = response.body_content;
            modalFooter = response.footer;

            openModal(modalHeader, modalBody, modalFooter);
        },";
    $renderScriptButton .= "error: function(xhr, status, error) {
            console.error(error);
            alert('Error calling helper function.');
        }});";
    $renderScriptButton .= "});";

    return $renderScriptButton;
}

function renderSweetAlert($title, $message, $type)
{
    $renderSweetAlert = 'swal({ title: "' . $title . '", text: "' . $message . '", type: "' . $type . '", showCancelButton: !0, confirmButtonColor: "#ae1724"});';
    return $renderSweetAlert;
}

function renderAlertWarning($code, $message, $errors)
{
    //check first character code
    $first_character = substr($code, 0, 1);
    $error_value = '';

    if (is_array($message)) {
        if (array_key_exists('message', $message)) {
            $message = $message['message'];
        } elseif (array_key_exists('errorInfo', $message)) {
            $message = $message['errorInfo'][2];
        }
    }

    if (!empty($errors)) {
        foreach ($errors->all() as $error) {
            $error_value .= $error;
        }
    }

    if ($first_character == "6") {
        $field = '
            <div class="alert alert-warning alert-dismissible fade show">
                <i class="fa-solid fa-triangle-exclamation" style="color:#FF8000;font-size:16px;"></i>
				<strong>' . $error_value . '</strong> ' . $message . '.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                </button>
            </div>
            ';
    } elseif ($first_character == "2") {
        $field = '
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fa-solid fa-triangle-exclamation" style="color:#90EE90;font-size:16px;"></i>
				<strong>' . $message . '</strong>.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                </button>
            </div>
            ';
    } else {
        $field = '
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fa-solid fa-triangle-exclamation" style="color:#FF0000;font-size:16px;"></i>
                <strong>' . $error_value . '</strong> ' . $message . '.
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                </button>
            </div>
            ';
        if (!empty($errors)) {
            if (count($errors->all()) > 1) {
                $li = '';
                foreach ($errors->all() as $error) {
                    $li .= '<li>' . $error . '</li>';
                }
                $field = '
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#FF0000;font-size:16px;"></i>
                        <strong>' . $message . '</strong>.
                        <ul>
                        ' . $li . '
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="btn-close">
                        </button>
                    </div>
                    ';
            }
        }
    }

    return $field;
};
