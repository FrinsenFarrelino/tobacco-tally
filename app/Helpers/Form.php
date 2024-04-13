<?php
function renderInput($div_class, $label_class, $input_div_class, $type, $name, $label, $value, $mode, $options = '', $id = '', $class_input = 'form-control')
{
    if ($mode == 'view') {
        if ($type == 'checkbox') {
            $options = $options . ' disabled';
        } else {
            $options = $options . ' readonly';
        }
    }

    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    $oldValue = old($name, $value);

    if ($id == '') {
        $id = $name;
    }

    $field = '<div class="' . $div_class . '">';
    if ($label != '') {
        $field .= '<label for="' . $id . '" class="' . $label_class . ' ' . $options . '">' . $label . ' ' . $setRequired . '</label>';
    }


    $field .= '<div class="' . $input_div_class . '">';

    $field .= '<input type="' . $type . '" class="' . $class_input . '" id="' . $id . '" name="' . $name . '" value="' . $oldValue . '" ' . $options . '>
            </div>
        </div>';

    return $field;
}

function phoneNumberInput($class, $class_label, $class_input, $label, $name, $value = null, $mode = '', $attributes = [], $options = '', $id = '')
{
    if ($id == '') {
        $id = $name;
    }
    if ($mode == 'view') {
        $options = $options . ' readonly';
    }
    // Merge default attributes with provided attributes
    $attributes = array_merge([
        'type' => 'tel',
        'id' => $id,
        'class' => 'form-control',
        'placeholder' => 'Phone Number',
        'pattern' => '[0-9+]+', // Allow only numeric characters and a plus sign
        'inputmode' => 'tel', // Set input mode to "tel" for numeric input
    ], $attributes);

    // Add the name and value attributes
    $oldValue = old($name, $value);
    $attributes['name'] = $name;
    $attributes['value'] = $oldValue;

    // Generate the input HTML
    $inputHtml = '<input ' . collect($attributes)->map(function ($value, $key) {
        return $key . '="' . e($value) . '"';
    })->implode(' ') . ' ' . $options . '>';

    $field = '<div class="' . $class . '">';
    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }
    $field .= '<label for="' . $id . '" class="' . $class_label . '">' . $label . ' ' . $setRequired . '</label>';
    $field .= '<div class="' . $class_input . '">';
    $field .= $inputHtml;
    $field .= '</div>';
    $field .= '</div>';

    return $field;
}
function renderTextArea($div_class, $label_class, $input_div_class, $name, $label, $rows, $value, $mode, $options = '', $id = '', $class_input = 'form-control')
{
    if ($mode == 'view') {
        $options = $options . ' readonly';
    }

    $oldValue = old($name, $value);
    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    if ($id == '') {
        $id = $name;
    }

    $field = '
        <div class="' . $div_class . '">
            <label for="' . $id . '" class="' . $label_class . ' ' . $options . '">' . $label . ' ' . $setRequired . '</label>
            <div class="' . $input_div_class . '">
                <textarea class="form-control" value="' . $oldValue . '" name="' . $name . '" rows="' . $rows . '" id="' . $id . '" ' . $options . '>' . $oldValue . '</textarea>
            </div>
        </div>';

    return $field;
};

function renderSelect($class, $div_class_label, $div_class_input, $name, $label, $class_select, $data, $array, $mode, $options = '', $id = '')
{

    if ($mode == 'view') {
        $options .= ' disabled';
    }

    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    if ($id == '') {
        $id = $name;
    }

    $field = '<div class="' . $class . '">';
    if ($label != '') {
        $field .= '<div class="' . $div_class_label . '">';
        $field .= '<label for="' . $id . '" class="form-label" id="label_' . $id . '">' . $label . ' ' . $setRequired . '</label>';
        $field .= '</div>';
    }

    $field .= '<div class="' . $div_class_input . '">';
    $field .= '<select id="' . $id . '" class="' . $class_select . '" name="' . $name . '" ' . $options . '>';
    foreach ($array as $value) {
        $selected = old($name, $data) == $value['id'] ? 'selected' : '';
        $field .= '<option value="' . $value['id'] . '" ' . $selected . '>' . $value['name'] . '</option>';
    }
    $field .= '</select>';
    $field .= '</div>';
    $field .= '</div>';

    return $field;
};

function renderCheckbox($div_class, $div_input, $id, $name, $options, $isChecked = null)
{
    $field = '
            <div class="' . $div_class . '">
                <input type="checkbox" class="' . $div_input . '" id="' . $id . '" name="' . $name . '" ' . $options . ' ' . ($isChecked ? 'checked' : '') . '>
            </div>
        ';

    return $field;
}

function renderCheckboxWithLabel($div_class, $id, $name, $mode, $options, $label, $isChecked = null)
{

    if ($mode == 'view') {
        $options .= ' disabled';
    }

    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    $field = '<div class="' . $div_class . '">';
    $field .= '<div class="form-check custom-checkbox mb-3">';
    $field .= '<input type="checkbox" class="form-check-input" name="' . $name . '" id="' . $id . '" ' . $options . ' ' . $isChecked . '>';
    $field .= '<label class="form-check-label" for="' . $id . '">' . $label . ' ' . $setRequired . '</label>';
    $field .= '</div>';
    $field .= '</div>';

    return $field;
}

