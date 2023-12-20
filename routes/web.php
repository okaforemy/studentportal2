<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\ParentsController;
use App\Http\Controllers\ClassesController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\CBTController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/',[HomeController::class, 'home'])->name('home');
    Route::get('/add-user', [HomeController::class, 'register'])->name('register');
    Route::post('/add-user', [HomeController::class, 'addUser'])->name('add-user');

    //Students controller
    Route::get('/add-students',[StudentController::class, 'index'])->name('get-addstudents');
    Route::post('/add-students',[StudentController::class, 'addStudent'])->name('addstudents');
    Route::get('/recently-added-students',[StudentController::class, 'getRecentlyAddedStudent']);
    Route::get('/students',[StudentController::class, 'Students'])->name('students');
    Route::get('/search-students',[StudentController::class, 'searchForChildren']);
    Route::get('/delete-student',[StudentController::class, 'deleteStudent']);
    Route::get('/students-exam-scores',[StudentController::class, 'addStudentScores']);
    Route::post('/students-exam-scores', [StudentController::class, 'saveScores']);
    Route::get('/affective-disposition', [StudentController::class, 'affectiveDisposition']);
    Route::post('/affective-disposition', [StudentController::class, 'saveAffective']);
    Route::get('/attendance',[StudentController::class, 'getAttendance']);
    Route::post('/attendance',[StudentController::class, 'attendance']);
    Route::get('/physical-development', [StudentController::class, 'getPhysicalDevelopment']);
    Route::post('/physical-development', [StudentController::class, 'physicalDevelopment']);
    Route::get('/remarks', [StudentController::class, 'getStudentsRemarks']);
    Route::post('/remarks', [StudentController::class, 'saveStudentsRemarks']);

    //Parents controller
    Route::get('/add-parents',[ParentsController::class, 'index']);
    Route::post('/add-parents',[ParentsController::class, 'store']);
    Route::get('/search-for-parent',[ParentsController::class, 'searchParent']);
    Route::get('/edit-parent',[ParentsController::class,'getEditParent']);
    Route::post('/edit-parent',[ParentsController::class,'editParent']);
    Route::get('/add-parents-only', [ParentsController::class, 'getAddParentsOnly']);
    Route::post('/add-parents-only',[ParentsController::class, 'addParentsOnly']);
    Route::get('/parents',[ParentsController::class, 'Parents']);
    Route::post('/assign-children',[ParentsController::class, 'assignChildrenToParents']);
    Route::get('/delete-parent',[ParentsController::class, 'deleteParent']);
    Route::get('/edit-parent-only',[ParentsController::class,'getEditOnlyParent']);
    Route::post('/edit-parent-only',[ParentsController::class, 'editOnlyParent']);

    //Classes controller
    Route::get('/add-class',[ClassesController::class,'index']);
    Route::post('/add-class', [ClassesController::class, 'create']);
    Route::get('/classes',[ClassesController::class, 'Classes'])->name('classes');
    Route::post('/save-arms',[ClassesController::class, 'saveArms']);
    Route::get('/edit-class',[ClassesController::class, 'getEditClasses']);
    Route::post('/edit-class',[ClassesController::class, 'editClasses']);
    Route::get('/delete-class', [ClassesController::class, 'deleteClasses']);
    Route::get('/get-arms', [ClassesController::class, 'getArms']);
    Route::get('/class-list',[ClassesController::class, 'getStudentsClass']);

    //subjects routes
    Route::get('/add-subjects',[SubjectController::class, 'index'])->name('addsubjects');
    Route::post('/add-subjects',[SubjectController::class, 'AddSubject']);
    Route::get('/assign-subjects',[SubjectController::class, 'getAssignSubject'])->name('assignsubjects');
    Route::post('/assign-subjects',[SubjectController::class, 'assignSubjects']);
    Route::get('/delete-subject', [SubjectController::class, 'deleteSubject']);

    //manages students result
    Route::get('/mid-term-result', [ResultController::class, 'index']);
    Route::get('/get-midterm-result', [ResultController::class, 'getMidTermResult']);

    //holiday assessment
    Route::get('/holiday-assessment', [ResultController::class, 'holidayAssessment']);
    Route::post('/holiday-assessment', [ResultController::class, 'saveHolidayAssessment']);

    //CBT
    Route::get('/add-question', [CBTController::class, 'addQuestion'])->name('add-question');
    Route::post('/add-question', [CBTController::class, 'saveQuestions']);
    Route::get('/cbt-question-subjects', [CBTController::class, 'questionSubjects']);
    Route::get('/add-cbt-student', [CBTController::class, 'addStudent']);
    Route::post('/add-cbt-student', [CBTController::class, 'saveStudent'])->name('save-cbt-students');
    Route::get('/delete-cbt-student', [CBTController::class, 'deleteCBTStudent']);
    Route::get('/cbt-students', [CBTController::class, 'CBTUsers'])->name('cbt-students');
    Route::get('/view-questions', [CBTController::class, 'viewQuestions'])->name('view-cbt-questions');
    Route::get('/get-questions', [CBTController::class, 'getQuestions']);
    Route::get('/delete-question', [CBTController::class, 'deleteQuestion']);
    Route::get('/cbt-settings', [CBTController::class, 'settings']);
    Route::post('/cbt-settings', [CBTController::class, 'saveSettings']);
    Route::get('/start-exam-setting', [CBTController::class,'startExam']);
    Route::get('/cbt-results', [CBTController::class, 'CBTResults']);
    Route::get('/results', [CBTController::class, 'getResults']);
    Route::post('/results', [CBTController::class, 'StudentResult']);
    Route::get('/students-in-a-class', [CBTController::class, 'getStudentsInAClass']);
    Route::get('/student-results-by-subjects', [CBTController::class, 'CBTResultsBySubjects']);

    //general
    Route::get('/settings', [HomeController::class, 'Settings']);
    Route::post('/settings', [HomeController::class, 'saveSettings']);

});

Route::get('/login',[LoginController::class, 'login'])->name('login');
Route::post('/login', [LoginController::class, 'authenticate'])->name('login-user');
Route::get('/cbt-login', [CBTController::class, 'CBTLogin'])->name('cbt-login');
Route::post('/cbt-login', [CBTController::class, 'CBTLoginValidate'])->name('post-cbt-login');

Route::middleware(['auth:cbt', 'is_logged_in'])->group(function () {
    Route::get('/cbt-home', [CBTController::class, 'CBTHome'])->name('exam-home');
    Route::get('/cbt-exam', [CBTController::class, 'exam'])->name('exam');
    Route::get('/prepare-exam', [CBTController::class, 'prepareQuestion']);
    Route::post('/answer-question', [CBTController::class, 'answerQuestion']);
    Route::get('/log-out', [CBTController::class, 'logOutStudent']);
});

Route::get('/cbt-result', [CBTController::class, 'result'])->name('cbt-result');