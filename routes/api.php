<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
//
use App\Http\Controllers\ProvinceController;
use App\Http\Controllers\AmphurController;
use App\Http\Controllers\TumbolController;
//
use App\Http\Controllers\FacultyController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\MajorController;
//
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\SemesterController;
//
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\DocumentTypeController;
use App\Http\Controllers\DocumentDownloadTypeController;
use App\Http\Controllers\MajorHeadController;
//
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormStatusController;
use App\Http\Controllers\RejectLogController;
use App\Http\Controllers\VisitRejectLogController;
use App\Http\Controllers\StudentDocumentController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitImageController;
//
use App\Http\Controllers\NewsController;
use App\Http\Controllers\NewsCategoryController;
use App\Http\Controllers\PrefixNameController;
//
use App\Http\Controllers\FroalaController;
use App\Http\Controllers\MailController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group(["prefix" => "auth"], function () {
    Route::post("login", [AuthController::class, "login"]);
    Route::post("register", [AuthController::class, "register"]);

    Route::group(["middleware" => "auth:api"], function () {
        Route::get("logout", [AuthController::class, "logout"]);
        Route::get("user", [AuthController::class, "user"]);
    });
});

Route::group(["prefix" => "user"], function () {
    Route::get("/get-icit-account/{id}", [
        UserController::class,
        "getIcitAccount",
    ]);
    Route::post("/import-icit-account/{id}", [
        UserController::class,
        "getImportIcitAccount",
    ]);
    Route::get("/{id}", [UserController::class, "get"]);
    Route::get("/", [UserController::class, "getAll"]);
    Route::post("/", [UserController::class, "add"]);
    Route::put("/{id}", [UserController::class, "edit"]);
    Route::delete("/{id}", [UserController::class, "delete"]);
});

Route::group(["prefix" => "province"], function () {
    Route::get("/{id}", [ProvinceController::class, "get"]);
    Route::get("/", [ProvinceController::class, "getAll"]);
    Route::post("/", [ProvinceController::class, "add"]);
    Route::put("/{id}", [ProvinceController::class, "edit"]);
    Route::delete("/{id}", [ProvinceController::class, "delete"]);
});

Route::group(["prefix" => "prefix-name"], function () {
    Route::get("/{id}", [PrefixNameController::class, "get"]);
    Route::get("/", [PrefixNameController::class, "getAll"]);
    // Route::post("/", [PrefixNameController::class, "add"]);
    Route::put("/{id}", [PrefixNameController::class, "edit"]);
    // Route::delete("/{id}", [PrefixNameController::class, "delete"]);
});

Route::group(["prefix" => "amphur"], function () {
    Route::get("/{id}", [AmphurController::class, "get"]);
    Route::get("/", [AmphurController::class, "getAll"]);
});

Route::group(["prefix" => "tumbol"], function () {
    Route::get("/{id}", [TumbolController::class, "get"]);
    Route::get("/", [TumbolController::class, "getAll"]);
});

Route::group(["prefix" => "teacher"], function () {
    Route::get("/hris-personnel-info", [
        TeacherController::class,
        "hrisPersonnelInfo",
    ]);
    Route::get("/hris-find-personnel", [
        TeacherController::class,
        "hrisFindPersonnel",
    ]);
    Route::get("/hris-import-personnel", [
        TeacherController::class,
        "hrisImportPersonnel",
    ]);
    Route::get("/hris-sync-all-teacher", [
        TeacherController::class,
        "hrisSyncAllTeacher",
    ]);
    Route::get("/{id}", [TeacherController::class, "get"]);
    Route::get("/", [TeacherController::class, "getAll"]);
    Route::post("/", [TeacherController::class, "add"]);
    Route::put("/{id}", [TeacherController::class, "edit"]);
    Route::delete("/{id}", [TeacherController::class, "delete"]);
});

Route::group(["prefix" => "faculty"], function () {
    Route::get("/{id}", [FacultyController::class, "get"]);
    Route::get("/", [FacultyController::class, "getAll"]);
    Route::put("/{id}", [FacultyController::class, "edit"]);
});

