<?php

namespace App\Http\Services;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CustomerGridService
{
    public function getCustomerBankAccountGrid($customerId, $columnHead)
    {

        $data = DB::table('customers as a')
            ->join('customer_bank_account_details as b', 'b.customer_id', '=', 'a.id')
            ->join('banks as c', 'c.id', '=', 'b.bank_id')
            ->where('a.id', $customerId)
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY a.id) AS id, a.id AS ' . $columnHead[1] . ', c.id AS ' . $columnHead[2] . ', c.name AS ' . $columnHead[3] . ', b.bank_account_number AS ' . $columnHead[4] . ', b.bank_account_name AS ' . $columnHead[5] . '')
            ->get();


        $countData = $data->count();

        $resultData = array('rows' => $data, 'page' => 1, 'records' => $countData, 'total' => $countData);

        $result = array('success' => true, 'data' => $resultData);
        return $result;
    }
}
