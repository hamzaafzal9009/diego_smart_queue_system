<?php

namespace App\Http\Controllers\Backend\Users;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Auth;
use Config;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

class UsersController extends Controller
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
     * More info DataTables : https://yajrabox.com/docs/laravel-datatables/master
     *
     * @param Datatables $datatables
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id',
            'image',
            'name',
            'email',
            'role',
            'branch',
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(User::all())
                ->addColumn('image', function (User $data){
                    $getAssetFolder = asset('uploads/' . $data->image);
                    $getImage = '<img src="'.$getAssetFolder.'" width="30px" class="img-circle elevation-2">';
                    return $getImage;
                })
                ->addColumn('action', function (User $data){
                    $routeEdit = route($this->getRoute() . '.edit', $data->id);
                    $routeDelete = route($this->getRoute() . '.delete', $data->id);

                    // Check is administrator
                    if (Auth::user()->hasRole('administrator')) {
                        $button = '<a href="'.$routeEdit.'"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="'.$routeDelete.'" class="delete-button"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a>';
                    } else {
                        $button = '<a href="#"><button class="btn btn-primary disabled"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="#"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a>';
                    }
                    return $button;
                })
                ->addColumn('role', function(User $user) {
                    return Role::where('id', $user->role)->first()->display_name;
                })
                ->addColumn('branch', function (User $data){
                    $branchId = $data->id_branch;
                    if($branchId == null){
                        $branch = "-";
                    }else{
                        $branch = $data->branch->name;
                    }
                    return $branch;
                })
                ->rawColumns(['action', 'image', 'intro'])
                ->toJson();
        }

        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'dom' => 'Bfrtip',
                'buttons' => ['csv', 'excel', 'pdf', 'print'],
            ]);

        return view('backend.users.index', compact('html'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {

        $data = new User();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        if (Auth::user()->hasRole('administrator')) {
            return view('backend.users.form', [
                'data' => $data,
                'role' => Role::orderBy('id')->pluck('display_name', 'id'),
                'branch' => Branch::orderBy('id')->pluck('name', 'id'),
            ]);
        }

        $userBranch = Auth::User()->id_branch;
        return view('backend.users.form', [
            'data' => $data,
            'role' => Role::whereNotIn('id', [1, 2])->orderBy('id')->pluck('display_name', 'id'),
            'branch' => Branch::Where('id', $userBranch)->orderBy('id')->pluck('name', 'id'),
        ]);
    }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'users';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $new = $request->all();
        $this->validator($new, 'create')->validate();
        try {
            $new['password'] = bcrypt($new['password']);
            $createNew = User::create($new);
            if ($createNew) {
                // Attach role
                $createNew->roles()->attach($new['role']);

                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    // image file name example: [news_id]_image.jpg
                    ${'image'} = $createNew->id . "_image." . $file->getClientOriginalExtension();
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                    $createNew->{'image'} = ${'image'};
                } else {
                    $createNew->{'image'} = 'default-user.png';
                }
                $createNew->save();

                // Create is successful, back to list
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }

            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Validator data.
     *
     * @param array $data
     * @param $type
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $type)
    {
        // Determine if password validation is required depending on the calling
        return Validator::make($data, [
            // Add unique validation to prevent for duplicate email while forcing unique rule to ignore a given ID
            'email' => $type == 'create' ? 'email|required|string|max:255|unique:users' : 'required|string|max:255|unique:users,email,' . $data['id'],
            // (update: not required, create: required)
            'password' => $type == 'create' ? 'required|string|min:6|max:255' : '',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = User::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        if (Auth::user()->hasRole('administrator')) {
            return view('backend.users.form', [
                'data' => $data,
                'role' => Role::orderBy('id')->pluck('display_name', 'id'),
                'branch' => Branch::orderBy('id')->pluck('name', 'id'),
            ]);
        }

        $userBranch = Auth::User()->id_branch;
        return view('backend.users.form', [
            'data' => $data,
            'role' => Role::whereNotIn('id', [1, 2])->orderBy('id')->pluck('display_name', 'id'),
            'branch' => Branch::Where('id', $userBranch)->orderBy('id')->pluck('name', 'id'),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $new = $request->all();
        try {
            $currentData = User::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();

                if (!$new['password']) {
                    $new['password'] = $currentData['password'];
                } else {
                    $new['password'] = bcrypt($new['password']);
                }

                if($currentData->role != $new['role']){
                    $currentData->roles()->sync($new['role']);
                }

                // check delete flag: [name ex: image_delete]
                if ($request->get('image_delete') != null) {
                    $new['image'] = null; // filename for db

                    if ($currentData->{'image'} != 'default-user.png') {
                        @unlink(Config::get('const.UPLOAD_PATH') . $currentData['image']);
                    }
                }

                // if new image is being uploaded
                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    // image file name example: [id]_image.jpg
                    ${'image'} = $currentData->id . "_image." . $file->getClientOriginalExtension();
                    $new['image'] = ${'image'};
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                } else {
                    $new['image'] = 'default-user.png';
                }

                // Update
                $currentData->update($new);
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            if(Auth::user()->id != $id) {
                // delete
                $user = User::find($id);
                $user->detachRole($id);
                $user->delete();

                //delete success
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
            }
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_SELF_MESSAGE'));
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }
}
