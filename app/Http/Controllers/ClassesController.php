<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Arms;
use App\Models\Student;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ScoreSheetExport;
use App\Models\Setting;

class ClassesController extends Controller
{
    /**
     * Get the add class page
     */
    public function index(){
        $classes = Classes::with('arms')->latest()->get();
        return inertia('Classes/addClass',compact('classes'));
    }

    /**
     * create classes
     */
    public function create(Request $request){
       $request->validate([
           'classname' => 'required',
           'section' => 'required'
       ]);

       $class = new Classes;
       $class->class_name = $request->classname;
       $class->section = $request->section;
       $class->save();
       return redirect()->back();
    }

    /**
     * Get all classes
     */
    public function Classes(){
        $classes = Classes::with(['arms'=>function($query){
            $query->withCount('subjects');
        }])->withCount(['subjects', 'arms'])->paginate(20);
        return inertia('Classes/classes',compact('classes'));
    }

    /**
     * save arms
     */

    public function saveArms(Request $request)
{
    $arms = $request->arms;
    $arms_arr = [];

    foreach ($arms as $arm) {
        array_push($arms_arr, [
            'arm_name' => $arm,
            'classes_id' => $request->class_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // Detach relationships before deleting old arms
    if (!empty($request->old_arms)) {
        foreach ($request->old_arms as $armId) {
            $arm = Arms::find($armId);
            if ($arm) {
                $arm->subjects()->detach(); // Detach relationships
                $arm->delete(); // Delete the arm
            }
        }
    }

    // Insert new arms only if there are new ones
    if (!empty($arms_arr)) {
        Arms::insert($arms_arr);
    }

    return ['success' => true];
}


    /**
     * Edit Classes
     */
    public function getEditClasses(Request $request){
        $classes = Classes::with('arms')->get();
        $singleclass = Classes::find($request->id);
        return inertia('Classes/editClass',compact('classes','singleclass'));
    }

    public function editClasses(Request $request){
        $request->validate([
            'class_name' => 'required',
            'section' => 'required',
        ]);
        $class = Classes::find($request->id);
        $class->class_name = $request->class_name;
        $class->section = $request->section;
        $class->save();
        //Classes::where('id',$request->id)->update($request->all());
        return redirect('/classes');
    }

    /**
     * delete classes
     */
    public function deleteClasses(Request $request){
        Classes::find($request->id)->delete();
        return ['success'=>true];
    }

    /**
     * Get the class arm
     */
    public function getArms(Request $request){
       $arms = Classes::with('arms')->find($request->class_name);
        return response()->json(isset($arms->arms)?$arms->arms:[]);
    }

    /**
     * Get the class student's page
     */
    public function getStudentsClass(Request $request){
        if($request->arm){
            $students = Student::with('studentGrade')->where('grade',$request->class)->where('arm',$request->arm)->paginate(25)->withQueryString();
        }else{
            $students = Student::with('studentGrade')->where('grade',$request->class)->paginate(25)->withQueryString();
        }
        $page= 0;
      
        if(request()->page > 1){
            $page = $students->firstItem()-1;
        }
        $grade=$request->class;
        $arm = $request->arm? $request->arm: "";
        return inertia('Classes/students',compact('students','grade','page','arm'));
    }

    public function searchClassesStudents(Request $request){
        $students = Student::with('studentGrade')->where(function($query)use($request){
            $query->where('class_id', $request->class_id)
                ->orWhere('surname','LIKE', '%'.$request->search.'%')
                ->orWhere('othernames','LIKE', '%'.$request->search.'%')
                ->orWhere('fullname','LIKE', '%'.$request->search.'%')
                ->orWhere('dob','LIKE', '%'.$request->search.'%')
                ->orWhere('sex', $request->search)
                ->orWhere('student_id','LIKE', '%'.$request->search.'%');
        })->paginate(20);

        return response()->json($students);
    }

    public function getSettings(){
        $settings = Setting::first();
        return $settings;
    }

    public function getScoreSheet(Request $request){
       $exam_section = match($request->section) {
            'primary' => 'primaryExam',
            'junior secondary' => 'secondaryExam',
            default => 'seniorSecondaryExam',
        };
         $settings = $this->getSettings();

        if($request->arm_id){
            $class = Arms::with('subjects')->find($request->arm_id);
            $students = Student::with([$exam_section => function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }])->where('class_id', $class->classes_id)->where('arm', $class->arm_name)->get();
            $class_name = $class->arm_name;
        }else{
            $class = Classes::with('subjects')->find($request->id);
            $students = Student::with([$exam_section => function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }])->where('class_id', $class->id)->where('arm', null)->get();
            $class_name = $class->class_name;
        }

        if($request->section == 'primary'){
            $max_ca = 20;
            $max_exam = 60;
        }else if($request->section == 'junior secondary'){
            $max_ca = 30;
            $max_exam = 40;
        }else if($request->section == 'senior secondary'){
            $max_ca = 15;
            $max_exam = 70;
        }
      
         //return Excel::download(new ScoreSheetExport($class, $students), 'score_sheet.xlsx');
         if($class->subjects && count($class->subjects) > 0){
            return (new ScoreSheetExport($class, $students, $max_ca, $max_exam))->download($class_name.'.xlsx');
         }else{
           // return redirect()->back();
         }
         
    }

    public function searchClasses(Request $request){
        $classes = Classes::with('Arms')->where(function($query)use($request){
            $query->where('class_name', 'LIKE', '%'.$request->search.'%')
                ->orWhere('section', 'LIKE', '%'.$request->search.'%');
        })->orWhereHas('Arms', function($query)use ($request){
            $query->where('arm_name', 'LIKE', '%'.$request->search.'%');
        })->paginate(20);

        return response()->json($classes);
    }

    public function getClassesWithArms(){
        $classes = Classes::with('Arms')->get();
        return response()->json($classes);
    }
    
}