function renderRadioButton($class, $name, $id, $label, $value, $options)
{
    $oldValue = old($name);

    $checked = ($oldValue == $value) ? 'checked' : '';

    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    $field = '
            <div class="' . $class . '">
                <input class="form-check-input" type="radio" value="' . $value . '" name="' . $name . '" id="' . $id . '" ' . $options . ' ' . $checked . '>
                <label class="form-check-label" for="' . $id . '">
                ' . $label . ' ' . $setRequired . '
                </label>
            </div>
        ';

    return $field;
}

if (!function_exists('fileInput')) {
    function fileInput($div_class, $label, $name, $id, $class = '', $accept = '', $multiple = false, $attributes = [], $mode = '', $options = '', $div_label_class = '', $div_input_class = '')
    {
        // Default attributes for the file input
        $defaultAttributes = [
            'type' => 'file',
            'name' => $name,
            'id' => $id,
            'class' => $class,
        ];

        $setRequired = '';
        if (str_contains($options, 'required')) {
            $setRequired = '<span style="color: red">*</span>';
        }

        if ($mode == 'view') {
            $options .= ' readonly';
        }


        // Merge the default attributes with user-defined attributes
        $attributes = array_merge($defaultAttributes, $attributes);

        // Set the 'accept' attribute for accepted file types
        if (!empty($accept)) {
            $attributes['accept'] = $accept;
        }

        // Set the 'multiple' attribute if multiple file selection is allowed
        if ($multiple) {
            $attributes['multiple'] = 'multiple';
        }

        // Generate the hidden input field for storing the old file value
        $oldFileValue = old($name . '_old'); // Get the old file value
        $hiddenInput = '<input type="hidden" name="' . $name . '_old" value="' . $oldFileValue . '">';

        // Generate the file input HTML with attributes
        $input = '<input ' . implode(' ', array_map(function ($key, $value) {
            return $key . '="' . $value . '"';
        }, array_keys($attributes), $attributes)) . '>';

        $field  = '<div class="' . $div_class . '">';
        $field .= '<label class="form-check-label ' . $div_label_class . '" for="' . $id . '">';
        $field .= $label . ' ' . $setRequired;
        $field .= '</label>';
        $field .= '<div class="' . $div_input_class . '">';
        $field .= $input;
        $field .= '</div>';
        $field .= '</div>';

        if ($mode == 'add' || $mode == 'view') {
            $field = '<label class="form-label">No Uploaded File</label>';
        }
        return $field;
    }
}

function renderFieldError($errors, $name)
{
    $field  = '<div class="alert alert-danger alert-dismissible fade show" id="alert">';
    $field .= '<div class="alert-body">';
    $field .= '<button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>';
    $field .= $errors->first("$name");
    $field .= "</div>";
    $field .= "</div>";

    return $field;
};

