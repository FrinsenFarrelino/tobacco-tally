<?php

namespace App\Http\Services;


use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class SaleGridService
{
    public function getSaleItemGrid($saleId, $columnHead)
    {

        $data = DB::table('sales as a')
            ->join('sale_item_details as b', 'b.sale_id', '=', 'a.id')
            ->join('items as c', 'c.id', '=', 'b.item_id')
            ->join('units', 'units.id', '=', 'c.unit_id')
            ->where('a.id', $saleId)
            ->selectRaw('ROW_NUMBER() OVER (ORDER BY a.id) AS id, a.id AS ' . $columnHead[1] . ', c.id AS ' . $columnHead[2] . ', c.code AS ' . $columnHead[3] . ', c.name AS ' . $columnHead[4] . ', b.amount AS ' . $columnHead[5] . ', units.name AS ' . $columnHead[6] . ', c.sell_price AS ' . $columnHead[7] . ', b.subtotal AS ' . $columnHead[8] . '')
            ->get();


        $countData = $data->count();

        $resultData = array('rows' => $data, 'page' => 1, 'records' => $countData, 'total' => $countData);

        $result = array('success' => true, 'data' => $resultData);
        return $result;
    }
}
