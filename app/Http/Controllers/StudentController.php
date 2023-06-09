<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Http\Controllers\FacultyController;
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


    public function getAll(Request $request)
    {
        $items = Student::select(
            'student.id as id',
            'student.student_code as student_code',
            'student.prefix_id as prefix_id',
            'student.firstname as firstname',
            'student.surname as surname',
            'student.citizen_id as citizen_id',
            'student.address as address',
            'student.province_id as province_id',
            'student.amphur_id as amphur_id',
            'student.tumbol_id as tumbol_id',
            'student.tel as tel',
            'student.email as email',
            'student.faculty_id as faculty_id',
            'student.department_id as department_id',
            'student.major_id as major_id',
            'student.class_year as class_year',
            'student.class_room as class_room',
            'student.advisor_id as advisor_id',
            'student.gpa as gpa',
            'student.contact1_name as contact1_name',
            'student.contact1_relation as contact1_relation',
            'student.contact1_tel as contact1_tel',
            'student.contact2_name as contact2_name',
            'student.contact2_relation as contact2_relation',
            'student.contact2_tel as contact2_tel',
            'student.blood_group as blood_group',
            'student.congenital_disease as congenital_disease',
            'student.drug_allergy as drug_allergy',
            'student.emergency_tel as emergency_tel',
            'student.height as height',
            'student.weight as weight',
            'student.active as active',
        )
        ->where('student.deleted_at', null);

        // Include
        if($request->includeAll){

            $items->addSelect('province.name_th as province_name',
            'amphur.name_th as amphur_name',
            'tumbol.name_th as tumbol_name',
            'faculty.name_th as faculty_name',
            'department.name_th as department_name',
            'major.name_th as major_name',
            DB::raw('CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'));
            $items->leftJoin('province','province.province_id','=','student.province_id')
            ->leftJoin('amphur','amphur.amphur_id','=','student.amphur_id')
            ->leftJoin('tumbol','tumbol.tumbol_id','=','student.tumbol_id')
            ->leftJoin('faculty','faculty.id','=','student.faculty_id')
            ->leftJoin('department','department.id','=','student.department_id')
            ->leftJoin('major','major.id','=','student.major_id')
            ->leftJoin('teacher','teacher.id','=','student.advisor_id');
        }
        
        if($request->includeAdvisor){
            $items->addSelect(DB::raw('CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'));
            $items->leftJoin('teacher','teacher.id','=','student.advisor_id');;
        }

        if($request->includeFaculty){
            $items->addSelect('faculty.name_th as faculty_name');
            $items->leftJoin('faculty','faculty.id','=','student.faculty_id');;
        }

        if($request->includeDepartment){
            $items->addSelect('department.name_th as department_name');
            $items->leftJoin('department','department.id','=','student.department_id');;
        }

        if($request->includeMajor){
            $items->addSelect('major.name_th as major_name');
            $items->leftJoin('major','major.id','=','student.major_id');;
        }

        if($request->includeProvince){
            $items->addSelect('province.name_th as province_name');
            $items->leftJoin('province','province.province_id','=','student.province_id');;
        }

        if($request->includeAmphur){
            $items->addSelect('amphur.name_th as amphur_name');
            $items->leftJoin('amphur','amphur.amphur_id','=','student.amphur_id');;
        }

        if($request->includeTumbol){
            $items->addSelect('tumbol.name_th as tumbol_name');
            $items->leftJoin('tumbol','tumbol.tumbol_id','=','student.tumbol_id');;
        }

        if($request->includeAdvisor){
            $items->addSelect(DB::raw('CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'));
            $items->leftJoin('teacher','teacher.id','=','student.advisor_id');;
        }


        if ($request->id) {
            $items->where('student.id',$request->id);
        }

        if ($request->student_code) {
            $items->where('student.student_code',$request->student_code);
        }

        if ($request->prefix_id) {
            $items->where('student.prefix_id',$request->prefix_id);
        }

        if ($request->firstname) {
            $items->where('student.firstname','LIKE',"%".$request->firstname."%");
        }

        if ($request->surname) {
            $items->where('student.surname','LIKE',"%".$request->surname."%");
        }

        if ($request->email) {
            $items->where('student.email','LIKE',"%".$request->email."%");
        }

        if ($request->citizen_id) {
            $items->where('student.citizen_id','LIKE',"%".$request->citizen_id."%");
        }

        if ($request->province_id) {
            $items->where('student.province_id',$request->province_id);
        }

        if ($request->amphur_id) {
            $items->where('student.amphur_id',$request->amphur_id);
        }

        if ($request->tumbol_id) {
            $items->where('student.tumbol_id',$request->tumbolid);
        }

        if ($request->faculty_id) {
            $items->where('student.faculty_id',$request->faculty_id);
        }

        if ($request->department_id) {
            $items->where('student.department_id',$request->department_id);
        }

        if ($request->major_id) {
            $items->where('student.major_id',$request->major_id);
        }

        if($request->class_year){
            $items->where('student.class_year',$request->class_year);

        }

        if($request->class_room){
            $items->where('student.class_room',$request->class_room);

        }

        if($request->advisor_id){
            $items->where('student.advisor_id',$request->advisor_id);
        }
        
        if ($request->active) {
            $items->where('student.active',$request->active);
        }

        if($request->orderBy){
            $items = $items->orderBy($request->orderBy, $request->order);
        }else{
            $items = $items->orderBy('id', 'asc');
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
        $item = Student::select(
            'student.id as id',
            'student.student_code as student_code',
            'student.prefix_id as prefix_id',
            'student.firstname as firstname',
            'student.surname as surname',
            'student.citizen_id as citizen_id',
            'student.address as address',
            'student.province_id as province_id',
            'student.amphur_id as amphur_id',
            'student.tumbol_id as tumbol_id',
            'student.tel as tel',
            'student.email as email',
            'student.faculty_id as faculty_id',
            'student.department_id as department_id',
            'student.major_id as major_id',
            'student.class_year as class_year',
            'student.class_room as class_room',
            'student.advisor_id as advisor_id',
            'student.gpa as gpa',
            'student.contact1_name as contact1_name',
            'student.contact1_relation as contact1_relation',
            'student.contact1_tel as contact1_tel',
            'student.contact2_name as contact2_name',
            'student.contact2_relation as contact2_relation',
            'student.contact2_tel as contact2_tel',
            'student.blood_group as blood_group',
            'student.congenital_disease as congenital_disease',
            'student.drug_allergy as drug_allergy',
            'student.emergency_tel as emergency_tel',
            'student.height as height',
            'student.weight as weight',
            'student.active as active',
            'province.name_th as province_name',
            'amphur.name_th as amphur_name',
            'tumbol.name_th as tumbol_name',
            'faculty.name_th as faculty_name',
            'department.name_th as department_name',
            'major.name_th as major_name',
            DB::raw('CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'))
        ->leftJoin('province','province.province_id','=','student.province_id')
        ->leftJoin('amphur','amphur.amphur_id','=','student.amphur_id')
        ->leftJoin('tumbol','tumbol.tumbol_id','=','student.tumbol_id')
        ->leftJoin('faculty','faculty.id','=','student.faculty_id')
        ->leftJoin('department','department.id','=','student.department_id')
        ->leftJoin('major','major.id','=','student.major_id')
        ->leftJoin('teacher','teacher.id','=','student.advisor_id')
        ->where('student.id', $id)
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
            'id' => $student_code,
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

            // $faculty = (new FacultyController)->import($result->faculty_code,$result->faculty_name);

            $faculty = app('App\Http\Controllers\FacultyController')->import($result->faculty_code,$result->faculty_name);

            $result['faculty_id'] = $faculty->id;

            $message = 'Updated student';
            if (!$item) {
                $this->add($result);
                $message = 'Inserted student';
            }
        }
        
        return response()->json(['message' => $message], 200);
    }
}
