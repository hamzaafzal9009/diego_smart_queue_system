<?php
namespace App\Http\Controllers\Backend\Analytic;

use App\Http\Controllers\Controller;
use Auth;
use Config;
use DB;

class AnalyticController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     */
    public function index()
    {
        return view('backend.analytic.index');
    }

    /**
     * Get summary per month analytic
     * @return json response
     */
    public function chartMonthSummary()
    {
        $getAllData = DB::table('token_numbers')
            ->whereYear('date', '=', date("Y"))
            ->whereMonth('date', '=', date("m"))
            ->select('date',
                DB::raw('COUNT(case when `status` = "active" then 0 END) as `active`'),
                DB::raw('COUNT(case when `status` = "done" then 0 END) as `done`'),
                DB::raw('COUNT(case when `status` IS NULL then 0 END) as `pending`'),
                DB::raw('COUNT("status") as `total`')
            )
            ->get();

        return response()->json($getAllData);
    }

    /**
     * Get detail per month analytic
     * @return json response
     */
    public function chartMonthDetail()
    {
        $getAllData = DB::table('token_numbers')
            ->whereYear('date', '=', date("Y"))
            ->whereMonth('date', '=', date("m"))
            ->select('date',
                DB::raw('COUNT(case when `status` = "active" then 0 END) as `active`'),
                DB::raw('COUNT(case when `status` = "done" then 0 END) as `done`'),
                DB::raw('COUNT(case when `status` IS NULL then 0 END) as `pending`'),
                DB::raw('COUNT("status") as `total`')
            )
            ->groupBy('date')
            ->get();
        return response()->json($getAllData);
    }

    /**
     * Get summary per year analytic
     * @return json response
     */
    public function chartYearSummary()
    {
        $getAllData = DB::table('token_numbers')
            ->whereYear('date', '=', date("Y"))
            ->select('date',
                DB::raw('COUNT(case when `status` = "active" then 0 END) as `active`'),
                DB::raw('COUNT(case when `status` = "done" then 0 END) as `done`'),
                DB::raw('COUNT(case when `status` IS NULL then 0 END) as `pending`'),
                DB::raw('COUNT("status") as `total`')
            )
            ->get();

        return response()->json($getAllData);
    }

    /**
     * Get detail per year analytic
     * @return json response
     */
    public function chartYearDetail()
    {
        $getAllData = DB::table('token_numbers')
            ->whereYear('date', '=', date("Y"))
            ->select(
                DB::raw('COUNT(case when `status` = "active" then 0 END) as `active`'),
                DB::raw('COUNT(case when `status` = "done" then 0 END) as `done`'),
                DB::raw('COUNT(case when `status` IS NULL then 0 END) as `pending`'),
                DB::raw('COUNT("status") as `total`'),
                DB::raw("DATE_FORMAT(`date`, '%Y-%m-%d') `new_date`"),
                DB::raw('MONTH(`date`) `month`')
            )
            ->groupby('month')
            ->get();
        return response()->json($getAllData);
    }
}
