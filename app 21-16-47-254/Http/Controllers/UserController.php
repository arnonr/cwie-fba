<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Carbon\Carbon;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function get($id)
    {
        // User DB
        $userDB = User::select(
                'users.id as id',
                'users.email as email',
                'users.username as username',
                'users.prefix as prefix',
                'users.firstname as firstname',
                'users.lastname as lastname',
                'users.type as type',
                'users.avatar as avatar',
                'users.status as status',
                'department.id as departmentID',
                'department.name as departmentName',
            )
            ->where('users.id', $id)
            ->first();

        $data = [
            'userID' => $userDB->id,
            'email' => $userDB->email ? $userDB->email : '',
            'prefix' => $userDB->prefix ? $userDB->prefix : '',
            'firstname' => $userDB->firstname,
            'lastname' => $userDB->lastname,
            'type' => $userDB->type,
            'status' => $userDB->status,
            'avatar' =>  $userDB->avatar ? $userDB->avatar : '',
        ];
        
        $tokenResult = $userDB->createToken('Personal Access Token');
        $token = $tokenResult->token;
 
        $token->expires_at = Carbon::now()->addWeeks(1);
        $token->save();

        $ability = [
            [
                'subject'=> 'Auth',
                'action'=> 'manage',
            ],
            [
                'subject'=> 'Auth',
                'action'=> 'read',
            ]
        ];

        if($userDB->status == 1){
            $role = 'wating-approve-user';

            array_push($ability, [
                'action' => 'manage',
                'subject'=> 'WatingApproveUser',
            ]);
        }else if($userDB->status == 2){

            array_push($ability, [
                'action' => 'manage',
                'subject'=> 'User',
            ]);

            if($userDB->type == 'staff'){
                $role = 'staff';
                array_push($ability, [
                    'action' => 'manage',
                    'subject'=> 'StaffUser',
                ]);
            }else if($userDB->type == 'admin'){
                $role = 'admin';
                array_push($ability, [
                    'action' => 'manage',
                    'subject'=> 'AdminUser',
                ]);
            }else{

            }
        }else if ($userDB->status == 3){
            $role = 'block';
            array_push($ability, [
                'action' => 'manage',
                'subject'=> 'BlockUser',
            ]);
        }else{

        }

        $userData = [
            'userID' => $userDB->id,
            'email' => $userDB->email,
            'username' => $userDB->username,
            'fullName' => $userDB->firstname.' '.$userDB->lastname,
            'avatar' => $userDB->avatar,
            'type' => $userDB->type,
            'status' => $userDB->status,
            'department' => ['id' => $userDB->departmentID,'name' => $userDB->departmentName],
            'role' => $role,
            'ability' => $ability,
        ];

        return response()->json([
            'message' => 'success',
            'user' => $data,
            'userData' => $userData,
            'accessToken' => $tokenResult->accessToken,
            'token_type' => 'Bearer',
            'expires_at' => Carbon::parse(
                $tokenResult->token->expires_at
            )->toDateTimeString()
        ], 200);
    }

    public function getAll(Request $request)
    {
        // User DB
        $items = User::select(
            'user.id as id',
            'user.username as username',
            'user.name as name',
            'user.tel as tel',
            'user.email as email',
            'user.citizen_id as citizen_id',
            'user.account_type as account_type',
            'user.active as active',
            'user.blocked_at as blocked_at',
        )
        ->where('user.deleted_at', null);

        if ($request->id) {
            $items->where('user.id', $request->id);
        }

        if ($request->username) {
            $items->where('user.username', $request->username);
        }

        if ($request->name) {
            $items->where('user.name','LIKE',"%".$request->name."%");
        }

        if ($request->citizen_id) {
            $items->where('user.citizen_id', $request->citizen_id);
        }

        if ($request->account_type) {
            $items->where('user.account_type', $request->account_type);
        }

        if ($request->active) {
            $items->where('user.active', $request->active);
        }

        if ($request->blocked_at) {
            $items->where('user.blocked_at', $request->blocked_at);
        }


        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
            
        }else{
            $items = $items->orderBy('id', 'desc');
        }
    
        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : $count;
        $currentPage = $request->currentPage ? $request->currentPage : 1;

        $totalPage = ceil($count /$perPage) == 0 ? 1 : ceil($count / $perPage);
        $offset = $perPage * ($currentPage - 1);
        $items = $items->skip($offset)->take($perPage);
        $items = $items->get();
    
        return response()->json([
            'message' => 'success',
            'data' => $items,
            'totalPage' => $totalPage,
            'totalData' => $count,
        ], 200);
    }

    public function add(Request $request)
    {
        $request->validate([
            'username as required',
            'name as required',
            'email as required',
            'account_type as required',
        ]);
        
        // $data = $request->all();
        // $data['column9'] = 'aaaa';
        // User::create($data);

        // User::create([
        //     'username' => $request->username,
        //     'name' => $request->name,
        //     'email' => $request->email,
        //     'citizen_id' => $request->citizen_id,
        //     'account_type' => $request->account_type,
        // ]);

        $data = new User;
        $data->username = $request->username;
        $data->name = $request->name;
        $data->email = $request->email;
        $data->citizen_id = $request->citizen_id;
        $data->account_type = $request->account_type;
        $data->save();

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate([
            'id as required',
        ]);

        $data = User::where('id', $id)->update($request->all());

        // $data->email = $request->email ? $request->email : $data->email;
        // $data->account_type = $request->account_type ? $request->account_type : $data->account_type;
        // $data->active = $request->active ? $request->active : $data->active;
        // $data->citizen_id = $request->citizen_id ? $request->citizen_id : $data->citizen_id;
        // $data->name = $request->name ? $request->name : $data->name;
        // $data->tel = $request->tel ? $request->tel : $data->tel;
        // $data->blocked_at = $request->blocked_at ? $request->blocked_at : $data->blocked_at;
        // $data->save();

        $responseData = [
            'message' => 'success',
            'data' => $data,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $data = User::where('id', $id)->first();
        
        $data->deleted_at = Carbon::now();
        $data->save();

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }

    public function getIcitAccount($id) 
    {
        $access_token = 'v_6atHl-nF8ZSoN6QQMRPakdbQQIAdQu'; // <----- API - Access Token Here
        // 
        $data = [
            'username' => $id,  // <----- Password for authen
        ];

        $api_url = 'https://api.account.kmutnb.ac.th/api/account-api/user-info'; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $data);

        if($response != false){
            if ($response['api_status_code'] == "201") {
                return response()->json([
                    'message' => 'success',
                    'username' => $response['userInfo']['username'],
                    'name' => $response['userInfo']['displayname'],
                    'email' => $response['userInfo']['email'],
                    'citizen_id' => '',
                    'account_type' => $response['userInfo']['account_type'] == 'personel' ? 3 : 1,
                    'icit_account_type' => $response['userInfo']['account_type'],
                ], 200);
            } else if ($response['api_status_code'] == "501") {
                return response()->json([
                    'message' => 'ไม่พบบัญชีผู้ใช้งาน',
                ], 501);
            } else {
                return $response;
            }
        }else{
            return response()->json([
                'message' => 'API ICIT ACCOUNT ERROR',
            ], 503);
        }
    }

    public function getImportIcitAccount($id) {

        $response = $this->getIcitAccount($id);
        $account = json_decode($response->getContent(), true);

        $item = User::where('username', $account['username'])->first();

        $req = new Request();
        $req->username = $account['username'];
        $req->name = $account['name'];
        $req->email = $account['email'];
        $req->citizen_id = $account['citizen_id'];
        $req->account_type =$account['account_type'];
        $req->icit_account_type = $account['icit_account_type'];

        $saveObj = !$item ?  $this->add($req) : $this->edit($item->id, $req);

        return $saveObj;
      }
    
}
