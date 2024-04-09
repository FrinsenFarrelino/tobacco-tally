<?php
$autogrid["element"] = "element";
$autogrid["minlength"] = "min_length";
$autogrid["url"] = route('autocomplete');
$autogrid["id"] = "id";
$autogrid["search_term"] = "search_term";
$autogrid["actions"] = "actions";
$autogrid["show_value"] = "show_value";
$autogrid["result_show"] = "result_show";
$autogrid["param_input"] = "param_input";
$autogrid["input_grid_param"] = "input_grid_param";
$autogrid["filter"] = "filter";
$autogrid["param_output"] = "param_output";
$autogrid["select_function"] = $grid_id . "_selected_suggest = ui.item.all;";

$autogrid_html = "\n<script language='javascript' type='text/javascript'>\nfunction autocomplete_grid_" . $grid_id . "(element,min_length,id,search_term,actions,show_value,result_show,param_input,param_output,input_grid_param='',filter=''){ ";
$acomplete_override = 1;
$acomplete = $autogrid;
include "autocomplete.php";
$autogrid_html .= $acomplete_html;
$autogrid_html .= "\n}\n
    </script>";
