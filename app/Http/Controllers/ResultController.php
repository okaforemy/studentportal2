<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\HolidayAssessment;
use App\Models\PrimaryExam;
use App\Models\Classes;
use App\Models\Arms;

class ResultController extends Controller
{
    public function index(Request $request){
        $section = $request->section;
        if($request->section=="pre_nursery"){
            // if($request->arm){
            //     $students = Student::with(['subjects'=>function($query){
            //         $query->select('subject','section','category')->groupBy('category');
            //     }])->where('grade',$request->grade)->where('arm',$request->arm)->get();
            // }else{
            //     $students = Student::with(['subjects'=>function($query){
            //         $query->select('subject','section','category')->groupBy('category');
            //     }])->where('grade',$request->grade)->get();
            // }
            $students = Student::where('grade', $request->grade)
            ->when($request->arm, function ($query) use ($request) {
                $query->where('arm', $request->arm);
            })
            ->get();

        //     $new_students = [];
        //     foreach($students as $student){
        //         $data = [
        //             'id'=>$student->id,
        //             'surname'=>$student->surname,
        //             'othernames' => $student->othernames,
        //             'fullname' => $student->fullname,
        //             'dob' => $student->dob,
        //             'sex' => $student->sex,
        //             'student_id' => $student->student_id,
        //             'grade' => $student->grade,
        //             'arm' => $student->arm,
        //             'subjects' => $student->subjects->groupBy('category')
        //         ];
        //         array_push($new_students, $data);
        //     }
        //    $students = $new_students;
            return inertia('Students/preNurseryMidtermResult', compact('students','section'));
        }
        
        if($request->section=="primary" || $request->section == 'nursery'){
            if($request->arm){
                $students = Student::with('subjects')->where('grade',$request->grade)->where('arm',$request->arm)->get();
            }else{
                $students = Student::with('subjects')->where('grade',$request->grade)->get();
            }
            return inertia('Students/primaryMidtermResult', compact('students','section'));
        }
    }

    public function holidayAssessment(Request $request){
        if($request->arm){
            $students = Student::with(['holidayAssessment'])->where('grade',$request->grade)->where('arm',$request->arm)->get();
            $grade = Arms::where('arm_name',$request->arm)->first();
        }else{
            $students = Student::with('holidayAssessment')->where('grade',$request->grade)->get();
            $grade = Classes::where('class_name',$request->grade)->first();
        }

        return inertia('Students/holidayassessment', compact('students','grade'));
    }
    
    public function saveHolidayAssessment(Request $request){
       // dd($request->all());
        HolidayAssessment::upsert($request->all(),['id'], ['score']);
        //return response()->json(['success'=>'saved successfully']);
        return redirect()->back();
    }

    /**
       * Get the midterm result
       */
      public function getMidTermResult(Request $request){
        if($request->section == "primary" || $request->section =="nursery"){
            $student = Student::with(['holidayAssessment','primaryExam'=>function($query){
                $query->where('session', '2021/2022')->where('term','second term');
            }])->where('id', $request->student_id)->first();
            $exams = PrimaryExam::where('grade',$student->grade)->where('session', '2021/2022')->where('term','second term')->where('arm', $student->arm)->get();
        
            $total = $exams->sum('first_ca');
            $count = $exams->count();
            $percentage = ($total/$count);
            $student['class_avg'] = $percentage;
            return response()->json($student);
        }

        if($request->section == "pre_nursery"){
            $student = Student::with(['prenurseryexam'=>function($query){
                $query->where('session', '2020/2021')->where('term', 'second term');
            }])->where('id',$request->student_id)->first();

            dd($student);
        }
      }
}