function renderFieldCombineSelect($class, $div_class_label, $div_class_input, $label, $name_select, $value_select, $array, $options_select, $name_input, $value_input, $options_input, $mode, $ltr = 'left')
{
    $setRequired = '';
    if (str_contains($options_input, 'required') || str_contains($options_select, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    $field = '<div class="' . $class . '">';
    $field .= '<label class="' . $div_class_label . ' col-form-label">' . $label . ' ' . $setRequired . '</label>';
    $field .= '<div class="' . $div_class_input . '">';
    $field .= '<div class="input-group">';
    if ($ltr == 'left') {
        $field .= renderSelect('col-lg-2', '', '', $name_select, '', 'form-control', $value_select, $array, $mode, $options_select);
        $field .= renderInput('col-lg-10', '', '', 'text', $name_input, '', $value_input ?? '', $mode, $options_input);
    } else {
        $field .= renderInput('col-lg-9', '', '', 'text', $name_input, '', $value_input ?? '', $mode, $options_input);
        $field .= renderSelect('col-lg-3', '', '', $name_select, '', 'form-control', $value_select, $array, $mode, $options_select);
    }
    $field .= '</div>';
    $field .= '</div>';
    $field .= '</div>';

    return $field;
};

// Untuk browse input

function renderBrowserInput($div_class, $div_class_label, $div_class_input, $id, $name, $label, $value, $mode, $options, $actions, $search_term, $input_param, $output_param, $concat_value, $route, $button_thead, $button_tbody, $div_class_button = 'col-1 mt-1', $title_error = '', $message_error = '', $min_length = 0, $filter = [], $title_modal = '', $class_label = 'form-label', $class_input = 'form-control', $options_input = 'onkeypress="return false;"', $id_ajax = 'example')
{
    $setRequired = '';
    if (str_contains($options, 'required')) {
        $setRequired = '<span style="color: red">*</span>';
    }

    if ($mode == 'view') {
        $options = $options . ' readonly';
    }

    $oldValue = old($name, $value);

    if ($mode == 'view') {
        $options = $options . ' readonly';
        $div_class_label = "col-md-4";
        $div_class_input = "col-md-7";
    }

    $button_search_id = $id . '_search';
    $button_refresh_id = $id . '_refresh';
    $button_redirect_id = $id . '_redirect';
    $div_id_list = $id . '_list_item';

    $field = "\n <div class='" . $div_class . "'>";
    $field .= "\n\t\t <div class='" . $div_class_label . "'>";
    $field .= "\n\t\t <label for='" . $id . "' class='" . $class_label . "' " . $options . ">$label $setRequired</label>";
    $field .= "\n\t\t </div>";
    $field .= "\n\t\t <div class='" . $div_class_input . "'>";
    $field .= "\n\t\t <input type='text' class='" . $class_input . "' name='" . $name . "' id='" . $id . "' value='" . $oldValue . "' " . $options_input . " " . $options . " />";
    $field .= "\n\t\t <div id='" . $div_id_list . "'></div>";
    $field .= "\n\t\t </div>";
    if ($mode != "view") {
        $field .= "\n\t\t <div class='" . $div_class_button . "'><button type='button' class='btn btn-info' id='" . $button_search_id . "'><i class='fas fa-search-plus' style='color: #ffffff;'></i></button></div>";
        $field .= "\n\t\t <div class='" . $div_class_button . "'><button type='button' class='btn btn-secondary' id='" . $button_refresh_id . "'><i class='fas fa-repeat' style='color: #ffffff;'></i></button></div>";
    }
    if ($route != "") {
        $field .= "\n\t\t <div class='" . $div_class_button . "'><button type='button' class='btn btn-warning' id='" . $button_redirect_id . "'><i class='fa fa-external-link' style='color: #ffffff;'></i></button></div>";
    }

    $field .= "\n\t\t </div>";

    $clear = [];

    foreach ($output_param as $parameter) {
        $param = explode("|", $parameter);
        array_push($clear, $param[0]);
    }

    $field .= "<script>";
    $field .= searchFunction($id, $actions, $search_term, $input_param, $output_param, $concat_value, $min_length, $title_error, $message_error, $filter);
    $field .= clearField($button_refresh_id, $clear);

    if ($route != "") {
        $field .= redirectButton($button_redirect_id, route($route . ".index"));
    }

    if ($title_modal != "") {
        $field .= searchButton($button_search_id, $actions, $button_thead, $button_tbody, $title_modal, $input_param, $output_param, $title_error, $message_error, $filter, $id_ajax);
    } else {
        $field .= searchButton($button_search_id, $actions, $button_thead, $button_tbody, "Get " . $route, $input_param, $output_param, $title_error, $message_error, $filter, $id_ajax);
    }
    $field .= "</script>";

    return $field;
}

function renderLabel($class, $label)
{
    $field = '<div class="' . $class . '">';
    $field .= $label;
    $field .= '</div>';

    return $field;
}

function renderButton($div_class, $class, $icon_class, $label, $button_id, $is_dropdown = false, $value_dropdown = [])
{
    if ($is_dropdown == true) {
        $field = '<div class="' . $div_class . ' dropdown">';
    } else {
        $field = '<div clas="' . $div_class . '">';
    }
    if ($is_dropdown == true) {
        $field .= '<button class="btn ' . $class . ' dropdown-toggle" type="button" id="' . $button_id . '" data-bs-toggle="dropdown" aria-expanded="false">';
    } else {
        $field .= '<button class="btn ' . $class . '">';
    }
    $field .= '<i class="' . $icon_class . ' me-2"></i>';
    $field .= $label;
    $field .= '</button>';
    if ($is_dropdown == true) {
        $field .= '<div class="dropdown-menu">';
        foreach ($value_dropdown as $value) {
            $field .= '<a class="dropdown-item" href="#">' . $value . '</a>';
        }
        $field .= '</div>';
    }
    $field .= '</div>';

    return $field;
}

function renderFieldCombineInput($class = '', $div_class_label = '', $div_class_input = '', $label = '', $div_class_input1 = '', $div_class_input2 = '', $div_class_input3 = '', $name_input1 = '', $value_input1 = '', $name_input2 = '', $value_input2 = '', $name_input3 = '', $value_input3 = '', $options_input1 = '', $options_input2 = '', $options_input3 = '', $id1 = '', $id2 = '', $id3 = '', $mode = '', $type_input1 = 'text', $type_input2 = 'text', $type_input3 = 'text')
{
    $field = '<div class="' . $class . '">';
    $field .= '<label class="' . $div_class_label . ' col-form-label">' . $label . '</label>';
    $field .= '<div class="' . $div_class_input . '">';
    $field .= '<div class="input-group">';
    $field .= renderInput($div_class_input1, '', '', $type_input1, $name_input1, '', $value_input1 ?? '', $mode, $options_input1, $id1);
    $field .= renderInput($div_class_input2, '', '', $type_input2, $name_input2, '', $value_input2 ?? '', $mode, $options_input2, $id2);
    if ($div_class_input3 != '') {
        $field .= renderInput($div_class_input3, '', '', $type_input3, $name_input3, '', $value_input3 ?? '', $mode, $options_input3, $id3);
    }
    $field .= '</div>';
    $field .= '</div>';
    $field .= '</div>';

    return $field;
};
