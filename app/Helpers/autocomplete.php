<?php
$acomplete_html = "";
if ($acomplete_override != 1) {
    $acomplete["element"] = "element";
    $acomplete["minlength"] = "min_length";

    $acomplete["url"] = route('autocomplete');
    $acomplete["id"] = "id";
    $acomplete["search_term"] = "search_term";
    $acomplete["actions"] = "actions";
    $acomplete["show_value"] = "show_value";
    $acomplete["result_show"] = "result_show";
    $acomplete["param_input"] = "param_input";
    $acomplete["param_output"] = "param_output";
    $acomplete["input_grid_param"] = "input_grid_param";
    $acomplete["filter"] = "filter";
    $acomplete["select_function"] = "grid_selected_suggest = ui.item.all;";
    $acomplete_html = "";

    if (!empty($acomplete_include))
        foreach ($acomplete_include as $setting)
            include $setting;
}

$acomplete_html .= "\nvar varid = " . $acomplete["id"] . ",";
$acomplete_html .= "inputs = " . $acomplete["param_input"] . ",";
$acomplete_html .= "input_grids = " . $acomplete["input_grid_param"] . ",";
$acomplete_html .= "outputs = " . $acomplete["param_output"] . ";";

$acomplete_html .= "filterObject = [];";
$acomplete_html .= "var get_data = '';";

$acomplete_html .= "\n$(" . $acomplete["element"] . ").autocomplete(";
$acomplete_html .= "\n{\n\tminLength:" . $acomplete["minlength"] . ",\n\tsource:function(request,response)\n\t{";
$acomplete_html .= "\n\t\tvar param_name = [], param_value = [];\n\t\t";

$acomplete_html .= "if(input_grids && input_grids != ''){";
$acomplete_html .= "var inputParams = input_grids.split(',');";
$acomplete_html .= "if(inputParams.length > 0){";
$acomplete_html .= "filterObject = [];";
$acomplete_html .= "for(var i = 0; i < inputParams.length; i++){";
$acomplete_html .= "var param = inputParams[i].split('|');";

$acomplete_html .= "if(param.length > 0){";
$acomplete_html .= "res_param = param[1];param_0 = param[0];get_data = param[2] || '';var param_mode = param[3] || '';";
$acomplete_html .= "if(res_param != ''){";

$acomplete_html .= "var setIds = varid + '_grid';";

$acomplete_html .= "var searchInput = $('#' + setIds);";
$acomplete_html .= 'var selectedRowId = searchInput.jqGrid("getGridParam", "selrow");';

$acomplete_html .= "var ids = searchInput.jqGrid('getDataIDs');";

$acomplete_html .= 'var selectedIndex = searchInput.jqGrid("getInd", selectedRowId);';

$acomplete_html .= "var rowData = searchInput.jqGrid('getRowData', ids[selectedIndex-1]);";

$acomplete_html .= "var inputValue = rowData[res_param];";
$acomplete_html .= "if (inputValue === undefined || inputValue === '') {";
$acomplete_html .= renderSweetAlert('Error', 'error', 'error');
$acomplete_html .= "return;";
$acomplete_html .= "} else {";
$acomplete_html .= "filterObject.push({ key: param_0, term: 'equal', query: inputValue });}";
$acomplete_html .= "}";
$acomplete_html .= "}";
$acomplete_html .= "}";
$acomplete_html .= "}";
$acomplete_html .= "}";

$acomplete_html .= "if(inputs && inputs != '')\n\t\t{\n\t\t\tvar input = inputs.split(',');\n\t\t\tif(input.length > 0)\n\t\t\t{\n\t\t\t\tfor(var i = 0; i < input.length; i++)\n\t\t\t\t{\n\t\t\t\t\tif(input[i] != '')\n\t\t\t\t\t{\n\t\t\t\t\t\tvar param = input[i].split('|');\n\t\t\t\t\t\tif(param.length > 0)\n\t\t\t\t\t\t{\n\t\t\t\t\t\t\tvar param_0 = param[0];\n\t\t\t\t\t\t\tparam_name[i] = param_0;\n\t\t\t\t\t\t\tparam_value[i] = $('#'+param[1]).val();\n\t\t\t\t\t\t\tif(!param_value[i])\n\t\t\t\t\t\t\t{\n\t\t\t\t\t\t\t\tif(param[4] == 'null')\n\t\t\t\t\t\t\t\t\tparam_value[i] = 'skip';\n\t\t\t\t\t\t\t\telse\n\t\t\t\t\t\t\t\t\t" . renderSweetAlert('Error', 'error', 'error') . "\n\t\t\t\t\t\t\t}\n\t\t\t\t\t\t}\n\t\t\t\t\t}\n\t\t\t\t}\n\t\t\t}\n\t\t}";
$acomplete_html .= "\n\t\tvar additional_url = '';\n\t\t";
$acomplete_html .= "for(var j = 0; j < param_name.length; j++)\n\t\t{\n\t\t\tadditional_url += '&'+param_name[j]+'='+param_value[j];\n\t\t filterObject.push({ key: param_name[j], term: 'equal', query: param_value[j] });}\n\t\t";

