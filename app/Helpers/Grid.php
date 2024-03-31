<?php

function gridSetup($mode, $action, $search_query, $id, $caption, $colNames, $column, $colModel, $defaultData = "{ }", $columnUnique = "[ ]", $isAdd = "true", $isDelete = "true", $columnAutoAddRow = 0, $mergedHeaders = null, $useFooter = false){

        $grid["var"]["load"]               = 0;
        if ($mode == "add"){
            $grid["var"]["load"] = 1;
        }

        $grid["id"]                        = $id;
        $grid["column"]                    = $column;
        $grid["var"]["default_data"]       = $defaultData;
        $grid["var"]["column_unique"]      = $columnUnique;

        $grid["nav_option"]["add"]         = $isAdd;
        $grid["nav_option"]["addtext"]     = '""';
        $grid["nav_option"]["del"]         = $isDelete;
        $grid["nav_option"]["deltext"]     = '""';

        $grid["navgrid"]                   = 1;
        $grid["var"]["element"]            = "'#" . $id . "_grid'";
        $grid["var"]["navgrid"]            = "'#" . $id . "_navgrid'";
        $grid["var"]["navgrid_active"]     = $grid["navgrid"];
        $grid["var"]["new_record"]         = 1;
        $grid["var"]["allow_delete"]       = 1;
        $grid["var"]["editing_rowid"]      = 0;
        $grid["var"]["editing_cellname"]   = "''";
        $grid["var"]["editing_value"]      = 0;
        $grid["var"]["editing_iRow"]       = 0;
        $grid["var"]["editing_iCol"]       = 0;
        $grid["var"]["selected_suggest"]   = "false";

        $grid["tab"]                       = "data";
        $grid["type"]                      = "grid";
        $grid["var"]["grid_type"]          = "'grid_default'";

        $grid["var"]["column_focus"]       = 1;
        $grid["var"]["column_total"]       = 0;
        $grid["var"]["before_edit_cell"]   = 0;
        $grid["var"]["add_row_data_pos"]   = "'last'";

        $controllerBase = new \App\Http\Controllers\Controller();
        $coltemplate_date_1 = 'yy-mm-dd';

        $filter_id = array('id' => $search_query);
        $grid["option"]["mtype"]           = "'POST'";
        $grid["option"]["datatype"]        = "'json'";
        $grid["option"]["postData"]        = "{action: '".$action."', filters: ".json_encode($filter_id).", columnHead: ".json_encode($column)."}";
        $grid["option"]["colNames"]        = $colNames;
        if($columnAutoAddRow){
            $grid["option"]["colNames"] = "[" . $grid["option"]["colNames"] . ",'ADD']";
        }else{
            $grid["option"]["colNames"] = "[" . $grid["option"]["colNames"] . "]";
        }
        $grid["option"]["gridComplete"]    = "actionGridComplete" . "_" . $id;
        $grid["option"]["loadComplete"]    = "actionLoadComplete" . "_" . $id;
        $grid["option"]["beforeEditCell"]  = "actionBeforeEditCell" . "_" . $id;
        $grid["option"]["beforeSaveCell"]  = "actionBeforeSaveCell" . "_" . $id;
        $grid["option"]["afterSaveCell"]   = "actionAfterSaveCell" . "_" . $id;
        $grid["option"]["afterRestoreCell"]= "actionAfterRestoreCell" . "_" . $id;
        $grid["option"]["cellEdit"]        = "true";
        if ($mode == "view"){
            $grid["nav_option"]["add"]         = "false";
            $grid["nav_option"]["del"]      = "false";
            $grid["option"]["cellEdit"]        = "false";
        }
        $grid["option"]["cellsubmit"]      = "'clientArray'";
        $grid["option"]["height"]          = "'auto'";
        $grid["option"]["width"]           = 1000;
        $grid["option"]["caption"]         = $caption;
        $grid["option"]["multiselect"]     = "true";
        $grid["option"]["rownumbers"]      = "true";
        if ($useFooter) {
            $grid["option"]["footerrow"]       = "true";
            $grid["option"]["userDataOnFooter"]       = "true";
        } else {
            $grid["option"]["footerrow"]       = "false";
        }
        $grid["option"]["rowNum"]          = 100000;
        $grid["option"]["pginput"]         = "false";
        $grid["option"]["pgbuttons"]       = "false";
        $grid["option"]["viewrecords"]     = "false";

        $new_url = $controllerBase->getUrlBase('getData');

        if ($mode != "add"){
            $grid["option"]["url"] = "'$new_url'";
        }
        $grid["option"]["editurl"] = "'javascript:void(0);'";
        $grid["option"]["pager"]           = "'#" . $id . "_navgrid'";
        $grid["option"]["colModel"]        = "automatic";

        $grid["colmodel"]                  = $colModel;
        if($columnAutoAddRow){
            $grid["colmodel"][count($colModel)]["name"]     = "'add'";
            $grid["colmodel"][count($colModel)]["index"]    = "'add'";
            $grid["colmodel"][count($colModel)]["hidden"]   = "true";
            $grid["colmodel"][count($colModel)]["width"]    = "25";
            $grid["colmodel"][count($colModel)]["align"]    = "'center'";
            $grid["colmodel"][count($colModel)]["sortable"] = "false";
            $grid["colmodel"][count($colModel)]["editable"] = "true";
        }
        $grid["parameter"]                 = array();

        $grid["debug"]                     = 0;
        $grid["filtertoolbar"]             = 0;
        $grid["nav_option"]["view"]        = "false";
        $grid["nav_option"]["edit"]        = "false";
        $grid["nav_option"]["addfunc"]     = "actionAddFunc" . "_" . $id;
        $grid["nav_option"]["delfunc"]     = "actionDelFunc" . "_" . $id;
        $grid["nav_option"]["search"]      = "false";
        $grid["nav_option"]["refresh"]     = "false";
        $grid["class_area"]                = "";
        $grid["functions_script"]          = 1;
        $grid["others_script"]             = array(
            "scripts"
        );

        $grid_id = $id;
        $grid_caption = str_replace("'", "", $caption);
        $grid_html                  = "";
        $grid_html .= "\n<script language='javascript' type='text/javascript'>";

        $temp_width="width:100";
        $temp_right="align:'right'";
        $temp_sortable="sortable:false";
        $temp_decimalSeparator="decimalSeparator:','";
        $temp_thousandsSeparator="thousandsSeparator:'.'";
        $temp_defaultValue="defaultValue:''";
        $temp_editable="editable:true";
        $temp_number="number:true";

        $coltemplate["general"]="{ ".$temp_width.", ".$temp_sortable.", ".$temp_editable."}";
        $coltemplate["integer"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:0, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:0, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number1"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:1, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number2"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:2, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number3"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:3, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number4"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:4, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number5"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:5, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number6"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:6, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["percent"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:2 }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["currency"]="{ ".$temp_width.", ".$temp_right.", ".$temp_sortable.", formatter:'currency', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:2, ".$temp_defaultValue." }, ".$temp_editable.", editrules: { ".$temp_number." } }";
        $coltemplate["currency2"]="{ ".$temp_width.", ".$temp_right.", ".$temp_sortable.", formatter:'currency', formatoptions:{ ".$temp_decimalSeparator.", ".$temp_thousandsSeparator.", decimalPlaces:0, ".$temp_defaultValue." }, ".$temp_editable.", editrules: { ".$temp_number." } }";
        $coltemplate["number_integer"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ thousandsSeparator:'', decimalPlaces:0, ".$temp_defaultValue." }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number_integer_2"]="{ width:50, ".$temp_right.", ".$temp_sortable.", formatter:'number', formatoptions:{ thousandsSeparator:'', decimalPlaces:0, defaultValue:0 }, ".$temp_editable.", editrules:{ ".$temp_number." } }";
        $coltemplate["number2_default_0"] = "{ width:50, " . $temp_right . ", " . $temp_sortable . ", formatter:'number', formatoptions:{ " . $temp_decimalSeparator . ", " . $temp_thousandsSeparator . ", decimalPlaces:2, defaultValue:'0,00' }, " . $temp_editable . ", editrules:{ " . $temp_number . " } }";

        $coltemplate["date_1"] = "{ " . $temp_width . ", " . $temp_sortable . ", " . $temp_editable . ", editoptions:{
            size: 18, maxlengh: 10, dataInit: function(element) {
                $(element).datepicker({
                    dateFormat:'" . $coltemplate_date_1 . "',
                    changeMonth: true,
                    changeYear: true,
                    onClose: function () { this.focus(); }
                });
                // Set default date here
                $(element).datepicker('setDate', 'today');
            }
        },formatoptions:{ newformat: 'Y-m-d' } }";

        $coltemplate["browsedefault"]="{ searchoptions:{sopt:['eq','bw','bn','cn','nc','ew','en']} }";
		foreach($coltemplate as $key => $val){
			$grid_html .= "\nvar coltemplate_".$key." = ".$val."; ";
        }

        if ($grid["type"] == "grid"){
            $grid_html .= "\nvgrid_comp['" . $grid_id . "'] = 'not_ready';\nvgrid_load['" . $grid_id . "'] = 'not_ready';";
        }
        foreach($grid["var"] as $key => $val){
            $grid_html .= "var " . $grid_id . "_" . $key . " = " . $val . ";";
        }
        $grid_html .= "\n$(function()\n{";

        $grid_html .= "\n\tjQuery(" . $grid_id . "_element).jqGrid({";
        $i = 0;
        foreach($grid["option"] as $key => $val) {
            if ($i != 0){
                $grid_html .= " , ";
            }
            if ($key != "colModel"){
                $grid_html .= " " . $key . " : " . $val . " ";
            }
            elseif ($key == "colModel") {
                $grid_html .= " " . $key . " : [ ";
                $i2 = 0;
                foreach ($grid["colmodel"] as $colmodel) {
                    if ($i2 != 0){
                        $grid_html .= " , ";
                    }
                    $grid_html .= " { ";
                    $i3 = 0;
                    foreach($colmodel as $key2 => $val2) {
                        if ($i3 != 0){
                            $grid_html .= " , ";
                        }
                        $grid_html .= " " . $key2 . " : " . $val2 . " ";
                        $i3++;
                    }
                    $grid_html .= " } ";
                    $i2++;
                }
                $grid_html .= " ] ";
            }
            $i++;
        }
        $grid_html .= "\n\t});";
        if ($grid["filtertoolbar"] == 1){
            $grid_html .= "jQuery(" . $grid_id . "_element).jqGrid('filterToolbar',{searchOperators:true});";
        }
        if ($grid["navgrid"] == 1) {
            $grid_html .= "\n\tjQuery(" . $grid_id . "_element).jqGrid('navGrid'," . $grid_id . "_navgrid,\n\t{";
            $i = 0;
            foreach($grid["nav_option"] as $key => $val) {
                if ($i != 0)
                    $grid_html .= " , ";
                $grid_html .= " " . $key . " : " . $val . " ";
                $i++;
            }
            $grid_html .= "\n\t}, {}, {}, {}, {});";
        }
        $i = 0;
        $group_header_var = "";

        if (!empty($mergedHeaders)) {
            $grid["colHeader"][0] = $mergedHeaders;
        }

        if (array_key_exists('colHeader', $grid)) {
            $i = 0;
            $group_header_var .= "[";
            foreach($grid["colHeader"] as $colHeader) {
                if ($i != 0){
                    $group_header_var .= " , ";
                }
                $group_header_var .= "{";
                $i1 = 0;
                foreach($colHeader as $key => $val) {
                    if ($i1 != 0){
                        $group_header_var .= " , ";
                    }
                    if ($key != "groupHeaders"){
                        $group_header_var .= " '" . $key . "' : " . $val . " ";
                    }
                    elseif ($key == "groupHeaders") {
                        $group_header_var .= " '" . $key . "' : [ ";
                        $i2 = 0;
                        foreach($colHeader["groupHeaders"] as $groupHeaders) {
                            if ($i2 != 0){
                                $group_header_var .= " , ";
                            }
                            $group_header_var .= " { ";
                            $i3 = 0;
                            foreach($groupHeaders as $key => $val) {
                                if ($i3 != 0){
                                    $group_header_var .= " , ";
                                }
                                $group_header_var .= " '" . $key . "' : " . $val . " ";
                                $i3++;
                            }
                            $group_header_var .= " } ";
                            $i2++;
                        }
                        $group_header_var .= " ] ";
                    }
                    $i1++;
                }
                $i++;
                $group_header_var .= " }";
            }
            $group_header_var .= "]";

            $grid_html .= "\nvar groupHeaders = " . $group_header_var .";";
            $grid_html .= "\nfor (var iRow = 0; iRow < groupHeaders.length; iRow++) {";
            $grid_html .= "\n\tjQuery(" . $grid_id . "_element).jqGrid('setGroupHeaders', groupHeaders[iRow]);";
            $grid_html .= "\n}";
        }

        $grid_html .= "\n});\nfunction " . $grid_id . "_cleargrid()\n{\n\t$(function()\n\t{\n\t\tjQuery(" . $grid_id . "_element).jqGrid('clearGridData');\n\t\t" . $grid_id . "_load = 0;\n\t\tactionGridComplete_" . $grid_id . "();\n\t\t" . $grid_id . "_allow_delete = 1;\n\t\tafter_delete_" . $grid_id . "();\n\t});\n}\n</script>";
        $grid_html .= "\n<div id='" . $grid_id . "_area' class='" . $grid["class_area"] . "'>";
        if (!empty($grid["input_search"])){
            $grid_html .= $grid["input_search"];
        }
        $grid_html .= "\n\t<table id='" . $grid_id . "_grid'></table>\n\t<div id='" . $grid_id . "_navgrid'></div>\n\t<span id='" . $grid_id . "_realgrid' class='hiding'></span>";
        $grid_html .= "\n</div>";
        if ($grid["functions_script"] == 1) {
            include(app_path().'/Helpers/grid_functions.php');
            $grid_html .= $grid_functions_html;
        }
        if (!empty($grid["others_script"])) {
            ob_start();
            include(app_path().'/Helpers/grid_scripts.php');
            $gridjs_html = ob_get_contents();
            ob_end_clean();
            $grid_html .= $gridjs_html;
        }

        return $grid_html;
    }
?>
