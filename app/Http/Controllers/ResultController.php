<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\HolidayAssessment;
use App\Models\PrimaryExam;
use App\Models\Classes;
use App\Models\Arms;
use App\Models\Setting;
use App\Models\PreNurseryExam;
use App\Models\SeniorSecondaryExam;
use Illuminate\Support\Facades\DB;
use App\Models\secondaryExam;
use App\Models\Subjects;

class ResultController extends Controller
{
    public function getSettings(){
        $settings = Setting::first();
        return $settings;
    }

    public function index(Request $request){
        $section = $request->section;
        if($request->section=="pre nursery"){
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

        if($request->section == 'junior secondary'){
            if($request->arm){
                $students = Student::with('subjects')->where('grade',$request->grade)->where('arm',$request->arm)->get();
            }else{
                $students = Student::with('subjects')->where('grade',$request->grade)->get();
            }
            return inertia('Students/juniorSecondaryMidterm', compact('students', 'section'));
        }

        if($request->section == 'senior secondary'){
            if($request->arm){
                $students = Student::with('subjects')->where('grade',$request->grade)->where('arm',$request->arm)->get();
            }else{
                $students = Student::with('subjects')->where('grade',$request->grade)->get();
            }
            return inertia('Students/seniorSecondaryMidterm', compact('students', 'section'));
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
        $settings  = $this->getSettings();
        if(!$settings){
            return redirect()->back();
        }
        if($request->section == "primary" || $request->section =="nursery"){
            $student = Student::with(['picture', 'remarks'=>function($query)use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'holidayAssessment'=>function($query)use ($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            },'primaryExam'=>function($query)use($settings){
                $query->where('session', $settings->session)->where('term',$settings->term);
            }])->where('id', $request->student_id)->first();
           
            //$exams = PrimaryExam::where('grade',$student->grade)->where('session', '2021/2022')->where('term','second term')->where('arm', $student->arm)->get();
        
            //$total = $exams->sum('first_ca');
                $student_total = $student->primaryExam->sum('first_ca');
                $student_count = $student->primaryExam->count();
                    //$count = $student->primaryExam->count();
                    $students = Student::with('primaryExam')->where('grade', $student->grade)->where('arm', $student->arm)->get();
                    $total = $students->sum(function ($student) {
                        return $student->primaryExam->sum('first_ca');
                    });
                    
                    $count = $students->sum(function ($student) {
                        return $student->primaryExam->count();
                    });
            $class_avg = $count? ($total/$count): 0;
            $student['class_avg'] = number_format($class_avg, 2);
            $student['percentage'] = $student_count? ($student_total/$student_count): 0;
            $settings = $this->getSettings();
            $student['settings'] = $settings;
            return response()->json($student);
        }

        if($request->section == "pre nursery"){
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'picture', 'prenurseryexam'=>function($query) use($settings){
                $query->where('session', $settings->session)->where('term', $settings->term);
            }])->where('id',$request->student_id)->first();

            $prenurseryexam = collect($student->prenurseryexam)->groupBy('category');
            return response()->json(['student'=>$student, 'prenurseryexam'=>$prenurseryexam, 'settings'=>$settings]);
        }

        if($request->section == 'junior secondary'){
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            },'picture', 'secondaryExam'=>function($query)use ($settings){
                $query->where('session', $settings->session)->where('term',$settings->term);
            }])->where('id', $request->student_id)->first();
       
            $total = $student->secondaryExam->sum('first_ca');
            $count = $student->secondaryExam->count();
          
            $percentage = $count? ($total/$count): 0;
           
            $student['class_avg'] = $percentage;
            $settings = $this->getSettings();
            $student['settings'] = $settings;
            return response()->json($student);
        }

        if($request->section == 'senior secondary'){
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            },'picture', 'seniorSecondaryExam'=>function($query) use($settings){
                $query->where('session', $settings->session)->where('term',$settings->term);
            }])->where('id', $request->student_id)->first();
        
            $total = $student->seniorSecondaryExam->sum('first_ca');
            $count = $student->seniorSecondaryExam->count();
            $percentage = $count? ($total/$count): 0;
            $student['class_avg'] = $percentage;
            $settings = $this->getSettings();
            $student['settings'] = $settings;
            return response()->json($student);
        }
      }

    public function getResultPage(Request $request){
        $section = $request->section; 
        $student_id = $request->studentid?? $request->studentid;

        if($request->section == 'pre nursery'){
            $students = Student::where('grade', $request->grade)
            ->when($request->arm, function ($query) use ($request) {
                $query->where('arm', $request->arm);
            })
            ->get();
            $studentid = $student_id;
            return inertia('Students/preNurseryResult', compact('section', 'students', 'studentid'));
        } 
        if($request->section == 'nursery'){
            $students = Student::where('grade', $request->grade)
            ->when($request->arm, function ($query) use ($request) {
                $query->where('arm', $request->arm);
            })
            ->get();
            return inertia('Students/nurseryResult', compact('section', 'students', 'student_id'));
        }
        if($request->section == 'primary'){
            $students = Student::where('grade', $request->grade)
            ->when($request->arm, function ($query) use ($request) {
                $query->where('arm', $request->arm);
            })
            ->get();
            return inertia('Students/primaryResult', compact('section', 'students', 'student_id'));
        }
        if($request->section == 'junior secondary'){
            $students = Student::where('grade', $request->grade)
            ->when($request->arm, function ($query) use ($request) {
                $query->where('arm', $request->arm);
            })
            ->get();
            return inertia('Students/juniorSecondaryResult', compact('section', 'students', 'student_id'));
        }
        if($request->section == 'senior secondary'){
            $students = Student::where('grade', $request->grade)
            ->when($request->arm, function ($query) use ($request) {
                $query->where('arm', $request->arm);
            })
            ->get();
            return inertia('Students/seniorSecondaryResult', compact('section', 'students', 'student_id'));
        }
    }

    public function getResult(Request $request){
        $settings = $this->getSettings();
        if(!$settings){
            return redirect()->back();
        }

        if($request->section == 'pre nursery'){
            $student = Student::with(['picture','remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'behaviour'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'physicaldevelopment'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'attendance'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'preNurseryAffective'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'prenurseryexam'=>function($query)use ($settings){
                $query->where('session', $settings->session)->where('term', $settings->term);
            }])->where('id',$request->student_id)->first();

            $prenurseryexam = collect($student->prenurseryexam)->groupBy('category');
            return response()->json(['student'=>$student, 'prenurseryexam'=>$prenurseryexam, 'settings'=>$settings,]);
        }

        if($request->section == 'nursery'){
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'behaviour'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'physicaldevelopment'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'attendance'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'primaryAffective'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'picture', 'primaryExam'=>function($query)use ($settings){
                $query->where('session', $settings->session)->where('term', $settings->term);
            }])->withCount('subjects')->where('id',$request->student_id)->first();

            $total_subjects = $student->subjects_count; 
            $total_marks_obtainable = 100 * $total_subjects;
            $total_marks_obtained = $student->primaryExam->sum('total');
            $overall_percentage = $total_marks_obtained? ($total_marks_obtained/ $total_marks_obtainable) * 100: 0;

            $total_class_sum = Student::whereHas('primaryExam', function ($query) use ($settings, $student) {
                $query->where('session', $settings->session)
                      ->where('term', $settings->term)
                      ->where('grade', $student->grade);
            })->withSum(['primaryExam as total' => function ($query) use ($settings, $student) {
                $query->where('session', $settings->session)
                      ->where('term', $settings->term)
                      ->where('grade', $student->grade);
            }],'total')->get()->sum('total');
     
            $total_student = Student::whereHas('primaryExam', function($query) use ($settings, $student){
                $query->where('session', $settings->session)
                    ->where('term', $settings->term)
                    ->where('grade', $student->grade);
            })->count();

            if ($total_class_sum > 0 && ($total_student * $total_subjects) > 0) {
                $class_average = $total_class_sum / ($total_student * $total_subjects);
            } else {
                $class_average = 0;
            }
            

            return response()->json([
                'student'=> $student, 
                'settings'=>$settings,
                'total_marks_obtainable' => $total_marks_obtainable,
                'total_marks_obtained' => $total_marks_obtained,
                'overall_percentage' => $overall_percentage,
                'class_average' => $class_average
            ]);
        }

        if($request->section == 'primary'){
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'physicaldevelopment'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'attendance'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'primaryAffective'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'picture', 'primaryExam'=>function($query)use ($settings){
                $query->where('session', $settings->session)->where('term', $settings->term);
            }])->withCount('subjects')->where('id',$request->student_id)->first();

            $total_subjects = $student->subjects_count; 
            $total_marks_obtainable = 100 * $total_subjects;
            $total_marks_obtained = $student->primaryExam->sum('total');
            $overall_percentage = ($total_marks_obtained/ $total_marks_obtainable) * 100;

            $total_class_sum = Student::whereHas('primaryExam', function ($query) use ($settings, $student) {
                $query->where('session', $settings->session)
                      ->where('term', $settings->term)
                      ->where('grade', $student->grade);
            })->withSum(['primaryExam as total' => function ($query) use ($settings, $student) {
                $query->where('session', $settings->session)
                      ->where('term', $settings->term)
                      ->where('grade', $student->grade);
            }],'total')->get()->sum('total');
     
            $total_student = Student::whereHas('primaryExam', function($query) use ($settings, $student){
                $query->where('session', $settings->session)
                    ->where('term', $settings->term)
                    ->where('grade', $student->grade);
            })->count();

            $class_average = $total_student? ($total_class_sum/ ($total_student * $total_subjects))  : 0;
            return response()->json([
                'student'=> $student, 
                'settings'=>$settings, 
                'total_marks_obtainable' => $total_marks_obtainable,
                'total_marks_obtained' => $total_marks_obtained,
                'overall_percentage' => $overall_percentage,
                'class_average' => $class_average
            ]);
        }

        if($request->section == 'junior secondary'){
           
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'physicaldevelopment'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'attendance'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'secondaryAffective'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'picture'])->where('id',$request->student_id)->first();

            $allExams = secondaryExam::where(function($query) use ($settings, $student){
                $query->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->where('grade', $student->grade)
                    ->where('arm', $student->arm);
            })
            //->orderBy('total', 'desc')
            ->get();

            $class_average = count($allExams) > 0?
                    ($allExams->sum('total')/ count($allExams)): 0;

            $allExams = $allExams->groupBy('subject')->map(function ($subjectExams) {
                $average = $subjectExams->avg('total');
                
                return $subjectExams->sortByDesc('total')
                    ->groupBy('total')
                    ->values()
                    ->map(function ($group, $groupIndex) use ($average) {
                        $rank = $groupIndex + 1;
                        return $group->each(function ($exam) use ($rank, $average) {
                            $exam->rank = $rank;
                            $exam->subject_average = round($average, 2);
                        });
                    })
                    ->flatten();
            })->flatten();

            $exams = $allExams->where('student_id', $student->id)->values();
            $student_average = count($exams) > 0? 
                    ($exams->sum('total')/count($exams)): 0;
            
           

            return response()->json([
                'student'=> $student, 
                'settings'=>$settings,
                'exams'  => $exams,
                'class_average' => $class_average,
                'student_average' => $student_average
            ]);
        }

        if($request->section == 'senior secondary'){
           
            $student = Student::with(['remarks'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'physicaldevelopment'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'attendance'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'secondaryAffective'=>function($query) use($settings){
                $query->where('term', $settings->term)->where('session', $settings->session);
            }, 'picture'])->where('id',$request->student_id)->first();

            $allExams = SeniorSecondaryExam::where(function($query) use ($settings, $student){
                $query->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->where('grade', $student->grade);
                    //->where('arm', $student->arm);
            })
            //->orderBy('total', 'desc')
            ->get();

            $class_average = count($allExams) > 0?
                    ($allExams->sum('total')/ count($allExams)): 0;

            $allExams = $allExams->groupBy('subject')->map(function ($subjectExams) {
                $average = $subjectExams->avg('total');
                
                return $subjectExams->sortByDesc('total')
                    ->groupBy('total')
                    ->values()
                    ->map(function ($group, $groupIndex) use ($average) {
                        $rank = $groupIndex + 1;
                        return $group->each(function ($exam) use ($rank, $average) {
                            $exam->rank = $rank;
                            $exam->subject_average = round($average, 2);
                        });
                    })
                    ->flatten();
            })->flatten();

            $exams = $allExams->where('student_id', $student->id)->values();
            $student_average = count($exams) > 0? 
                    ($exams->sum('total')/count($exams)): 0;
            
           

            return response()->json([
                'student'=> $student, 
                'settings'=>$settings,
                'exams'  => $exams,
                'class_average' => $class_average,
                'student_average' => $student_average
            ]);
        }
    }

    public function deletePrenurseryExamSubject(Request $request){
        $exam = PreNurseryExam::find($request->id);
        if($exam){
            $exam->delete();
        }
        return redirect()->back();
    }

    public function deleteSeniorSecondaryExamSubject(Request $request){
        $exam = SeniorSecondaryExam::find($request->id);
        $student = Student::find($exam->student_id);
        $subject = Subjects::where('subject', $exam->subject)->where('section','senior secondary')->first();
        $student_subject = DB::table('student_subjects')->where('student_id', $student->id)->where('subjects_id', $subject->id)->delete();
        
        if($exam){
            $exam->delete();
        }
        return response()->json(['message'=>true]);
    }
}
