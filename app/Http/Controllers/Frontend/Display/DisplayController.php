<?php
namespace App\Http\Controllers\Frontend\Display;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\TokenNumber;

class DisplayController extends Controller
{
    /**
     * Show the application.
     *
     * @param $id
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        $settingData = Setting::find(1);
        $getData = TokenNumber::with('department')
            ->where('id_department', $id)
            ->where('date', date('Y-m-d'))
            ->where('status','active')
            ->orderBy('number', 'desc')
            ->get()
        ;

        return view('frontend.display.index',[
            'data' => $getData,
            'id_department' => $id,
            'setting_data' => $settingData,
        ]);
    }
}
