<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AuthController;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;
use App\Http\Requests\LoginRequest;
use App\Models\AccessMenu;
use App\Models\Menu;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\UserGroup;
use App\Models\Warehouse;
use Carbon\Carbon;

class DashboardController extends GlobalController
{
    private $globalVariable;
    protected $globalActionController;

    public function __construct(GlobalVariable $globalVariable, GlobalActionController $globalActionController)
    {
        $this->globalVariable = $globalVariable;
        $this->globalActionController = $globalActionController;
        $this->globalVariable->ModuleGlobal('home', 'dashboard', '/', 'dashboard', 'dashboard');
    }

    public function dashboard(Request $request)
    {
        $listMenu = Session::get('list_menu');
        $user_group = Session::get('user_group');

        $formData['list_menus'] = $listMenu;
        // dd($formData);
        $formData['title'] = 'Home';

        // data purchase section
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetPurchase);
        $result = $this->getData($set_request);
        $totalPurchaseNotApproved = 0;
        $totalPurchaseApproved = 0;

        foreach ($result['data'] as $dataPurchase) {
            if($dataPurchase['is_approve'] !== true) {
                $totalPurchaseNotApproved++;
            } else {
                $totalPurchaseApproved++;
            }
        }
        $formData['totalPurchaseNotApproved'] = $totalPurchaseNotApproved;
        $formData['totalPurchaseApproved'] = $totalPurchaseApproved;
        // end of data purchase section

