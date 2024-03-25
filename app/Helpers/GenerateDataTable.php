<?php
function initializeDataTableModal($action, $field, $output_param, $filter = [], $input_param = [], $id = 'example')
{
    $custom_filter = [];

    $renderDataTable = "var table = $('#" . $id . "').DataTable({";
    $renderDataTable .= "\n\t\t\t scrollCollapse: true,";
    $renderDataTable .= "\n\t\t\t responsive: true,";
    $renderDataTable .= "\n\t\t\t deferRender: true,";
    $renderDataTable .= "\n\t\t\t scrollX: true,";
    $renderDataTable .= "\n\t\t\t search: {
            return: true
        },";
    $renderDataTable .= "\n\t\t\t 'ajax': {";
    $renderDataTable .= "\n\t\t\t 'url': '" . route('ajax-data-table', ['action' => $action]) . "',";
    $renderDataTable .= "\n\t\t\t 'type': 'GET',";
    $renderDataTable .= "\n\t\t\t 'data': function (d) {";
    $renderDataTable .= "\n\t\t\t d.action = '" . $action . "';";
    if (!empty($input_param)) {
        $set_input_param = json_decode($input_param, true);
        // custom filter
        if (!empty($set_input_param)) {
            foreach ($set_input_param as $key => $value) {
                $set_value = $value['query'];
                if ($value['query'] == 'on') {
                    $set_value = 'true';
                }
                $custom_filter[] = array(
                    'key' => $value['key'],
                    'term' => $value['term'],
                    'query' => $set_value,
                );
            }
        }
    }
    if (!empty(json_encode($filter))) {
        $filter = json_decode(json_encode($filter), true);

        foreach ($filter as $key => $value) {
            $custom_filter[] = array(
                'key' => $key,
                'term' => 'equal',
                'query' => $value
            );
        }
    }
    if (!empty($custom_filter)) {
        $renderDataTable .= "\n\t\t\t d.filters = '" . json_encode($custom_filter) . "';";
    }
    $renderDataTable .= "\n\t\t\t },";
    $renderDataTable .= "\n\t\t\t },";
    $renderDataTable .= "\n\t\t\t processing: true,";

    $renderDataTable .= "columns: [";
    foreach ($field as $value) {
        $renderDataTable .= "{ data: '" . strtolower($value['field']) . "', name: '" . $value['name'] . "'},";
    }
    $renderDataTable .= "],";

    $renderDataTable .= "\n\t\t\t language: {";
    $renderDataTable .= "\n\t\t\t paginate: {";
    $renderDataTable .= "\n\t\t\t next: '<i class=\"fa fa-angle-double-right\" aria-hidden=\"true\"></i>',";
    $renderDataTable .= "\n\t\t\t previous: '<i class=\"fa fa-angle-double-left\" aria-hidden=\"true\"></i>'";
    $renderDataTable .= "\n\t\t\t },";
    $renderDataTable .= "\n\t\t\t }";
    $renderDataTable .= "\n\t\t\t });";

    $renderDataTable .= "\n\t\t\t table.on('click', 'tbody tr', (e) => {";
    $renderDataTable .= "\n\t\t\t let classList = e.currentTarget.classList;";
    $renderDataTable .= "\n\t\t\t if (classList.contains('selected')) {";
    $renderDataTable .= "\n\t\t\t classList.remove('selected');";
    $renderDataTable .= "\n\t\t\t }";
    $renderDataTable .= "\n\t\t\t else {";
    $renderDataTable .= "\n\t\t\t table.rows('.selected').nodes().each((row) => row.classList.remove('selected'));";
    $renderDataTable .= "\n\t\t\t classList.add('selected');}";
    $renderDataTable .= "\n\t\t\t });";

    $renderDataTable .= "\n\t\t\t table.on( 'dblclick', 'tbody tr', function () {";
    $renderDataTable .= "\n\t\t\t var getDataRow = table.row( this ).data();";
    if (!empty($output_param)) {
        foreach ($output_param as $parameter) {
            $param = explode("|", $parameter);
            $output_action = "val";
            if (isset($param[2])) {
                if ($param[2] == "html") {
                    $output_action = $param[2];
                }
            }

            $setOutputUI = "getDataRow." . $param[1] . ".toString()";
            if (str_contains($param[1], '-')) {
                $renderOutputs = explode("-", $param[1]);
                $tempOutputUI = "";
                $count = 0;

                foreach ($renderOutputs as $value) {
                    $tempOutputUI .= "getDataRow." . $value . ".toString()";
                    $count++;

                    if ($count < count($renderOutputs)) {
                        $tempOutputUI .= " + ' - ' + ";
                    }
                }
                $setOutputUI = $tempOutputUI;
            }

            $renderDataTable .= "\n\t\t\t $('#" . $param[0] . "')." . $output_action . "(" . $setOutputUI . ");";
        }
    }

    $renderDataTable .= "$('#baseModel').modal('hide');";
    $renderDataTable .= "if(typeof getVal === 'function') {
            getVal();
        } else {
            console.log('No function defined');
        }";
    $renderDataTable .= '})';

    return $renderDataTable;
}

if (!function_exists('renderTableGlobalAjax')) {
    function renderTableGlobalAjax($data_thead, $id_ajax = 'example')
    {
        // Initialize the table structure
        $thead = '';
        $tbody = '';
        $table = '<div>';
        $table .= '<table id="' . $id_ajax . '" class="stripe cell-border table table-bordered" style="width: 100%;">';
        $table .= '<thead>';
        $table .= '<tr>';
        foreach ($data_thead as $data) {
            if ($data == 'action') {
                $thead .= '<th scope="col" data-orderable="false">' . $data . '</th>';
            } else {
                $thead .= '<th scope="col">' . $data . '</th>';
            }
        }
        $table .= $thead;
        $table .= '</tr>';
        $table .= '</thead>';
        $table .= '</table>';
        $table .= '</div>';

        return $table;
    }
}
