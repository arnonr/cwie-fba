<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Database\Query\JoinClause;

use App\Models\Student;
use App\Http\Controllers\FacultyController;
use Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Laravel\Sanctum\PersonalAccessToken;
const whitelist = ["127.0.0.1", "::1", "localhost:8117"];

class StudentController extends Controller
{
    protected $uploadUrl = "http://co-op.fba.kmutnb.ac.th/storage/";
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
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }

        $items = Student::select(
            "student.id as id",
            "student.student_code as student_code",
            "student.prefix_id as prefix_id",
            "student.firstname as firstname",
            "student.surname as surname",
            "student.citizen_id as citizen_id",
            "student.address as address",
            "student.province_id as province_id",
            "student.amphur_id as amphur_id",
            "student.tumbol_id as tumbol_id",
            "student.tel as tel",
            "student.email as email",
            "student.faculty_id as faculty_id",
            "student.department_id as department_id",
            "student.major_id as major_id",
            "student.class_year as class_year",
            "student.class_room as class_room",
            "student.advisor_id as advisor_id",
            "student.gpa as gpa",
            "student.contact1_name as contact1_name",
            "student.contact1_relation as contact1_relation",
            "student.contact1_tel as contact1_tel",
            "student.contact2_name as contact2_name",
            "student.contact2_relation as contact2_relation",
            "student.contact2_tel as contact2_tel",
            "student.blood_group as blood_group",
            "student.congenital_disease as congenital_disease",
            "student.drug_allergy as drug_allergy",
            "student.emergency_tel as emergency_tel",
            "student.height as height",
            "student.weight as weight",
            "student.active as active",
            "student.status_id as status_id",
            DB::raw(
                "(CASE WHEN photo_file = NULL THEN ''
             ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',photo_file) END) AS photo_file"
            )
        )->where("student.deleted_at", null);

        // Include
        if ($request->includeAll) {
            $items->addSelect(
                "province.name_th as province_name",
                "amphur.name_th as amphur_name",
                "tumbol.name_th as tumbol_name",
                "faculty.name_th as faculty_name",
                "department.name_th as department_name",
                "major.name_th as major_name",
                "prefix_name.prefix_name as prefix_name",
                DB::raw(
                    'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
                )
            );
            $items
                ->leftJoin(
                    "province",
                    "province.province_id",
                    "=",
                    "student.province_id"
                )
                ->leftJoin(
                    "amphur",
                    "amphur.amphur_id",
                    "=",
                    "student.amphur_id"
                )
                ->leftJoin(
                    "tumbol",
                    "tumbol.tumbol_id",
                    "=",
                    "student.tumbol_id"
                )
                ->leftJoin("faculty", "faculty.id", "=", "student.faculty_id")
                ->leftJoin(
                    "department",
                    "department.id",
                    "=",
                    "student.department_id"
                )
                ->leftJoin("major", "major.id", "=", "student.major_id")
                ->leftJoin("teacher", "teacher.id", "=", "student.advisor_id")
                ->leftJoin(
                    "prefix_name",
                    "prefix_name.id",
                    "=",
                    "student.prefix_id"
                );
        }

        if ($request->includeForm) {
            if ($request->semester_id) {
                $items->addSelect(
                    "form.semester_id as semester_id",
                    "form.id as form_id",
                    "request_document_date",
                    "request_document_number",
                    "max_response_date",
                    "confirm_response_at",
                    "send_document_date",
                    "send_document_number",
                    "company.name_th as company_name",
                    "form.response_province_id as response_province_id",
                    DB::raw(
                        'CONCAT(supervision.prefix,supervision.firstname," ", supervision.surname) AS supervision_name'
                    )
                );
                // $items->addSelect('form.* as form');
                $items->leftJoin("form", function ($join) {
                    $join
                        ->on("form.student_id", "=", "student.id")
                        ->where("form.active", 1);
                });
                $items->leftJoin("company", function ($join) {
                    $join->on("form.company_id", "=", "company.id");
                });

                $items->leftJoin("teacher as supervision", function ($join) {
                    $join->on("form.supervision_id", "=", "supervision.id");
                });
                $items->where("form.semester_id", $request->semester_id);

                if ($request->supervision_id) {
                    $items->where(
                        "form.supervision_id",
                        $request->supervision_id
                    );
                }

                if ($request->book_status) {
                    if ($request->book_status == 1) {
                        $items->whereNull("form.request_document_number");
                    }

                    if ($request->book_status == 2) {
                        $items->whereNotNull("form.request_document_number");
                    }

                    if ($request->book_status == 3) {
                        $items->whereNotNull("form.request_document_number");
                        $items->whereNull("form.send_document_number");
                    }

                    if ($request->book_status == 4) {
                        $items->whereNotNull("form.request_document_number");
                        $items->whereNotNull("form.send_document_number");
                    }
                }

                if ($request->approve_status) {
                    if ($request->approve_status == 1) {
                        $items->whereNull("form.advisor_verified_at");
                    }

                    if ($request->approve_status == 2) {
                        $items->whereNotNull("form.advisor_verified_at");
                    }

                    if ($request->approve_status == 3) {
                        $items->whereNotNull("form.advisor_verified_at");
                        $items->whereNull("form.chairman_approved_at");
                    }

                    if ($request->approve_status == 4) {
                        $items->whereNotNull("form.chairman_approved_at");
                    }

                    if ($request->approve_status == 5) {
                        $items->whereNotNull("form.chairman_approved_at");
                        $items->whereNull("form.faculty_confirmed_at");
                    }

                    if ($request->approve_status == 6) {
                        $items->whereNotNull("form.faculty_confirmed_at");
                    }

                    if ($request->approve_status == 7) {
                        $items->whereNotNull("form.response_send_at");
                        $items->whereNull("form.confirm_response_at");
                    }

                    if ($request->approve_status == 8) {
                        $items->whereNotNull("form.confirm_response_at");
                    }
                }

                if ($request->plan_status) {
                    if ($request->plan_status == 1) {
                        $items->whereNotNull("form.plan_send_at");
                        $items->whereNull("form.plan_accept_at");
                    }

                    if ($request->plan_status == 2) {
                        $items->whereNotNull("form.plan_accept_at");
                    }
                }

                if ($request->includeVisit) {
                    $items->addSelect("visit.visit_id as visit_id");
                    $items->addSelect("visit_status as visit_status");
                    $items->addSelect("visit_date as visit_date");
                    $items->addSelect("visit_time as visit_time");
                    $items->addSelect(
                        "visit_reject_status_id as visit_reject_status_id"
                    );
                    $items->addSelect(
                        "visit.document_number as visit_document_number"
                    );

                    if ($request->visit_status) {
                        if ($request->visit_status == 1) {
                            $items->join("visit", function ($join) {
                                $join
                                    ->on("form.id", "=", "visit.form_id")
                                    ->where("visit.active", 1);
                            });
                        } elseif ($request->visit_status == 11) {
                            $items
                                ->join("visit", function ($join) {
                                    $join
                                        ->on("form.id", "=", "visit.form_id")
                                        ->where("visit.active", 1);
                                })
                                ->where("visit.visit_status", 1);
                        } elseif ($request->visit_status == 2) {
                            $items
                                ->join("visit", function ($join) {
                                    $join
                                        ->on("form.id", "=", "visit.form_id")
                                        ->where("visit.active", 1);
                                })
                                ->where("visit.visit_status", ">", 1);
                        } elseif ($request->visit_status == 21) {
                            $items
                                ->join("visit", function ($join) {
                                    $join
                                        ->on("form.id", "=", "visit.form_id")
                                        ->where("visit.active", 1);
                                })
                                ->where("visit.visit_status", "=", 2);
                        } elseif ($request->visit_status == 3) {
                            $items
                                ->join("visit", function ($join) {
                                    $join
                                        ->on("form.id", "=", "visit.form_id")
                                        ->where("visit.active", 1);
                                })
                                ->where("visit.visit_status", ">", 2);
                        } elseif ($request->visit_status == 31) {
                            $items->join("visit", function ($join) {
                                $join
                                    ->on("form.id", "=", "visit.form_id")
                                    ->where("visit.active", 1)
                                    ->where("visit.visit_status", "=", 3);
                            });
                        } elseif ($request->visit_status == 4) {
                            $items->join("visit", function ($join) {
                                $join
                                    ->on("form.id", "=", "visit.form_id")
                                    ->where("visit.active", 1)
                                    ->where("visit.visit_status", ">", 3);
                            });
                        } elseif ($request->visit_status == 41) {
                            $items->join("visit", function ($join) {
                                $join
                                    ->on("form.id", "=", "visit.form_id")
                                    ->where("visit.active", 1)
                                    ->where("visit.visit_status", "=", 4);
                            });
                        } else {
                            // status = 0
                            $items
                                ->leftJoin("visit", function ($join) {
                                    $join->on("form.id", "=", "visit.form_id");
                                })
                                ->whereNull("visit.visit_id");
                        }
                    } elseif ($request->visit_status === "0") {
                        $items
                            ->leftJoin("visit", function ($join) {
                                $join->on("form.id", "=", "visit.form_id");
                            })
                            ->whereNull("visit.visit_id");
                    } else {
                        $items->leftJoin("visit", function ($join) {
                            $join
                                ->on("form.id", "=", "visit.form_id")
                                ->where("visit.active", 1);
                        });
                    }
                }

                // whereNotNull()
            }
        }

        if ($request->includePrefixName) {
            $items->addSelect("prefix_name.prefix_name as prefix_name");
            $items->leftJoin(
                "prefix_name",
                "prefix_name.id",
                "=",
                "student.prefix_id"
            );
        }

        if ($request->includeAdvisor) {
            $items->addSelect(
                DB::raw(
                    'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
                )
            );
            $items->leftJoin(
                "teacher",
                "teacher.id",
                "=",
                "student.advisor_id"
            );
        }

        if ($request->includeFaculty) {
            $items->addSelect("faculty.name_th as faculty_name");
            $items->leftJoin(
                "faculty",
                "faculty.id",
                "=",
                "student.faculty_id"
            );
        }

        if ($request->includeDepartment) {
            $items->addSelect("department.name_th as department_name");
            $items->leftJoin(
                "department",
                "department.id",
                "=",
                "student.department_id"
            );
        }

        if ($request->includeMajor) {
            $items->addSelect("major.name_th as major_name");
            $items->leftJoin("major", "major.id", "=", "student.major_id");
        }

        if ($request->includeProvince) {
            $items->addSelect("province.name_th as province_name");
            $items->leftJoin(
                "province",
                "province.province_id",
                "=",
                "student.province_id"
            );
        }

        if ($request->includeAmphur) {
            $items->addSelect("amphur.name_th as amphur_name");
            $items->leftJoin(
                "amphur",
                "amphur.amphur_id",
                "=",
                "student.amphur_id"
            );
        }

        if ($request->includeTumbol) {
            $items->addSelect("tumbol.name_th as tumbol_name");
            $items->leftJoin(
                "tumbol",
                "tumbol.tumbol_id",
                "=",
                "student.tumbol_id"
            );
        }

        if ($request->includeAdvisor) {
            $items->addSelect(
                DB::raw(
                    'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
                )
            );
            $items->leftJoin(
                "teacher",
                "teacher.id",
                "=",
                "student.advisor_id"
            );
        }

        if ($request->major_id_array) {
            $items->whereIn("student.major_id", $request->major_id_array);
        }

        if ($request->id) {
            $items->where("student.id", $request->id);
        }

        if ($request->status_id) {
            if ($request->status_id == 11) {
                $items->where("student.status_id", ">=", $request->status_id);
            } else {
                $items->where("student.status_id", $request->status_id);
            }
        }

        if ($request->student_code) {
            $items->where("student.student_code", $request->student_code);
        }

        if ($request->prefix_id) {
            $items->where("student.prefix_id", $request->prefix_id);
        }

        if ($request->firstname) {
            $items->where(
                "student.firstname",
                "LIKE",
                "%" . $request->firstname . "%"
            );
        }

        if ($request->surname) {
            $items->where(
                "student.surname",
                "LIKE",
                "%" . $request->surname . "%"
            );
        }

        if ($request->email) {
            $items->where("student.email", "LIKE", "%" . $request->email . "%");
        }

        if ($request->citizen_id) {
            $items->where(
                "student.citizen_id",
                "LIKE",
                "%" . $request->citizen_id . "%"
            );
        }

        if ($request->province_id) {
            $items->where("student.province_id", $request->province_id);
        }

        if ($request->amphur_id) {
            $items->where("student.amphur_id", $request->amphur_id);
        }

        if ($request->tumbol_id) {
            $items->where("student.tumbol_id", $request->tumbolid);
        }

        if ($request->faculty_id) {
            $items->where("student.faculty_id", $request->faculty_id);
        }

        if ($request->department_id) {
            $items->where("student.department_id", $request->department_id);
        }

        if ($request->major_id) {
            $items->where("student.major_id", $request->major_id);
        }

        if ($request->class_year) {
            $items->where("student.class_year", $request->class_year);
        }

        if ($request->class_room) {
            $items->where("student.class_room", $request->class_room);
        }

        if ($request->advisor_id) {
            $items->where("student.advisor_id", $request->advisor_id);
        }

        if ($request->active) {
            $items->where("student.active", $request->active);
        }

        if ($request->orderBy) {
            $items = $items->orderBy($request->orderBy, $request->order);
        } else {
            $items = $items->orderBy("id", "asc");
        }

        $count = $items->count();
        $perPage = $request->perPage ? $request->perPage : $count;
        $currentPage = $request->currentPage ? $request->currentPage : 1;

        $totalPage = ceil($count / $perPage) == 0 ? 1 : ceil($count / $perPage);
        $offset = $perPage * ($currentPage - 1);
        $items = $items->skip($offset)->take($perPage);
        $items = $items->get();

        return response()->json(
            [
                "message" => "success",
                "data" => $items,
                "totalPage" => $totalPage,
                "totalData" => $count,
            ],
            200
        );
    }

    public function get($id)
    {
        if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
            $this->uploadUrl = "http://localhost:8117/storage/";
        }
        $item = Student::select(
            "student.id as id",
            "student.student_code as student_code",
            "student.prefix_id as prefix_id",
            "student.firstname as firstname",
            "student.surname as surname",
            "student.citizen_id as citizen_id",
            "student.address as address",
            "student.province_id as province_id",
            "student.amphur_id as amphur_id",
            "student.tumbol_id as tumbol_id",
            "student.tel as tel",
            "student.email as email",
            "student.faculty_id as faculty_id",
            "student.department_id as department_id",
            "student.major_id as major_id",
            "student.class_year as class_year",
            "student.class_room as class_room",
            "student.advisor_id as advisor_id",
            "student.gpa as gpa",
            "student.contact1_name as contact1_name",
            "student.contact1_relation as contact1_relation",
            "student.contact1_tel as contact1_tel",
            "student.contact2_name as contact2_name",
            "student.contact2_relation as contact2_relation",
            "student.contact2_tel as contact2_tel",
            "student.blood_group as blood_group",
            "student.congenital_disease as congenital_disease",
            "student.drug_allergy as drug_allergy",
            "student.emergency_tel as emergency_tel",
            "student.height as height",
            "student.weight as weight",
            "student.active as active",
            "student.status_id as status_id",
            "student.photo_file as photo_file",
            "province.name_th as province_name",
            "amphur.name_th as amphur_name",
            "tumbol.name_th as tumbol_name",
            "faculty.name_th as faculty_name",
            "department.name_th as department_name",
            "major.name_th as major_name",
            DB::raw(
                "(CASE WHEN photo_file = NULL THEN ''
             ELSE CONCAT('" .
                    $this->uploadUrl .
                    "',photo_file) END) AS photo_file"
            ),
            DB::raw(
                'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
            )
        )
            ->leftJoin(
                "province",
                "province.province_id",
                "=",
                "student.province_id"
            )
            ->leftJoin("amphur", "amphur.amphur_id", "=", "student.amphur_id")
            ->leftJoin("tumbol", "tumbol.tumbol_id", "=", "student.tumbol_id")
            ->leftJoin("faculty", "faculty.id", "=", "student.faculty_id")
            ->leftJoin(
                "department",
                "department.id",
                "=",
                "student.department_id"
            )
            ->leftJoin("major", "major.id", "=", "student.major_id")
            ->leftJoin("teacher", "teacher.id", "=", "student.advisor_id")
            ->where("student.id", $id)
            ->first();

        return response()->json(
            [
                "message" => "success",
                "data" => $item,
            ],
            200
        );
    }

    public function add(Request $request)
    {
        $pathPhoto = null;
        if (
            $request->photo_file != "" &&
            $request->photo_file != "null" &&
            $request->photo_file != "undefined"
        ) {
            $pathPhoto =
                date("YmdHis") .
                "_student_photo_" .
                rand(1, 10000) .
                "." .
                $request->file("photo_file")->extension();
            $pathPhoto = "/student/" . $pathPhoto;
            Storage::disk("public")->put(
                $pathPhoto,
                file_get_contents($request->photo_file)
            );
        }

        $item = new Student();
        $item->student_code = $request->student_code;
        $item->prefix_id = $request->prefix_id;
        $item->firstname = $request->firstname;
        $item->surname = $request->surname;
        $item->citizen_id = $request->citizen_id;
        $item->faculty_id = $request->faculty_id;
        $item->major_id = $request->major_id;
        $item->photo_file = $pathPhoto;
        // $item->department_id = $request->department_id;
        $item->created_by = "arnonr";
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function edit($id, Request $request)
    {
        $request->validate(["id as required"]);

        $id = $request->id;
        $item = Student::where("id", $id)->first();

        $pathPhoto = null;
        if (
            $request->photo_file != "" &&
            $request->photo_file != "null" &&
            $request->photo_file != "undefined"
        ) {
            $pathPhoto =
                date("YmdHis") .
                "_student_photo_" .
                rand(1, 10000) .
                "." .
                $request->file("photo_file")->extension();
            $pathPhoto = "/student/" . $pathPhoto;
            Storage::disk("public")->put(
                $pathPhoto,
                file_get_contents($request->photo_file)
            );
        } else {
            $pathPhoto = $item->photo_file;
        }

        $item->student_code = $request->has("student_code")
            ? $request->student_code
            : $item->student_code;

        $item->prefix_id = $request->has("prefix_id")
            ? $request->prefix_id
            : $item->prefix_id;
        $item->firstname = $request->has("firstname")
            ? $request->firstname
            : $item->firstname;
        $item->surname = $request->has("surname")
            ? $request->surname
            : $item->surname;
        $item->citizen_id = $request->has("citizen_id")
            ? $request->citizen_id
            : $item->citizen_id;
        $item->address = $request->has("address")
            ? $request->address
            : $item->address;
        $item->province_id = $request->has("province_id")
            ? $request->province_id
            : $item->province_id;
        $item->amphur_id = $request->has("amphur_id")
            ? $request->amphur_id
            : $item->amphur_id;
        $item->tumbol_id = $request->has("tumbol_id")
            ? $request->tumbol_id
            : $item->tumbol_id;
        $item->tel = $request->has("tel") ? $request->tel : $item->tel;
        $item->email = $request->has("email") ? $request->email : $item->email;
        $item->faculty_id = $request->has("faculty_id")
            ? $request->faculty_id
            : $item->faculty_id;
        $item->department_id = $request->has("department_id")
            ? $request->department_id
            : $item->department_id;
        $item->major_id = $request->has("major_id")
            ? $request->major_id
            : $item->major_id;
        $item->class_year = $request->has("class_year")
            ? $request->class_year
            : $item->class_year;
        $item->class_room = $request->has("class_room")
            ? $request->class_room
            : $item->class_room;
        $item->advisor_id = $request->has("advisor_id")
            ? $request->advisor_id
            : $item->advisor_id;
        $item->gpa = $request->has("gpa") ? $request->gpa : $item->gpa;
        $item->contact1_name = $request->has("contact1_name")
            ? $request->contact1_name
            : $item->contact1_name;
        $item->contact1_relation = $request->has("contact1_relation")
            ? $request->contact1_relation
            : $item->contact1_relation;
        $item->contact1_tel = $request->has("contact1_tel")
            ? $request->contact1_tel
            : $item->contact1_tel;
        $item->contact2_name = $request->has("contact2_name")
            ? $request->contact2_name
            : $item->contact2_name;
        $item->contact2_relation = $request->has("contact2_relation")
            ? $request->contact2_relation
            : $item->contact2_relation;
        $item->contact2_tel = $request->has("contact2_tel")
            ? $request->contact2_tel
            : $item->contact2_tel;
        $item->blood_group = $request->has("blood_group")
            ? $request->blood_group
            : $item->blood_group;
        $item->congenital_disease = $request->has("congenital_disease")
            ? $request->congenital_disease
            : $item->congenital_disease;
        $item->drug_allergy = $request->has("drug_allergy")
            ? $request->drug_allergy
            : $item->drug_allergy;
        $item->emergency_tel = $request->has("emergency_tel")
            ? $request->emergency_tel
            : $item->emergency_tel;
        $item->height = $request->has("height")
            ? $request->height
            : $item->height;
        $item->weight = $request->has("weight")
            ? $request->weight
            : $item->weight;
        $item->active = $request->has("active")
            ? $request->active
            : $item->active;

        $item->photo_file = $pathPhoto;

        // $item->updated_by = "arnonr";
        $item->save();

        $responseData = [
            "message" => "success",
            "data" => $item,
        ];

        return response()->json($responseData, 200);
    }

    public function delete($id)
    {
        $item = Team::where("id", $id)->first();

        $item->level = null;
        $item->is_publish = 0;
        $item->deleted_at = Carbon::now();
        $item->save();

        $items = Team::where("deleted_at", null)
            ->orderBy("level", "asc")
            ->get();

        $i = 1;
        foreach ($items as $it) {
            $it->level = $i;
            $i++;
            $it->save();
        }

        $responseData = [
            "message" => "success",
        ];

        return response()->json($responseData, 200);
    }

    public function icitAccountApiStudentInfo($student_code)
    {
        $this->errorCode = self::ERROR_NONE;

        $access_token = "v_6atHl-nF8ZSoN6QQMRPakdbQQIAdQu"; // <----- API - Access Token Here

        $data = [
            "id" => $student_code,
        ];

        $api_url =
            "https://api.account.kmutnb.ac.th/api/account-api/student-info"; // <----- API URL

        $response = Http::timeout(50)
            ->withToken($access_token)
            ->post($api_url, $data);

        // return $response->json();

        if ($response == false) {
            $this->errorCode = self::ERROR_API_FAIL;
            $this->errorMessage = "HTTP Guzzle error";
        } else {
            $json_data = json_decode($response, true);

            if ($json_data["STU_CODE"] !== "undefined") {
                $this->errorCode = self::ERROR_NOT_FOUND_STUDENT_CODE;
                $this->errorMessage = "NOT FOUND STUDENT CODE";
            }

            $req = new Request();
            $req->student_code = $json_data["STU_CODE"];

            if ($json_data["STU_PRE_NAME"] == "นางสาว") {
                $req->prefix_id = "03";
            }

            if ($json_data["STU_PRE_NAME"] == "นาง") {
                $req->prefix_id = "02";
            }

            if ($json_data["STU_PRE_NAME"] == "นาย") {
                $req->prefix_id = "01";
            }

            $req->firstname = $json_data["STU_FIRST_NAME_THAI"];
            $req->surname = $json_data["STU_LAST_NAME_THAI"];
            $req->citizen_id = $json_data["ID_CARD"];
            $req->faculty_code = $json_data["FAC_CODE"];
            $req->faculty_name = $json_data["FAC_NAME_THAI"];
            $req->department_code = $json_data["DEPT_CODE"];
            $req->department_name = $json_data["DEPT_NAME_THAI"];
            $req->major_code = $json_data["DIV_CODE"];
            $req->major_name = $json_data["DIV_NAME_THAI"];

            if ($req->major_code == "140101") {
                $req->major_code = "140102";
                $req->major_name = "บริหารธุรกิจอุตสาหกรรมและโลจิสติกส์";
            }

            return $req;
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
        $message = "Student already exists";
        $item = Student::where("student_code", $student_code)->first();

        if (!$item) {
            $result = $this->icitAccountApiStudentInfo($student_code);

            $faculty = app("App\Http\Controllers\FacultyController")->import(
                $result->faculty_code,
                $result->faculty_name
            );

            $major = app("App\Http\Controllers\MajorController")->import(
                $result->major_code,
                $result->major_name
            );

            $result["faculty_id"] = $faculty->id;
            $result["major_id"] = $major->id;

            // var_dump($result);
            // print_r($result);
            $message = "Updated student";
            if (!$item) {
                $this->add($result);
                $message = "Inserted student";
            }
        }

        return response()->json(["message" => $message], 200);
    }

    // public function getList(Request $request)
    // {
    //     if (in_array($_SERVER["HTTP_HOST"], whitelist)) {
    //         $this->uploadUrl = "http://localhost:8117/storage/";
    //     }

    //     $items = Student::select(
    //         "student.id as id",
    //         "student.student_code as student_code",
    //         "student.prefix_id as prefix_id",
    //         "student.firstname as firstname",
    //         "student.surname as surname",
    //         "student.citizen_id as citizen_id",
    //         "student.address as address",
    //         "student.province_id as province_id",
    //         "student.amphur_id as amphur_id",
    //         "student.tumbol_id as tumbol_id",
    //         "student.tel as tel",
    //         "student.email as email",
    //         "student.faculty_id as faculty_id",
    //         "student.department_id as department_id",
    //         "student.major_id as major_id",
    //         "student.class_year as class_year",
    //         "student.class_room as class_room",
    //         "student.advisor_id as advisor_id",
    //         "student.gpa as gpa",
    //         "student.contact1_name as contact1_name",
    //         "student.contact1_relation as contact1_relation",
    //         "student.contact1_tel as contact1_tel",
    //         "student.contact2_name as contact2_name",
    //         "student.contact2_relation as contact2_relation",
    //         "student.contact2_tel as contact2_tel",
    //         "student.blood_group as blood_group",
    //         "student.congenital_disease as congenital_disease",
    //         "student.drug_allergy as drug_allergy",
    //         "student.emergency_tel as emergency_tel",
    //         "student.height as height",
    //         "student.weight as weight",
    //         "student.active as active",
    //         "student.status_id as status_id",
    //         DB::raw(
    //             "(CASE WHEN photo_file = NULL THEN ''
    //          ELSE CONCAT('" .
    //                 $this->uploadUrl .
    //                 "',photo_file) END) AS photo_file"
    //         )
    //     )->where("student.deleted_at", null);

    //     // Include
    //     if ($request->includeAll) {
    //         $items->addSelect(
    //             "province.name_th as province_name",
    //             "amphur.name_th as amphur_name",
    //             "tumbol.name_th as tumbol_name",
    //             "faculty.name_th as faculty_name",
    //             "department.name_th as department_name",
    //             "major.name_th as major_name",
    //             "prefix_name.prefix_name as prefix_name",
    //             DB::raw(
    //                 'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
    //             )
    //         );
    //         $items
    //             ->leftJoin(
    //                 "province",
    //                 "province.province_id",
    //                 "=",
    //                 "student.province_id"
    //             )
    //             ->leftJoin(
    //                 "amphur",
    //                 "amphur.amphur_id",
    //                 "=",
    //                 "student.amphur_id"
    //             )
    //             ->leftJoin(
    //                 "tumbol",
    //                 "tumbol.tumbol_id",
    //                 "=",
    //                 "student.tumbol_id"
    //             )
    //             ->leftJoin("faculty", "faculty.id", "=", "student.faculty_id")
    //             ->leftJoin(
    //                 "department",
    //                 "department.id",
    //                 "=",
    //                 "student.department_id"
    //             )
    //             ->leftJoin("major", "major.id", "=", "student.major_id")
    //             ->leftJoin("teacher", "teacher.id", "=", "student.advisor_id")
    //             ->leftJoin(
    //                 "prefix_name",
    //                 "prefix_name.id",
    //                 "=",
    //                 "student.prefix_id"
    //             );
    //     }

    //     if ($request->includeForm) {
    //         if ($request->semester_id) {
    //             $items->addSelect("form.id as form_id");
    //             $items->addSelect("form.semester_id as semester_id");
    //             // $items->addSelect(
    //             //     "form.confirm_response_at as confirm_response_at"
    //             // );
    //             $items->leftJoin("form", function ($join) {
    //                 $join
    //                     ->on("form.student_id", "=", "student.id")
    //                     ->where("form.active", 1);
    //             });
    //             $items->where("form.semester_id", $request->semester_id);
    //         }

    //         if ($request->includeVisit) {
    //             $items->addSelect("visit.id as visit_id");
    //             $items->addSelect("visit_status as visit_status");

    //             $items->leftJoin("visit", function ($join) {
    //                 $join
    //                     ->on("form.id", "=", "visit.form_id")
    //                     ->where("visit.active", 1);
    //             });
    //             $items->where("form.semester_id", $request->semester_id);
    //         }
    //     }

    //     if ($request->includePrefixName) {
    //         $items->addSelect("prefix_name.prefix_name as prefix_name");
    //         $items->leftJoin(
    //             "prefix_name",
    //             "prefix_name.id",
    //             "=",
    //             "student.prefix_id"
    //         );
    //     }

    //     if ($request->includeAdvisor) {
    //         $items->addSelect(
    //             DB::raw(
    //                 'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
    //             )
    //         );
    //         $items->leftJoin(
    //             "teacher",
    //             "teacher.id",
    //             "=",
    //             "student.advisor_id"
    //         );
    //     }

    //     if ($request->includeFaculty) {
    //         $items->addSelect("faculty.name_th as faculty_name");
    //         $items->leftJoin(
    //             "faculty",
    //             "faculty.id",
    //             "=",
    //             "student.faculty_id"
    //         );
    //     }

    //     if ($request->includeDepartment) {
    //         $items->addSelect("department.name_th as department_name");
    //         $items->leftJoin(
    //             "department",
    //             "department.id",
    //             "=",
    //             "student.department_id"
    //         );
    //     }

    //     if ($request->includeMajor) {
    //         $items->addSelect("major.name_th as major_name");
    //         $items->leftJoin("major", "major.id", "=", "student.major_id");
    //     }

    //     if ($request->includeProvince) {
    //         $items->addSelect("province.name_th as province_name");
    //         $items->leftJoin(
    //             "province",
    //             "province.province_id",
    //             "=",
    //             "student.province_id"
    //         );
    //     }

    //     if ($request->includeAmphur) {
    //         $items->addSelect("amphur.name_th as amphur_name");
    //         $items->leftJoin(
    //             "amphur",
    //             "amphur.amphur_id",
    //             "=",
    //             "student.amphur_id"
    //         );
    //     }

    //     if ($request->includeTumbol) {
    //         $items->addSelect("tumbol.name_th as tumbol_name");
    //         $items->leftJoin(
    //             "tumbol",
    //             "tumbol.tumbol_id",
    //             "=",
    //             "student.tumbol_id"
    //         );
    //     }

    //     if ($request->includeAdvisor) {
    //         $items->addSelect(
    //             DB::raw(
    //                 'CONCAT(teacher.prefix,teacher.firstname," ", teacher.surname) AS advisor_name'
    //             )
    //         );
    //         $items->leftJoin(
    //             "teacher",
    //             "teacher.id",
    //             "=",
    //             "student.advisor_id"
    //         );
    //     }

    //     if ($request->id) {
    //         $items->where("student.id", $request->id);
    //     }

    //     if ($request->major_id_array) {
    //         $items->whereIn("student.major_id", $request->major_id_array);
    //     }

    //     if ($request->student_code) {
    //         $items->where("student.student_code", $request->student_code);
    //     }

    //     if ($request->prefix_id) {
    //         $items->where("student.prefix_id", $request->prefix_id);
    //     }

    //     if ($request->firstname) {
    //         $items->where(
    //             "student.firstname",
    //             "LIKE",
    //             "%" . $request->firstname . "%"
    //         );
    //     }

    //     if ($request->surname) {
    //         $items->where(
    //             "student.surname",
    //             "LIKE",
    //             "%" . $request->surname . "%"
    //         );
    //     }

    //     if ($request->email) {
    //         $items->where("student.email", "LIKE", "%" . $request->email . "%");
    //     }

    //     if ($request->citizen_id) {
    //         $items->where(
    //             "student.citizen_id",
    //             "LIKE",
    //             "%" . $request->citizen_id . "%"
    //         );
    //     }

    //     if ($request->province_id) {
    //         $items->where("student.province_id", $request->province_id);
    //     }

    //     if ($request->amphur_id) {
    //         $items->where("student.amphur_id", $request->amphur_id);
    //     }

    //     if ($request->tumbol_id) {
    //         $items->where("student.tumbol_id", $request->tumbolid);
    //     }

    //     if ($request->faculty_id) {
    //         $items->where("student.faculty_id", $request->faculty_id);
    //     }

    //     if ($request->department_id) {
    //         $items->where("student.department_id", $request->department_id);
    //     }

    //     if ($request->major_id) {
    //         $items->where("student.major_id", $request->major_id);
    //     }

    //     if ($request->class_year) {
    //         $items->where("student.class_year", $request->class_year);
    //     }

    //     if ($request->class_room) {
    //         $items->where("student.class_room", $request->class_room);
    //     }

    //     if ($request->advisor_id) {
    //         $items->where("student.advisor_id", $request->advisor_id);
    //     }

    //     if ($request->status_id) {
    //         $items->where("student.status_id", $request->status_id);
    //     }

    //     if ($request->active) {
    //         $items->where("student.active", $request->active);
    //     }

    //     if ($request->orderBy) {
    //         $items = $items->orderBy($request->orderBy, $request->order);
    //     } else {
    //         $items = $items->orderBy("id", "asc");
    //     }

    //     $count = $items->count();
    //     $perPage = $request->perPage ? $request->perPage : $count;
    //     $currentPage = $request->currentPage ? $request->currentPage : 1;

    //     $totalPage = ceil($count / $perPage) == 0 ? 1 : ceil($count / $perPage);
    //     $offset = $perPage * ($currentPage - 1);
    //     $items = $items->skip($offset)->take($perPage);
    //     $items = $items->get();

    //     return response()->json(
    //         [
    //             "message" => "success",
    //             "data" => $items,
    //             "totalPage" => $totalPage,
    //             "totalData" => $count,
    //         ],
    //         200
    //     );
    // }
}