        // data sale section
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetSale);
        $result = $this->getData($set_request);
        $totalSaleNotApproved = 0;
        $totalSaleApproved = 0;

        foreach ($result['data'] as $dataSale) {
            if($dataSale['is_approve'] !== true) {
                $totalSaleNotApproved++;
            } else {
                $totalSaleApproved++;
            }
        }
        $formData['totalSaleNotApproved'] = $totalSaleNotApproved;
        $formData['totalSaleApproved'] = $totalSaleApproved;
        // end of data sale section

        // data warehouse section
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetOutgoingItem);
        $result = $this->getData($set_request);
        $totalOutgoing = 0;
        $totalIncoming = 0;
        $totalOnTheWay = 0;

        foreach ($result['data'] as $dataOutgoing) {
            if($dataOutgoing['is_approve_1'] == true && $dataOutgoing['is_approve_2'] == false) {
                $totalOnTheWay++;
            }
            if($dataOutgoing['is_approve_1'] == true && $dataOutgoing['is_approve_2'] == true) {
                $totalIncoming++;
            }
            if($dataOutgoing['is_approve_1'] == false && $dataOutgoing['is_approve_2'] == false) {
                $totalOutgoing++;
            }
        }
        $formData['totalOutgoing'] = $totalOutgoing;
        $formData['totalIncoming'] = $totalIncoming;
        $formData['totalOnTheWay'] = $totalOnTheWay;
        // end of data warehouse section

        // data stock
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetWarehouse);
        $result = $this->getData($set_request);
        $formData['dataWarehouses'] = $result['data'];
        // end of data stock

        // data top selling
        $set_request = SetRequestGlobal(action: 'getSaleDetail');
        $result = $this->getData($set_request);

        // Inisialisasi array untuk menyimpan jumlah penjualan tiap item
        $itemSales = [];

        // Loop melalui data dan menghitung jumlah penjualan tiap item
        foreach ($result['data'] as $saleItem) {
            $itemName = $saleItem['item_name'];
            $amount = $saleItem['amount'];

            if (!isset($itemSales[$itemName])) {
                $itemSales[$itemName] = 0;
            }

            $itemSales[$itemName] += $amount;
        }

        // Fungsi untuk mengurutkan array berdasarkan jumlah penjualan (descending)
        arsort($itemSales);

        // Array baru untuk menyimpan data dengan model yang diinginkan
        $newArray = [];

        // Loop melalui data yang sudah dihitung jumlah penjualannya
        foreach ($itemSales as $itemName => $amount) {
            // Buat sub-array dengan kunci "item_name" dan "amount"
            $newArray[] = [
                'item_name' => $itemName,
                'amount' => $amount,
            ];
        }

        // Output array yang sudah diurutkan
        $formData['topSellings'] = $newArray;
        // end of data top selling

        // per month
        $today = Carbon::today();
        // Get the first day of the current month
        $first_day_of_the_current_month = Carbon::parse($today)->firstOfMonth()->toDateString();

        // Get the last day of the current month
        $last_day_of_the_current_month = Carbon::parse($today)->endOfMonth()->toDateString();
        $start_date = Carbon::parse($first_day_of_the_current_month)->format('Y-m-d');
        $end_date = Carbon::parse($last_day_of_the_current_month)->format('Y-m-d');

        $filter = array(
            'start_date' => $start_date,
            'end_date' => $end_date
        );

        // data total sale
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetSale, filter:$filter);
        // dd($set_request);
        $result = $this->getData($set_request);

        // dd($result);
        $totalSalesThisMonth = 0;
        foreach($result['data'] as $data) {
            $total = strtok($data['total'], ',');
            $grandtotal = str_replace(['Rp ','Rp.', '.', ',', ' '], '', $total);
            $totalSalesThisMonth += intval($grandtotal);
        }
        $formattedTotalSalesThisMonth = 'Rp. ' . number_format($totalSalesThisMonth, 0, ',', '.');
        $formData['total_sales_this_month'] = $formattedTotalSalesThisMonth;
        // end of data total sale

        // data total purchase
        $set_request = SetRequestGlobal(action: $this->globalVariable->actionGetPurchase, filter:$filter);
        // dd($set_request);
        $result = $this->getData($set_request);

        // dd($result);
        $totalPurchaseThisMonth = 0;
        foreach($result['data'] as $data) {
            $total = strtok($data['total'], ',');
            $grandtotal = str_replace(['Rp ','Rp.', '.', ',', ' '], '', $total);
            $totalPurchaseThisMonth += intval($grandtotal);
        }
        $formattedTotalPurchaseThisMonth = 'Rp. ' . number_format($totalPurchaseThisMonth, 0, ',', '.');
        $formData['total_purchase_this_month'] = $formattedTotalPurchaseThisMonth;
        // end of data total purchase

        // dd($formData);

        // data total profit
        $totalProfit = $totalSalesThisMonth - $totalPurchaseThisMonth;
        // dd($totalProfit < 0);
        if ($totalProfit < 0) {
            $is_profit_minus = true;
        } else {
            $is_profit_minus = false;
        }
        if($is_profit_minus == true) {
            $formattedTotalProfit = '- Rp. ' . number_format(abs($totalProfit), 0, ',', '.');
        } else {
            $formattedTotalProfit = 'Rp. ' . number_format($totalProfit, 0, ',', '.');
        }

        $formData['is_profit_minus'] = $is_profit_minus;
        $formData['total_profit'] = $formattedTotalProfit;
        // end of data total profit

        // total sale purchase in 1 year
        // Find the latest month and year from your sales and purchases data
        $latestDate = max([
            Carbon::parse(Sale::max('date')),
            Carbon::parse(Purchase::max('date')),
        ]);

        // Calculate the start date 1 year before the latest date
        $startDate = $latestDate->copy()->subYear()->startOfMonth();

        // Initialize an empty array to store monthly data
        $monthlyData = [];

        // Loop through months from the start date to the latest date
        $currentDate = $latestDate->copy()->startOfMonth();
        while ($startDate->lessThanOrEqualTo($currentDate)) {
            $monthYear = $startDate->format('M Y');
            $monthlyData[$monthYear] = [
                'month_year' => $monthYear,
                'total_sales' => 0,
                'total_purchases' => 0,
            ];

            // Move to the next month
            $startDate->addMonth();
        }

        // Fetch total sales data in the last 12 months
        $salesData = Sale::selectRaw("to_char(date, 'Mon YYYY') AS month_year, SUM(SUBSTRING(REPLACE(REPLACE(total, '.', ''), 'Rp ', ''), '[0-9]+')::integer) AS total_sales")
            ->where('is_approve', true)
            // ->whereBetween('date', [$startDate, $currentDate])
            ->groupByRaw("to_char(date, 'YYYY-MM'), to_char(date, 'Mon YYYY')")
            ->orderByRaw("to_char(date, 'YYYY-MM') DESC")
            ->limit(12)
            ->get();

        // Fetch total purchases data in the last 12 months
        $purchasesData = Purchase::selectRaw("to_char(date, 'Mon YYYY') AS month_year, SUM(SUBSTRING(REPLACE(REPLACE(total, '.', ''), 'Rp ', ''), '[0-9]+')::integer) AS total_purchases")
            ->where('is_approve', true)
            // ->whereBetween('date', [$startDate, $currentDate])
            ->groupByRaw("to_char(date, 'YYYY-MM'), to_char(date, 'Mon YYYY')")
            ->orderByRaw("to_char(date, 'YYYY-MM') DESC")
            ->limit(12)
            ->get();

        // Preprocess sales data
        $salesProcessed = [];
        foreach ($salesData as $sale) {
            $salesProcessed[$sale->month_year] = $sale->total_sales;
        }

        // Preprocess purchases data
        $purchasesProcessed = [];
        foreach ($purchasesData as $purchase) {
            $purchasesProcessed[$purchase->month_year] = $purchase->total_purchases;
        }

        // Merge sales and purchases data into the monthlyData array
        foreach (array_merge(array_keys($salesProcessed), array_keys($purchasesProcessed)) as $month_year) {
            $monthlyData[$month_year] = [
                'month_year' => $month_year,
                'total_sales' => $salesProcessed[$month_year] ?? 0,
                'total_purchases' => $purchasesProcessed[$month_year] ?? 0,
            ];
        }

        // Sort the monthlyData array by month and year
        uksort($monthlyData, function ($a, $b) {
            return $this->monthYearToNumber($a) <=> $this->monthYearToNumber($b);
        });

        $formData['monthlyData'] = $monthlyData;
        // end of total sale purchase in 1 year

        // admin lombok
        if ($user_group['name'] === 'Admin Lombok' && $user_group['branch_id'] === 1) {
            return view('dashboard-adm-lombok', $formData);
        }
        // admin bojonegoro
        if ($user_group['name'] === 'Admin Bojonegoro' && $user_group['branch_id'] === 2) {
            return view('dashboard-adm-bjn', $formData);
        }
        // admin warehouse
        if (str_contains($user_group['name'], 'Warehouse')) {
            return view('dashboard-adm-wh', $formData);
        }
        // super admin
        if($user_group['name'] === 'Admin') {
            return view('dashboard-adm', $formData);
        }
        // manager
        if (str_contains($user_group['name'], 'Manager')) {
            return view('dashboard-manager', $formData);
        }

        return redirect()->route('error')->with('message', 'You do not have permission to access this page.');

    }

    function monthYearToNumber($month_year)
    {
        $date = Carbon::createFromFormat('M Y', $month_year);
        return $date->year * 12 + $date->month;
    }
}
