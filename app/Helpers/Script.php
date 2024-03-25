<?php
use App\Http\Controllers\Controller;
    //script buat buka modal excel
    function exportExcel(){

        $renderArray = [
            renderRadioButton("mb-3 col-lg-6", "type", "csv", "CSV", "csv", ""),
            renderRadioButton("mb-3 col-lg-6", "type", "xlsx", "XLSX", "xlsx", ""),
        ];

        $renderJSON = json_encode($renderArray);

        $renderScriptButton = "$('#btnExport').click(function() {";
        $renderScriptButton .= "var modalHeader = '';";
        $renderScriptButton .= "var modalBody = '';";
        $renderScriptButton .= "var modalFooter = '';";
        $renderScriptButton .= "var renderJSON = ".json_encode($renderJSON).";";

        $renderScriptButton .= "$.ajax({";
        $renderScriptButton .= "url: '" . route('call-helper-function') . "',";
        $renderScriptButton .= "method: 'GET',";
        $renderScriptButton .= "dataType: 'json',";
        $renderScriptButton .= "data:{";
        $renderScriptButton .= "type: 'form',";
        $renderScriptButton .= "render: renderJSON,";
        $renderScriptButton .= "title: 'Choose export mode',";
        $renderScriptButton .= "route: 'generate-excel',";
        $renderScriptButton .= "div: 'yes',";
        $renderScriptButton .= "is_footer: 'export_table',";
        $renderScriptButton .= "method: 'get',";
        $renderScriptButton .= "},";
        $renderScriptButton .= "success: function(response) {";
        $renderScriptButton .= "modalHeader = response.header_content;";
        $renderScriptButton .= "modalBody = response.body_content;";
        $renderScriptButton .= "modalFooter = response.footer;";

        $renderScriptButton .= "openModal(modalHeader, modalBody, modalFooter);";
        $renderScriptButton .= "},";
        $renderScriptButton .= "error: function(xhr, status, error) {";
        $renderScriptButton .= "console.error(error);";
        $renderScriptButton .= "alert('Error calling helper function.');";
        $renderScriptButton .= "}";
        $renderScriptButton .= "});";
        $renderScriptButton .= "});";

        return $renderScriptButton;
    }

    //download excel dari BE
    function downloadExcel($mapping_table, $action, $name){
        if (!session()->isStarted()) {
            session()->start();
        }

        if (session()->has('auth_token')) {
            $auth_token = session('auth_token');
        }
        $controller = new Controller();

        $new_url = $controller->getUrlBase('download-excel');

        $renderDownloadExcel = "function downloadExcel() {";
        $renderDownloadExcel .= "var selectedValue = document.querySelector('input[name=\"type\"]:checked').value;";
        $renderDownloadExcel .= "const headers = {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer $auth_token'
        };";
        $renderDownloadExcel .= "axios({";
        $renderDownloadExcel .= "url: '".$new_url."',";
        $renderDownloadExcel .= "responseType: 'blob',";
        $renderDownloadExcel .= "params: {";
        $renderDownloadExcel .= "mapping_table: '".$mapping_table."',";
        $renderDownloadExcel .= "excel_type: selectedValue,";
        $renderDownloadExcel .= "name: '".$name."',";
        $renderDownloadExcel .= "action: '". $action ."'";
        $renderDownloadExcel .= "},";
        $renderDownloadExcel .= "headers: headers,";
        $renderDownloadExcel .= "})";
        $renderDownloadExcel .= ".then(response => {";
        $renderDownloadExcel .= "const url = window.URL.createObjectURL(new Blob([response.data]));";
        $renderDownloadExcel .= "const link = document.createElement('a');";
        $renderDownloadExcel .= "link.href = url;";
        $file_name =  date('dmYhis') . $name;
        $renderDownloadExcel .= 'var fileName = "' . $file_name . '."+selectedValue;';
        $renderDownloadExcel .= "link.setAttribute('download', fileName);";
        $renderDownloadExcel .= "document.body.appendChild(link);";
        $renderDownloadExcel .= "link.click();";
        $renderDownloadExcel .= "})";
        $renderDownloadExcel .= ".catch(error => {";
        $renderDownloadExcel .= "console.error('Error downloading Excel file:', error);";
        $renderDownloadExcel .= "swal('Request failed', error['message'], 'error')";
        $renderDownloadExcel .= "});";
        $renderDownloadExcel .= "}";

        return $renderDownloadExcel;
    }

    // untuk browse di bawah ini sampai clear field
    // autocomplete
    function searchFunction($id, $action, $search_term, $input_param, $output_param, $show_value=['name'], $min_length=0, $title_error = '', $message_error = '', $filter = [], $check_checkbox = false) {

        $res_param = [];
        $res_param_0 = '';
        $data_param_input = "";
        $after_check_input_param = "";

        $renderScript  = "\n\t\t var path = \"" . route('autocomplete') . "\";";
        $renderScript .= "\n\t\t var filterObject = [];";

        if (!empty($input_param)) {
            $after_check_input_param .= "\n\t\t var countCheckbox = 0;";

            $renderAlert = renderSweetAlert('Error','Supporting data is not complete','error');

            if($title_error != '' && $message_error != '')
            {
                $renderAlert = renderSweetAlert($title_error,$message_error,'error');
            }

            foreach ($input_param as $parameter) {
                $param = explode("|", $parameter);

                $res_param = $param[1];
                $param_0 = $param[0];
                $param_mode = isset($param[2]) ? $param[2] : '';

                if ($res_param != '') {
                    $after_check_input_param .= "\n\t\t\t var searchInput = $('#" . $res_param . "');";
                    $after_check_input_param .= "\n\t\t\t var inputValue = searchInput.val();";

                    if ($param_mode == 'checkbox') {
                        $after_check_input_param .= "\n\t\t\t var isChecked = searchInput.is(':checked');";
                        $after_check_input_param .= "\n\t\t\t if (isChecked) {";
                        $after_check_input_param .= "\n\t\t\t filterObject.push({ key: '" . $param_0 . "', term: 'equal', query: inputValue })";
                        $after_check_input_param .= "\n\t\t\t countCheckbox += 1";
                        $after_check_input_param .= "\n\t\t\t }";
                    } else {
                        $after_check_input_param .= "\n\t\t\t if (inputValue === undefined || inputValue.trim() === '') {";
                        $after_check_input_param .= $renderAlert;
                        $after_check_input_param .= "\n\t\t\t return;";
                        $after_check_input_param .= "\n\t\t\t } else {";
                        $after_check_input_param .= "\n\t\t\t filterObject.push({ key: '" . $param_0 . "', term: 'equal', query: inputValue })";
                        $after_check_input_param .= "\n\t\t\t }";
                    }
                }
            }
            if($check_checkbox == true)
            {
                $after_check_input_param .= "\n\t\t\t if (countCheckbox == 0) {";
                $after_check_input_param .= $renderAlert;
                $after_check_input_param .= "\n\t\t\t return;";
                $after_check_input_param .= "\n\t\t\t }";
            }

            $data_param_input = ",\n\t\t\t input_param: JSON.stringify(filterObject)";
        }

        $renderScript .= "console.log(filterObject);";

        $renderScript .= "\n\t\t\t $( '#".$id."' ).autocomplete({";
        $renderScript .= "\n\t\t\t source: function( request, response ) {";
        $renderScript .= $after_check_input_param;
        $renderScript .= "\n\t\t\t $.ajax({";
        $renderScript .= "\n\t\t\t url: path,";
        $renderScript .= "\n\t\t\t type: 'GET',";
        $renderScript .= "\n\t\t\t dataType: 'json',";
        $renderScript .= "\n\t\t\t data: {";
        $renderScript .= "\n\t\t\t search: request.term,";
        $renderScript .= "\n\t\t\t show_value: '" . json_encode($show_value) . "',";
        $renderScript .= "\n\t\t\t search_term: " . json_encode($search_term) . ",";
        $renderScript .= "\n\t\t\t filter: '". json_encode($filter) ."',";
        $renderScript .= "\n\t\t\t action: '".$action."'";
        $renderScript .= $data_param_input;
        $renderScript .= "\n\t\t\t },";
        $renderScript .= "\n\t\t\t success: function(data) {";
        $renderScript .= "\n\t\t\t console.log(data);";
        $renderScript .= "\n\t\t\t if(data.success == false){
            swal('Error', data['message'], 'error')
        }";
        $renderScript .= "\n\t\t\t else{
            response(data);
        };";
        $renderScript .= "\n\t\t\t }";
        $renderScript .= "\n\t\t\t });";
        $renderScript .= "\n\t\t\t },";
        $renderScript .= "\n\t\t\t minLength: ".$min_length.",";
        $renderScript .= "\n\t\t\t select: function (event, ui) {";
        $renderScript .= "\n\t\t\t $('#".$id."').val(ui.item.label);";
        if(!empty($output_param))
        {
            foreach ($output_param as $parameter) {
                $param = explode("|", $parameter);
                $output_action = "val";
                if(isset($param[2]))
                {
                    if ($param[2] == "html") {
                        $output_action = $param[2];
                    }
                }

                $setOutputUI = "ui.item.data.".$param[1].".toString()";
                if(str_contains($param[1],'-'))
                {
                    $renderOutputs = explode("-",$param[1]);
                    $tempOutputUI = "";
                    $count = 0;

                    foreach ($renderOutputs as $value) {
                        $tempOutputUI .= "ui.item.data." . $value . ".toString()";
                        $count++;

                        if ($count < count($renderOutputs)) {
                            $tempOutputUI .= " + ' - ' + ";
                        }
                    }
                    $setOutputUI = $tempOutputUI;
                }
                if($param[0] == 'browse_rfq_selected'){
                    $renderScript .= "\n\t\t\t $('#".$param[0]."')." . $output_action . "('Selected: ' + " . $setOutputUI . ");";
                }
                elseif ($param[0] == 'browse_rfq_2_selected') {
                    $renderScript .= "\n\t\t\t $('#".$param[0]."')." . $output_action . "('Selected: ' + " . $setOutputUI . ");";
                }
                else{
                    $renderScript .= "\n\t\t\t $('#".$param[0]."').".$output_action."(".$setOutputUI.");";
                }
            }
        }
        $renderScript .= "\n\t\t\t return false;";
        $renderScript .= "\n\t\t\t }";
        $renderScript .= "\n});";

        return $renderScript;
    }

    function validateFormField($required_input_json){
        $required_input = json_decode($required_input_json);
        $renderScript  = "function validateForm() {";
        $renderScript .= "\n\t\t\t var isValid = true;";

        // Array to store the IDs of required inputs
        $renderScript .= "var requiredInputs = ".json_encode($required_input).";"; // Add IDs of all required inputs

        // Iterate over each required input
        $renderScript .= "requiredInputs.forEach(function(inputId) {";
        // Get the value of the input
        $renderScript .= "var value = $('#' + inputId).val();";

        // If the value is empty, set isValid to false and display an error message
        $renderScript .= "if (!value) {";
        $renderScript .= "isValid = false;";
        $renderScript .= "}";
        $renderScript .= "});";
        $renderScript .= "return isValid;";
        $renderScript .= "}";
    }

    //button search
    function searchButton($id, $action, $headTable, $fieldTable, $table_name, $input_param, $output_param, $title_error = '', $message_error = '', $filter = [], $check_checkbox = false, $id_ajax = 'example') {
        $res_param = [];
        $res_param_0 = '';
        $data_param_input = "";
        $after_check_input_param = "";

        if (!empty($input_param)) {
            $after_check_input_param .= "\n\t\t var filterObject = [];";
            $filter_input_param = [];
            $after_check_input_param .= "\n\t\t var countCheckbox = 0;";

            $renderAlert = renderSweetAlert('Error','Supporting data is not complete','error');

            if($title_error != '' && $message_error != '')
            {
                $renderAlert = renderSweetAlert($title_error,$message_error,'error');
            }

            foreach ($input_param as $parameter) {
                $param = explode("|", $parameter);

                $res_param = $param[1];
                $param_0 = $param[0];
                $param_mode = isset($param[2]) ? $param[2] : '';

                if ($res_param != '') {
                    $after_check_input_param .= "\n\t\t\t var searchInput = $('#" . $res_param . "');";
                    $after_check_input_param .= "\n\t\t\t var inputValue = searchInput.val();";

                    if ($param_mode == 'checkbox') {
                        $after_check_input_param .= "\n\t\t\t var isChecked = searchInput.is(':checked');";
                        $after_check_input_param .= "\n\t\t\t if (isChecked) {";
                        $after_check_input_param .= "\n\t\t\t filterObject.push({ key: '" . $param_0 . "', term: 'equal', query: inputValue })";
                        $after_check_input_param .= "\n\t\t\t countCheckbox += 1";
                        $after_check_input_param .= "\n\t\t\t }";
                    } else {
                        $after_check_input_param .= "\n\t\t\t if (inputValue === undefined || inputValue.trim() === '') {";
                        $after_check_input_param .= $renderAlert;
                        $after_check_input_param .= "\n\t\t\t return;";
                        $after_check_input_param .= "\n\t\t\t } else {";
                        $after_check_input_param .= "\n\t\t\t filterObject.push({ key: '" . $param_0 . "', term: 'equal', query: inputValue })";
                        $after_check_input_param .= "\n\t\t\t }";
                    }
                }
            }
            if($check_checkbox == true)
            {
                $after_check_input_param .= "\n\t\t\t if (countCheckbox == 0) {";
                $after_check_input_param .= $renderAlert;
                $after_check_input_param .= "\n\t\t\t return;";
                $after_check_input_param .= "\n\t\t\t }";
            }

            $data_param_input = ",\n\t\t\t input_param: JSON.stringify(filterObject)";
        }

        $ajaxRender  = "\n\t\t\t $.ajax({";
        $ajaxRender .= "\n\t\t\t url: '" . route('get-browse-data') . "',";
        $ajaxRender .= "\n\t\t\t method: 'GET',";
        $ajaxRender .= "\n\t\t\t dataType: 'json',";
        $ajaxRender .= "\n\t\t\t data:{";
        $ajaxRender .= "\n\t\t\t action: '".$action."',";
        $ajaxRender .= "\n\t\t\t filter: '". json_encode($filter) ."',";
        $ajaxRender .= "\n\t\t\t head_table: " . json_encode($headTable) . ",";
        $ajaxRender .= "\n\t\t\t field_table: " . json_encode($fieldTable) . ",";
        $ajaxRender .= "\n\t\t\t output_param: " . json_encode($output_param) . ",";
        $ajaxRender .= "\n\t\t\t id_ajax: '" . $id_ajax. "',";
        $ajaxRender .= "\n\t\t\t table_name: '" . $table_name . "'";
        $ajaxRender .= $data_param_input;
        $ajaxRender .= "},";
        $ajaxRender .= "\n\t\t\t success: function(response) {";
        $ajaxRender .= "\n\t\t\t if(response.success == false){
            swal('Error', response.message, 'error')
        }";
        $ajaxRender .= "\n\t\t\t else{";
        $ajaxRender .= "\n\t\t\t var modalHeader = response.header;";
        $ajaxRender .= "\n\t\t\t var modalBody = response.body_content;";
        $ajaxRender .= "\n\t\t\t var modalFooter = response.footer;";
        $ajaxRender .= "\n\t\t\t if(modalHeader==''){";
        $ajaxRender .= "\n\t\t\t $('#customModalHeader').remove();}else{";
        $ajaxRender .= "\n\t\t\t $('#customModalHeader').html(modalHeader);}";
        $ajaxRender .= "\n\t\t\t $('#modalContent').html(modalBody);";
        $ajaxRender .= "\n\t\t\t $('#customModalFooter').html(modalFooter);";
        $ajaxRender .= "\n\t\t\t $('#baseModel').modal('show');";

        $ajaxRender .= "\n\t\t\t eval(response.init_table_modal);";
        $ajaxRender .= "};";

        $ajaxRender .= "\n\t\t\t },";

        $ajaxRender .= "\n\t\t\t error: function(xhr, status, error) {
            \n\t\t\t console.error(error);
            \n\t\t\t alert('Error calling helper function.');
            \n\t\t\t }});";

        $search = "\n\t\t $('#".$id."').on('click', function(e) {";
        $search .= "\n\t\t console.log('click on me');";
        $search .= $after_check_input_param;
        $search .= "\n\t\t ".$ajaxRender;
        $search .= "\n\t\t});";

        return $search;
    }

    //button refresh/clear
    function clearField($id, $output_clear) {
        $clear = "$('#".$id."').on('click', function(e) {";
        $clear .= "\n\t\tvar array = " . json_encode($output_clear) . ";";
        $clear .= "\n\t\tarray.forEach(element => {";
        $clear .= "\n\t\t\t$('#' + element).val('');"; // Use jQuery to clear input values
        $clear .= "\n\t\t});\n\t});";

        return $clear;
    }

    //button redirect ke halaman yang diset
    function redirectButton($id, $url) {
        $redirect = "\n\t\t$('#".$id."').on('click', function(e) {";
        $redirect .= "\n\t\twindow.location = '".$url."';";
        $redirect .= "\n\t\t});";

        return $redirect;
    }

    // 3 dibawah ini untuk grid
    // untuk autocomplete di grid
    function autocomplete_render($id, $search_term, $action, $show_value, $result_show, $function = '', $input_param = '', $input_grid_param = '', $filter = ''){
        if($function == '')
        {
            $function = $id;
        }
        $render = "function autocomplete_".$function."(element){";
        $render .= "return autocomplete_grid_".$id."(element,0,'".$id."',".json_encode($search_term).", '".$action."', ".json_encode($show_value)." , ".json_encode($result_show).",  '".$input_param."', '', '".$input_grid_param."', ".json_encode($filter).");";
        $render .= "}";
        return $render;
    }

    // untuk isi data di field yang ditentukan sesuai dengan data yang di dapat
    function grid_selected_suggest($grid_id, $columns){
        $render = "";

        foreach ($columns as $column) {
            $render .= "jQuery(".$grid_id."_element).jqGrid('setCell',rowid,'".$column['input']."',".$grid_id."_selected_suggest.".$column['field'].");";
        }

        return $render;
    }

    // buat sebelum di submit di isi data detail grid ke input detail
    function addToArray($getAllRowId) {
        // Add the values to an array
        $render = "var customData = [];";

        $render .= "$('#loadingIndicator').show();";
        $render .= "$('button').prop('disabled', true);";

        $render .= "var resData = [];";

        foreach ($getAllRowId as $value) {
            $render .= "var getAllRow".$value." = getAllRows_".$value."();";
            $render .= "customData.push({".$value.": getAllRow".$value."});";
        }

        $render .= "document.getElementById('hiddenInput').value = JSON.stringify(customData);";
        $render .= "var idGrid = '';";
        $render .= "var messageWarn = '';";
        $render .= "customData.forEach(element => {
            if('user_branch' in element) {
                idGrid = 'user_branch';
                if(element['user_branch'].length == 0) {
                    messageWarn = 'Please input the branch name at least one';
                }
                else{
                    if(element['user_branch'][0]['branch_id'] == ''){
                        messageWarn = 'Please input the branch name at least one';
                    }
                }
                if(messageWarn != ''){
                    sendDiv();
                }
                else{
                    var divElement = document.getElementById(idGrid);
                    divElement.innerHTML = '';
                }
            }
            else if('user_group' in element){
                idGrid = 'user_group';
                if(element['user_group'].length == 0) {
                    messageWarn = 'Please input the user group name at least one';
                }
                else{
                    if(element['user_group'][0]['user_group_id'] == ''){
                        messageWarn = 'Please input the user group name at least one';
                    }
                }
                if(messageWarn != ''){
                    sendDiv();
                }
                else{
                    var divElement = document.getElementById(idGrid);
                    divElement.innerHTML = '';
                }
            }
            else if('user_company' in element){
                idGrid = 'user_company';
                if(element['user_company'].length == 0) {
                    messageWarn = 'Please input the company name at least one';
                }
                else{
                    if(element['user_company'][0]['company_id'] == ''){
                        messageWarn = 'Please input the company name at least one';
                    }
                }
                if(messageWarn != ''){
                    sendDiv();
                }
                else{
                    var divElement = document.getElementById(idGrid);
                    divElement.innerHTML = '';
                }
            }
        });";

        // You can then submit the form programmatically with the added values
        // Now submit the form
        $render .= "if(messageWarn == ''){";
        $render .= "myForm.submit();";
        $render .= "}";

        $render .= "function sendDiv(){";
            $render .= "var divElement = document.getElementById(idGrid);";
            $render .= "divElement.innerHTML = '';";

            // Use AJAX to get the rendered HTML from the server
            $render .= "var xhttp = new XMLHttpRequest();";
            $render .= "xhttp.onreadystatechange = function() {";
            $render .= "  if (this.readyState == 4 && this.status == 200) {";
            $render .= "    divElement.innerHTML = this.responseText;";
            $render .= "  }";
            $render .= "};";
            // Append messageWarn as a query parameter to the URL
            $render .= "var url = '/render-warn?messageWarn=' + encodeURIComponent(messageWarn);";
            $render .= "xhttp.open('GET', url, true);";
            $render .= "xhttp.send();";
        $render .= "}";

        return $render;
    }
?>