Route::group(["prefix" => "department"], function () {
    Route::get("/{id}", [DepartmentController::class, "get"]);
    Route::get("/", [DepartmentController::class, "getAll"]);
    Route::put("/{id}", [DepartmentController::class, "edit"]);
});

Route::group(["prefix" => "major"], function () {
    Route::get("/{id}", [MajorController::class, "get"]);
    Route::get("/", [MajorController::class, "getAll"]);
    Route::put("/{id}", [MajorController::class, "edit"]);
});

Route::group(["prefix" => "major-head"], function () {
    Route::get("/{id}", [MajorHeadController::class, "get"]);
    Route::get("/", [MajorHeadController::class, "getAll"]);
    Route::post("/", [MajorHeadController::class, "add"]);
    Route::put("/{id}", [MajorHeadController::class, "edit"]);
    Route::delete("/{id}", [MajorHeadController::class, "delete"]);
});

Route::group(["prefix" => "company"], function () {
    Route::get("/{id}", [CompanyController::class, "get"]);
    Route::get("/", [CompanyController::class, "getAll"]);
    Route::post("/", [CompanyController::class, "add"]);
    Route::put("/{id}", [CompanyController::class, "edit"]);
    Route::delete("/{id}", [CompanyController::class, "delete"]);
});

Route::group(["prefix" => "semester"], function () {
    Route::get("/{id}", [SemesterController::class, "get"]);
    Route::get("/", [SemesterController::class, "getAll"]);
    Route::post("/", [SemesterController::class, "add"]);
    Route::put("/{id}", [SemesterController::class, "edit"]);
    Route::delete("/{id}", [SemesterController::class, "delete"]);
});

Route::group(["prefix" => "student"], function () {
    Route::post("/import/{id}", [StudentController::class, "import"]);
    //   Route::get('/list', [StudentController::class, 'getList']);
    Route::get("/{id}", [StudentController::class, "get"]);
    Route::get("/", [StudentController::class, "getAll"]);
    Route::post("/", [StudentController::class, "add"]);
    Route::put("/{id}", [StudentController::class, "edit"]);
    Route::delete("/{id}", [StudentController::class, "delete"]);
});

Route::group(["prefix" => "document-type"], function () {
    Route::get("/{id}", [DocumentTypeController::class, "get"]);
    Route::get("/", [DocumentTypeController::class, "getAll"]);
    Route::post("/", [DocumentTypeController::class, "add"]);
    Route::put("/{id}", [DocumentTypeController::class, "edit"]);
    Route::delete("/{id}", [DocumentTypeController::class, "delete"]);
});

Route::group(["prefix" => "news-category"], function () {
    Route::get("/{id}", [NewsCategoryController::class, "get"]);
    Route::get("/", [NewsCategoryController::class, "getAll"]);
    Route::post("/", [NewsCategoryController::class, "add"]);
    Route::put("/{id}", [NewsCategoryController::class, "edit"]);
    Route::delete("/{id}", [NewsCategoryController::class, "delete"]);
});

Route::group(["prefix" => "news"], function () {
    Route::get("/{id}", [NewsController::class, "get"]);
    Route::get("/", [NewsController::class, "getAll"]);
    Route::post("/", [NewsController::class, "add"]);
    Route::put("/{id}", [NewsController::class, "edit"]);
    Route::delete("/{id}", [NewsController::class, "delete"]);
});

Route::group(["prefix" => "document-download-type"], function () {
    Route::get("/{id}", [DocumentDownloadTypeController::class, "get"]);
    Route::get("/", [DocumentDownloadTypeController::class, "getAll"]);
    Route::post("/", [DocumentDownloadTypeController::class, "add"]);
    Route::put("/{id}", [DocumentDownloadTypeController::class, "edit"]);
    Route::delete("/{id}", [DocumentDownloadTypeController::class, "delete"]);
});

Route::group(["prefix" => "student-document"], function () {
    Route::get("/{id}", [StudentDocumentController::class, "get"]);
    Route::get("/", [StudentDocumentController::class, "getAll"]);
    Route::post("/", [StudentDocumentController::class, "add"]);
    Route::put("/{id}", [StudentDocumentController::class, "edit"]);
    Route::delete("/{id}", [StudentDocumentController::class, "delete"]);
});

