<?php
    function SetRequestGlobal($action=null, $requestData = null, $filter = null, $formatCode = null, $manualCode = null, $groupBy = null, $orderBy = null, $sort = 'asc', $limit = null, $additional_action=null, $additional_request=null, $search=null, $input_param=null, $custom_filters = [], $get_data = '')
    {
        $set_request = [];

        //Check if there is an existing request
        if($action != null)
        {
            $set_request['action'] = $action;
        }
        if($additional_request != null)
        {
            foreach ($additional_request as $key => $value) {
                $set_request[$key] = $value;
            }
        }
        if($additional_action != null)
        {
            $set_request['additional_action'] = $additional_action;
        }
        if($filter!==null)
        {
            $set_request['filters'] = $filter;
        }
        $set_request['filters']['sort'] = $sort;
        if($orderBy != null)
        {
            $set_request['filters']['order_by'] = $orderBy;
        }
        if($search != null)
        {
            $set_request['search'] = $search;
        }
        if($requestData!==null)
        {
            if(is_array($requestData))
            {
                $set_request['requestData'] = $requestData;
            }
            else
            {
                if($requestData->get('columnHead') != '' || $requestData->get('columnHead') != null)
                {
                    $set_request['columnHead'] = $requestData->get('columnHead');
                }
                $set_request['requestData'] = $requestData->all();
            }

        }
        if($formatCode!==null)
        {
            $set_request['requestData']['format_code'] = $formatCode;
        }
        if($manualCode !== null)
        {
            $set_request['requestData']['manual_code'] = $manualCode;
        }
        if($input_param != null)
        {
            $set_request['input_param'] = $input_param;
        }
        if($groupBy!==null)
        {
            $set_request['filters']['group_by'] = $groupBy;
        }
        if($limit!==null)
        {
            $set_request['filters']['limit'] = $limit;
        }

        if(!empty($custom_filters))
        {
            $set_request['filters']['custom_filters'] = $custom_filters;
        }
        if(!empty($get_data))
        {
            $set_request['filters']['get_data'] = $get_data;
        }

        return $set_request;
    }
?>