$acomplete_html .= "$.ajax(\n\t\t{\n\t\t\t";
$acomplete_html .= "\n\t\t\turl:'" . $acomplete["url"] . "'";
$acomplete_html .= ",\n\t\t\ttype:'GET'";
$acomplete_html .= ",\n\t\t\tdata:\n\t\t\t{";
$acomplete_html .= "\n\t\t\t\t search:request.term,";
$acomplete_html .= "\n\t\t\t\t is_grid:'true',";
$acomplete_html .= "\n\t\t\t show_value:" . $acomplete['show_value'] . ",";
$acomplete_html .= "\n\t\t\t result_show:" . $acomplete['result_show'] . ",";
$acomplete_html .= "\n\t\t\t input_param: JSON.stringify(filterObject),";
$acomplete_html .= "\n\t\t\t filter: JSON.stringify(" . $acomplete['filter'] . "),";
$acomplete_html .= "\n\t\t\t search_term:" . $acomplete['search_term'] . ",";
$acomplete_html .= "\n\t\t\t action:" . $acomplete["actions"] . ",";
$acomplete_html .= "\n\t\t\t get_data: get_data,";
$acomplete_html .= "\n\t\t\t },";
$acomplete_html .= "\n\t\t\tdataType:'json',\n\t\t\t";
$acomplete_html .= "success:function(data,textStatus,jqXHR)\n\t\t\t{";

$acomplete_html .= "\n\t\t\t\t response($.map(data.items,function(item)\n\t\t\t\t{";
$acomplete_html .= "\n\t\t\t\t\tvar debugging_otf = '';";
$acomplete_html .= "\n\t\t\t\t\tif(item.debug)\n\t\t\t\t\t";
$acomplete_html .= "{\n\t\t\t\t\t\tvar item_debug = 'autocomplete '+" . $acomplete["id"] . "+' debug : '+item.debug;";
$acomplete_html .= "\n\t\t\t\t\t\tconsole.log(item_debug);";
$acomplete_html .= "\n\t\t\t\t\t\tdebugging_otf = item_debug;";
$acomplete_html .= "\n\t\t\t\t\t}";
$acomplete_html .= "\n\t\t\t\t\tif(item.debug_error)\n\t\t\t\t\t{";
$acomplete_html .= "\n\t\t\t\t\t\tvar item_debug_error = 'autocomplete '+" . $acomplete["id"] . "+' debug_error : '+item.debug_error;";
$acomplete_html .= "\n\t\t\t\t\t\tconsole.log(item_debug_error);";
$acomplete_html .= "\n\t\t\t\t\t\tdebugging_otf = debugging_otf+'<br /><br />'+item_debug_error;";
$acomplete_html .= "\n\t\t\t\t\t}";
$acomplete_html .= "\n\t\t\t\t\tif(debugging_otf !== '')";
$acomplete_html .= "\n\t\t\t\t\t\t$('#debugging_otf').html(debugging_otf);";
$acomplete_html .= "\n\t\t\t\t\treturn { value:item.visible,all:item }\n\t\t\t\t}));";
$acomplete_html .= "\n\t\t\t}\n\t\t});";
$acomplete_html .= "\n\t},";
$acomplete_html .= "\n\t\t\t error: function(xhr, status, error) {
        \n\t\t\t console.error(error);
        \n\t\t\t alert('Error calling autocomplete.');
        \n\t\t\t },";
$acomplete_html .= "\n\tselect:function(event,ui)\n\t{";
$acomplete_html .= "\n\t\tselected = ui.item.all;";
$acomplete_html .= "\n\t\t\t console.log('selected : ');";
$acomplete_html .= "\n\t\t\t console.log(selected);";
$acomplete_html .= "\n\t\t$(" . $acomplete["element"] . ").val(selected.result);";
$acomplete_html .= "\n\t\t" . $acomplete["select_function"] . "\n\t\tvar param_name = [], param_value = [];";
$acomplete_html .= "\n\t\treturn false;\n\t}\n});";