Route::group(["prefix" => "document-download"], function () {
    Route::get("/{id}", [DocumentDownloadController::class, "get"]);
    Route::get("/", [DocumentDownloadController::class, "getAll"]);
    Route::post("/", [DocumentDownloadController::class, "add"]);
    Route::put("/{id}", [DocumentDownloadController::class, "edit"]);
    Route::delete("/{id}", [DocumentDownloadController::class, "delete"]);
});

Route::group(["prefix" => "form"], function () {
    Route::get("/{id}", [FormController::class, "get"]);
    Route::get("/", [FormController::class, "getAll"]);
    Route::post("/import-form-supervision", [
        FormController::class,
        "importFormSupervision",
    ]);
    Route::post("/", [FormController::class, "add"]);
    Route::post("/add-request-book", [FormController::class, "addRequestBook"]);
    Route::post("/add-send-book", [FormController::class, "addSendBook"]);
    Route::post("/add-response-book", [
        FormController::class,
        "AddResponseBook",
    ]);
    Route::post("/add-plan", [FormController::class, "AddPlan"]);
    Route::put("/approve/{id}", [FormController::class, "approve"]);
    Route::put("/edit-supervision/{id}", [
        FormController::class,
        "EditSupervision",
    ]);
    Route::put("/{id}", [FormController::class, "edit"]);
    Route::delete("/{id}", [FormController::class, "delete"]);
});

Route::group(["prefix" => "reject-log"], function () {
    Route::get("/{id}", [RejectLogController::class, "get"]);
    Route::get("/", [RejectLogController::class, "getAll"]);
    Route::post("/", [RejectLogController::class, "add"]);
    Route::put("/{id}", [RejectLogController::class, "edit"]);
    Route::delete("/{id}", [RejectLogController::class, "delete"]);
});

Route::group(["prefix" => "visit-reject-log"], function () {
    Route::get("/{id}", [VisitRejectLogController::class, "get"]);
    Route::get("/", [VisitRejectLogController::class, "getAll"]);
    Route::post("/", [VisitRejectLogController::class, "add"]);
    Route::put("/{id}", [VisitRejectLogController::class, "edit"]);
    Route::delete("/{id}", [VisitRejectLogController::class, "delete"]);
});

Route::group(["prefix" => "visit"], function () {
    Route::get("/update-travel-express", [
        VisitController::class,
        "updateTravelExpress",
    ]);
    Route::get("/{id}", [VisitController::class, "get"]);
    Route::get("/", [VisitController::class, "getAll"]);
    Route::put("/approve/{id}", [VisitController::class, "approve"]);
    Route::post("/add-visit-book", [VisitController::class, "addVisitBook"]);
    Route::post("/", [VisitController::class, "add"]);
    Route::put("/{id}", [VisitController::class, "edit"]);
    Route::delete("/{id}", [VisitController::class, "delete"]);
});

// Route::group(['prefix' => ''], function () {
//   Route::put('/level/{id}', [Controller::class, 'editLevel']);
//   Route::get('/{id}', [Controller::class, 'get']);
//   Route::get('/', [Controller::class, 'getAll']);
//   Route::post('/', [Controller::class, 'add']);
//   Route::put('/{id}', [Controller::class, 'edit']);
//   Route::delete('/{id}', [Controller::class, 'delete']);
// //   Route::post('/uppy', [NewsGalleryController::class, 'uppy']);
// });

Route::group(["prefix" => "froala"], function () {
    Route::post("/image", [FroalaController::class, "image"]);
    Route::post("/document", [FroalaController::class, "document"]);
    Route::post("/video", [FroalaController::class, "video"]);
});

Route::group(["prefix" => "mail"], function () {
    Route::get("/send/{receiver}", [MailController::class, "send"]);
});

Route::group(["prefix" => "config"], function () {
    Route::get("/{id}", [ConfigController::class, "get"]);
    Route::put("/{id}", [ConfigController::class, "edit"]);
});
