<?php
namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Base\Department;
use App\Models\User;
use Illuminate\Http\Request;

class ApiTokenController extends Controller
{
    /**
     * API check user online
     *
     * @return \Illuminate\Http\Response
     */
    public function checkUserOnline()
    {
        $getStatus = User::where('is_online', 1)->get();
        $count = count($getStatus);

        return response()->json($count);
    }

    /**
     * API get branch base on department
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function getDepartmentByBranch(Request $request)
    {
        $dept = Department::where('id_branch', $request->id_branch)
            ->get();

        $checkBranchOnline = User::where('id_branch', $request->id_branch)
            ->where('is_online', 1)
            ->get();

        $checkAdministratorOnline = User::where('role', 1)
            ->where('is_online', 1)
            ->get();

        if ($checkBranchOnline->count() > 0){
            $returnData = $dept;
        }else if($checkAdministratorOnline->count() > 0){
            $department = Department::where('id_branch', $request->id_branch)->get();
            $returnData = $department;
        }else{
            $returnData = [];
        }
        return response()->json($returnData);
    }
}
