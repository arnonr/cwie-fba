<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
const whitelist = ['127.0.0.1', "::1","localhost:8115"];

class StudentController extends Controller
{
    const ERROR_NONE = 0;
    const ERROR_API_FAIL = 1;
    const ERROR_INVALID_CREDENTIALS = 2;
    const ERROR_INTERNAL = 3;
    const ERROR_CREATE_USER = 4;  
    const ERROR_UPDATE_USER = 5;
    const ERROR_NOT_FOUND_STUDENT_CODE = 6;

    public $errorCode;
    public $errorMessage;

    protected $uploadUrl = 'http://143.198.208.110:8115/storage/';

    public function getAll(Request $request)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8115/storage/';
        }

        $items = Team::select(
            'team.id as id',
            'team.department_id as department_id',
            'team.prefix as prefix',
            'team.firstname as firstname',
            'team.surname as surname',
            'team.email as email',
            'team.position_type as position_type',
            'team.position as position',
            'team.detail as detail',
            DB::raw("(CASE WHEN team_file = NULL THEN 'http://localhost:8115/storage/team/scg.png'
            ELSE CONCAT('".$this->uploadUrl."',team_file) END) AS team_file"),
            'team.prefix_en as prefix_en',
            'team.firstname_en as firstname_en',
            'team.surname_en as surname_en',
            'team.position_type_en as position_type_en',
            'team.position_en as position_en',
            'team.detail_en as detail_en',
            'team.level as level',
            'team.is_publish as is_publish',
            'team.deleted_at as delete_at',
            'team.created_at as created_at',
            'team.created_by as created_by',
            'team.updated_at as updated_at',
            'team.updated_by as updated_by',
            'department.title as department_name',
        )
        ->join('department','department.id','=','team.department_id')
        ->where('team.deleted_at', null);

        if ($request->id) {
            $items->where('team.id',$request->id);
        }

        if ($request->department_id) {
            $items->where('team.department_id',$request->department_id);
        }

        if ($request->prefix) {
            $items->where('team.prefix','LIKE',"%".$request->prefix."%");
        }

        if ($request->firstname) {
            $items->where('team.firstname','LIKE',"%".$request->firstname."%");
        }

        if ($request->surname) {
            $items->where('team.surname','LIKE',"%".$request->surname."%");
        }

        if ($request->email) {
            $items->where('team.email','LIKE',"%".$request->email."%");
        }

        if ($request->position_type) {
            $items->where('team.position_type','LIKE',"%".$request->position_type."%");
        }

        if ($request->position) {
            $items->where('team.position','LIKE',"%".$request->position."%");
        }

        // 

        if ($request->prefix_en) {
            $items->where('team.prefix_en','LIKE',"%".$request->prefix_en."%");
        }

        if ($request->firstname_en) {
            $items->where('team.firstname_en','LIKE',"%".$request->firstname_en."%");
        }

        if ($request->surname_en) {
            $items->where('team.surname_en','LIKE',"%".$request->surname_en."%");
        }

        if ($request->position_type_en) {
            $items->where('team.position_type_en','LIKE',"%".$request->position_type_en."%");
        }

        if ($request->position_en) {
            $items->where('team.position_en','LIKE',"%".$request->position_en."%");
        }

        if ($request->level) {
            $items->where('level',$request->level);
        }
        
        if ($request->is_publish) {
            $items->where('team.is_publish',$request->is_publish);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
        }else{
            $items = $items->orderBy('level', 'asc');
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

    public function get($id)
    {
        if(in_array($_SERVER['HTTP_HOST'], whitelist)){
            $this->uploadUrl = 'http://localhost:8115/storage/';
        }

        $item = Team::select(
            'team.id as id',
            'team.department_id as department_id',
            'team.prefix as prefix',
            'team.firstname as firstname',
            'team.surname as surname',
            'team.email as email',
            'team.position_type as position_type',
            'team.position as position',
            'team.detail as detail',
            DB::raw("(CASE WHEN team_file = NULL THEN 'http://localhost:8115/storage/team/scg.png'
            ELSE CONCAT('".$this->uploadUrl."',team_file) END) AS team_file"),
            'team.prefix_en as prefix_en',
            'team.firstname_en as firstname_en',
            'team.surname_en as surname_en',
            'team.position_type_en as position_type_en',
            'team.position_en as position_en',
            'team.detail_en as detail_en',
            'team.level as level',
            'team.is_publish as is_publish',
            'team.deleted_at as delete_at',
            'team.created_at as created_at',
            'team.created_by as created_by',
            'team.updated_at as updated_at',
            'team.updated_by as updated_by',
            'department.title as department_name',
        )
        ->join('department','department.id','=','team.department_id')
        ->where('team.id', $id)
        ->first();
        
        return response()->json([
            'message' => 'success',
            'data' => $item,
        ], 200);
    }

    public function add(Request $request)
    {
        $item = new Student;

        $item->student_code = $request->student_code;
        $item->prefix_id = $request->prefix_id;
        $item->firstname = $request->firstname;
        $item->surname = $request->surname;
        $item->citizen_id = $request->citizen_id;
        $item->faculty_id = $request->faculty_id;
        // $item->department_id = $request->department_id;
        $item->created_by = 'arnonr';
        $item->save();

        $responseData = [
            'message' => 'success',
            'data' => $item,
        ];
        
        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate([
            'id as required',
            'department_id as required',
            'prefix as required',
            'firstname as required',
            'surname as required',
        ]);

        $id = $request->id;
        $item = Team::where('id', $id)->first();

        $pathTeam = null;
        if(($request->team_file != "") && ($request->team_file != 'null') && ($request->team_file != 'undefined')){
            $fileNameTeam = 'team-'.rand(10,100).'-'.$request->file('team_file')->getClientOriginalName();
            $pathTeam = '/team/'.$fileNameTeam;
            Storage::disk('public')->put($pathTeam, file_get_contents($request->team_file));
        }else{
            $pathTeam  = $item->team_file;
        }
        
        $item->team_file = $pathTeam;
        $item->department_id = $request->has('department_id') ? $request->department_id : $item->department_id; 
        $item->prefix = $request->has('prefix') ? $request->prefix : $item->prefix; 
        $item->firstname = $request->has('firstname') ? $request->firstname : $item->firstname; 
        $item->surname = $request->has('surname') ? $request->surname : $item->surname; 
        $item->email = $request->has('email') ? $request->email : $item->email; 
        $item->position_type = $request->has('position_type') ? $request->position_type : $item->position_type; 
        $item->position = $request->has('position') ? $request->position : $item->position; 
        $item->detail =  $request->has('detail') ? $request->detail : $item->detail;
        $item->prefix_en = $request->has('prefix_en') ? $request->prefix_en : $item->prefix_en; 
        $item->firstname_en = $request->has('firstname_en') ? $request->firstname_en : $item->firstname_en; 
        $item->surname_en = $request->has('surname_en') ? $request->surname_en : $item->surname_en; 
        $item->position_type_en = $request->has('position_type_en') ? $request->position_type_en : $item->position_type_en; 
        $item->position_en = $request->has('position_en') ? $request->position_en : $item->position_en; 
        $item->detail_en = $request->has('detail_en') ? $request->detail_en : $item->detail_en;
        $item->is_publish = $request->has('is_publish') ? $request->is_publish : $item->is_publish;
        $item->updated_by = 'arnonr';
        $item->save();

        $responseData = [
            'message' => 'success',
            'data' => $item,
        ];
        
        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $item = Team::where('id', $id)->first();

        $item->level = null;
        $item->is_publish = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $items = Team::where('deleted_at', null)->orderBy('level', 'asc')->get();

        $i = 1; 
        foreach($items as $it){
            $it->level = $i;
            $i++;
            $it->save();
        }

        $responseData = [
            'message' => 'success'
        ];

        return response()->json($responseData, 200);
    }

    public function editLevel($id, Request $request)
    {
        $request->validate([
            'id as required',
            'type as required',
        ]);

        $id = $request->id;
        $type = $request->type;

        $item = Team::where('id', $id)->first();

        $item1 = null;
        if($type == 'IC'){
            $item1 = Team::where('level', $item->level + 1)->first();
        }

        if($type == 'DC'){
            $item1 = Team::where('level', $item->level - 1)->first();
        }

        if($item1 != null){
            $level = $item1->level;
            $level1 = $item->level;

            $item->level = $level;
            $item->save();

            $item1->level = $level1;
            $item1->save();
        }

        $responseData = [
            'message' => 'success',
            // 'data' => $item,
        ];
        
        return response()->json($responseData, 200);
    }

    public function icitAccountApiStudentInfo($student_code)
    {
        $this->errorCode = self::ERROR_NONE;

        $access_token = 'v_6atHl-nF8ZSoN6QQMRPakdbQQIAdQu'; // <----- API - Access Token Here
        
        $data = [
            'id' => student_code,
        ];

        $api_url = 'https://api.account.kmutnb.ac.th/api/account-api/student-info'; // <----- API URL

        $response = Http::timeout(50)->withToken($access_token)->post($api_url, $data);

        // return $response->json();
        
        if($response == false){
            $this->errorCode = self::ERROR_API_FAIL;
            $this->errorMessage = 'HTTP Guzzle error';
        }else{
            $json_data = json_decode($response, true);

            if($json_data['STU_CODE']  !==  "undefined"){
                $this->errorCode = self::ERROR_NOT_FOUND_STUDENT_CODE;
                $this->errorMessage = 'NOT FOUND STUDENT CODE';
            }

            $req = new Request();
            $req->student_code = $json_data['STU_CODE'];
            $req->prefix_id = $json_data['STU_PRE_NAME'];
            $req->firstname = $json_data['STU_FIRST_NAME_THAI'];
            $req->surname = $json_data['STU_LAST_NAME_THAI'];
            $req->citizen_id = $json_data['ID_CARD'];
            $req->faculty_code = $json_data['FAC_CODE'];
            $req->faculty_name = $json_data['FAC_NAME_THAI'];
            $req->department_code = $json_data['DEPT_CODE'];
            $req->department_name = $json_data['DEPT_NAME_THAI'];
            $req->division_code = $json_data['DIV_CODE'];
            $req->division_name = $json_data['DIV_NAME_THAI'];

            return  $req;
            // return [
            //     student_code => $json_data['STU_CODE'],
            //     prefix_id => $json_data['STU_PRE_NAME'],
            //     firstname => $json_data['STU_FIRST_NAME_THAI'],
            //     surname => $json_data['STU_LAST_NAME_THAI'],
            //     citizen_id => $json_data['ID_CARD'],
            //     faculty_code => $json_data['FAC_CODE'],
            //     faculty_name => $json_data['FAC_NAME_THAI'],
            //     department_code => $json_data['DEPT_CODE'],
            //     department_name => $json_data['DEPT_NAME_THAI'],
            //     division_code => $json_data['DIV_CODE'],
            //     division_name => $json_data['DIV_NAME_THAI'],
            // ];
        }

        return $this->errorCode;

    }

    public function import($student_code)
    {
        $message = 'Student already exists';
        $item = Student::where('student_code', $student_code)->first();
        if(!$item){
            $result = $this->icitAccountApiStudentInfo($student_code);

            $faculty = (new FacultyController)->import(result.faculty_code,result.faculty_name);

            $result['faculty_id'] = $faculty->id;

            $message = 'Updated student';
            if (!$item) {
                $this->add($req);
                $message = 'Inserted student';
            }
        }
        
        return response()->json(['message' => message], 200);
    }
}
