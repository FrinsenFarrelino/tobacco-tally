<?php

namespace App\Http\Controllers;

use App\Http\Services\CustomerGridService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected $globalActionController;
    protected $customerGridService;

    public function modelName($string)
    {
        $set = "App\\Models\\" . $string;
        return $set;
    }

    function formatCode($stringCode = "", $stringDate = "")
    {
        $formatCode = DB::table('variables')
            ->where('code', $stringCode)
            ->first();

        $separatorSetting = '/';

        if ($formatCode) {
            $format = explode("|", $formatCode->value);
            $initial = $format[0];
            $maxDigit = $format[1];
            $dateFormat = $format[2];
            $header = $format[3];
            $type = $format[4];
            // Jika format[7] ada, gunakan nilainya, jika tidak, gunakan setting dari tabel setting
            $separator = isset($format[5]) ? $format[5] : $separatorSetting;
            $menu_mode = (isset($format[6]) && !empty($format[6])) ? $format[6] : null;
            $formattedCode = $initial;

            // Jika $stringDate kosong, gunakan tanggal sekarang
            $date = empty($stringDate) ? date("Y-m-d") : $stringDate;

            $dateParts = explode("-", $date);
            $year = $dateParts[0];
            $month = $dateParts[1];
            $day = $dateParts[2];
            $ym = "";

            if ($dateFormat == "ym") {
                $y = substr($year, 2, 2);
                $ym = $y . $month;
            } elseif ($dateFormat == "my") {
                $y = substr($year, 2, 2);
                $ym = $month . $y;
            } elseif ($dateFormat == "Y/m") {
                $ym = $year . "/" . $month;
            } elseif ($dateFormat == "y") {
                $y = substr($year, 2, 2);
                $ym = $y;
            }

            // $branch = !empty($stringBranch['code']) ? $stringBranch['code'] : null;

            if (!empty($type)) {
                switch ($type) {
                    case "str-tgl":
                        $formattedCode = $initial . $separator . $ym . $separator;
                        break;
                    case "str":
                        $formattedCode = $initial . $separator;
                        break;
                    case "str-tgl-transaction":
                        $formattedCode = $initial . $separator . $ym . $separator;
                        break;
                    default:
                        $formattedCode = ''; // Handle jika tipe tidak cocok
                        break;
                }
            } elseif (!empty($date_format)) {
                $formattedCode = $initial . $separator . $ym . $separator;
            }

            
            $lastNumber = null;
            if($menu_mode === "transaction") {
                $latestData = DB::table($header)
                    ->latest()->first();
                if($latestData) {
                    $codeFormat = explode("/", $latestData->code);
                    if(count($codeFormat) > 1) {
                        if ($codeFormat[1] !== $ym) {
                            $lastNumber = 0;
                        } else {
                            $lastDigit = explode("0",end($codeFormat));
                            $lastNumber = end($lastDigit);
                        }
                    }
                }
            } else {
                $lastNumber = DB::table($header)
                    ->where('id', '<>', 0)
                    ->count();
            }

            $newNumber = $lastNumber + 1;
            $newNumberDigit = strlen($newNumber);
            if ($newNumberDigit == 0) {
                $newNumberDigit = 1;
            }
            $number = "";
            for ($i = $newNumberDigit; $i < $maxDigit; $i++) {
                $number .= "0";
            }

            $formattedCode .= $number . $newNumber;

            return $formattedCode;
        } else {
            return null;
        }
    }

    public function objResponse($title, $subtitle, $menu, $mode)
    {
        // Access the "home.name" key in the language file
        $title = Lang::get('subMenu')[$title];

        // Access the "home.dashboard.name" key in the language file
        $submodule = Lang::get($subtitle)['submodule'] ?? '';
        $subtitle = Lang::get($subtitle)['name'];

        $objResponse = [
            'title' => $title,
            'subtitle' => $subtitle,
            'submodule' => $submodule,
            'menu' => $menu,
            'mode' => $mode
        ];

        return $objResponse;
    }

    public function setPrivButton($code)
    {
        $setValueFeature = '';
        if (Session::get('user_group')['name'] === 'Admin') {
            $setValueFeature .= '|' . 'add';
            $setValueFeature .= '|' . 'edit';
            $setValueFeature .= '|' . 'delete';
        } else {
            if (Session::has('list_menu') && Session::has('access_menu')) {
                $listMenu = Session::get('list_menu');
                $accessMenu = Session::get('access_menu');

                foreach ($listMenu as $list_menu) {
                    if ($list_menu['code'] == $code) {
                        foreach ($accessMenu as $access_menu) {
                            if ($access_menu['menu_id'] == $list_menu['id']) {
                                if ($access_menu['add'] == true) {
                                    $setValueFeature .= '|' . 'add';
                                }
                                if ($access_menu['edit'] == true) {
                                    $setValueFeature .= '|' . 'edit';
                                }
                                if ($access_menu['delete'] == true) {
                                    $setValueFeature .= '|' . 'delete';
                                }
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        return $setValueFeature;
    }
}
