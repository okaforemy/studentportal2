<?php

namespace App\Http\Controllers;

use Illuminate\Support\Collection;
use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Classes;
use App\Models\PrimaryExam;
use App\Models\primaryAffective;
use App\Models\attendance;
use App\Models\Subjects;
use App\Models\physicaldevelopment;
use App\Models\secondaryExam;
use App\Models\remark;
use App\Models\PreNurseryExam;
use App\Models\SeniorSecondaryExam;
use App\Models\preNurseryAffective;
use App\Models\secondaryAffective;
use App\Models\Behaviour;
use App\Models\Setting;
use Illuminate\Validation\Rule;
//use Spatie\Image\Image;
use App\Models\StudentImage;
use Intervention\Image\ImageManager;
//use Image;
use Intervention\Image\Facades\Image;
use App\Models\Arms;

class StudentController extends Controller
{

    public function getSettings(){
        $settings = Setting::first();
        return $settings;
    }
    /**
     * Gets the students page
     */
    public function index(){
        $classes = Classes::all();
        return inertia('Students/addStudent', compact('classes'));
    }

    public function upload($request, $id, $isupdate)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        if ($request->file('image')) {
            //$pupil=new $classname;
           // $error=""; 
           $img = StudentImage::where('student_id', $id)->first();
           $img = $img? $img: new StudentImage();
            $originalImage=$request->file('image');
            $path=$originalImage->getClientOriginalName();
            $full_path='img/studentimage/'.$path;
            $student=studentimage::where('path',$full_path)->first();
            
            //$originalPath='/home2/purplins/public_html/img/studentimage/';
           
            //$originalPath = base_path($full_path);
            $originalPath = '/home/codeemy-purplins/htdocs/purplins.codeemy.com/public/'.$full_path;
     
            $thumbnail=Image::make($originalImage)->resize(160,null,function($constraint){
                $constraint->aspectRatio();
            })->save($originalPath);
           
            $img->path=$full_path;
            $img->student_id=$id;
            $img->save();
    
            return true;
        }

        return response()->json(['error' => 'File not uploaded'], 400);
    }


    /**
     * Add student's record
     */
    public function addStudent(Request $request){
        if($request->id){
            $student = Student::where('id', $request->id)->first();
            $request->validate([
                'surname' => 'required',
                'othernames' => 'required',
                'dob' => 'required',
                'sex' => 'required',
                'grade' => 'required',
                'student_id' => [
                    'required',
                    Rule::unique('students')->ignore($student->id)->whereNull('deleted_at'),
                ],
            ]);
        }else{
            $request->validate([
                'surname'=>'required',
                'othernames'=>'required',
                'dob'=>'required',
                'sex'=>'required',
                'grade' => 'required',
                'student_id'=>'required|unique:students,deleted_at,NULL'
            ]);
        }
       
       
        $class = Classes::find($request->grade);

        $request->merge(['fullname'=>$request->surname." ".$request->othernames,'grade'=>$class->class_name,'class_id'=>$request->grade]);
        if($request->id){
            $student->update($request->all());
           
        }else{
            $student =Student::create($request->all());
        }
/*
        if($request->file('image')){
            if($request->id){
                $this->upload($request,$student->id, true);
            }else{
                $this->upload($request,$student->id, false);
            }
        }
*/
        $students = $this->getRecentlyAddedStudent();
        if($request->id){
            return redirect()->route('editStudent',$request->id);
        }
        return redirect()->route('addstudents',compact('students'));
    }

    public function editStudent($id){
        $student = Student::find($id);
        $classes = Classes::all();
        return inertia('Students/editStudent', compact('student', 'classes'));
    }
    
    /**
     * fetch the recently added students
     */
    public function getRecentlyAddedStudent(){
        $students = Student::latest()->get()->take(7);
        return $students;
    }

    /**
     * fetch all students
     */
    public function Students(){
        $students = Student::with('studentGrade')->orderBy('fullname', 'ASC')->paginate(20);
        return inertia('Students/students',compact('students'));
    }

    public function filterStudents(Request $request){
        $students = Student::with('studentGrade')->where(function($query)use($request){
                $query->where('fullname', 'LIKE', '%'.$request->search.'%')
                ->orWhere('dob', 'LIKE', '%'.$request->search.'%')
                ->orWhere('sex','LIKE', '%'.$request->search.'%')
                ->orWhere('student_id', 'LIKE', '%'.$request->search.'%')
                ->orWhere('grade', 'LIKE', '%'.$request->search.'%')
                ->orWhere('arm', 'LIKE', '%'.$request->search.'%');
            })->paginate(20);

        return response()->json($students);
    }

    /**
     * search for student
     */
    public function searchForChildren(Request $request){
        $students = Student::query()
        ->when($request->input('search'), function($query,$search){
            $query->withCount('Parents')->where('fullname','like',"%{$search}%")
            ->orWhere('student_id','like',"%{$search}%");
        })->get();
        
        return $students;
    }

    /**
     * Delete Student
     */
    public function deleteStudent(Request $request){
        Student::find($request->id)->delete();
        return response()->json(['success'=>true]);
    }

    /**
     * Enter student's scores
     */

     public function addStudentScores(Request $request){

        $settings = $this->getSettings();

        if(!$settings){
            return redirect()->back();
        }

         if($request->section=="primary" || $request->section == "nursery"){
              //if no class supplied then go to the class page
           
            if(!$request->grade){
                return redirect('/classes');
            }

            if($request->arm && $request->arm !=='null'){
                $students = Student::with('subjects')->where('grade',$request->grade)->where('arm',$request->arm)->paginate(20)->withQueryString();
                $page= 0;
            }else{
              $students = Student::with('subjects')->where('grade',$request->grade)->paginate(20)->withQueryString();
                $page= 0;  
            }
          
                //if a student is selected, give me the subject, else give me the first subject
            // if(!$request->newpage){
            //     if(!$request->page){
            //             $student = [0];
            //             $selectedstudent = 0;
            //     }else{
            //             $student = $students[$request->currentStudent];
            //             $selectedstudent=$request->currentStudent;
            //     }
            // }else{
            //         $student = $students[0];
            //         $selectedstudent = 0;
            // }
            if(!$request->singleStudent){
                if($request->currentStudent == 0){
                    $student = $students[0];
                    $selectedstudent = 0;
                }else{
                    $student = $students->filter(function($data) use($request){
                        return $data->id == $request->studentid;
                    });
                    if($student){
                        $student = $student->first();
                        $selectedstudent=$request->currentStudent;
                    }
                }
                
            }else{
                 $student = $students->where('id', $request->studentid);
                    if($student->count() > 0){
                        $keys = $student->keys();
                        $student = $student->first();
                        $selectedstudent=$keys[0];
                    }
                     $selectedstudent=$keys[0];
            }

            $subjects = isset($student->subjects)? $student->subjects : [];
          
            $grade = $request->grade;
            //if no student is selected return back
            if(!$request->studentid){
                $section = $request->section;
                    return inertia('Students/addstudentscores',compact('students','subjects','student','grade','section'));
            }

            //paginator increment
                if(request()->page > 1){
                    $page = $students->firstItem()-1;
                }
            
                $currentpage = $request->page? $request->page: 1;
             
                $primaryexam = PrimaryExam::where('student_id',$request->studentid)->where('grade',$request->grade)->where('term', $settings->term)->where('session',$settings->session)->get();
         
            $subj_transform = collect($subjects);
            //remove the id from the object
            $subj_transform->map(function($value, $key){
                unset($value->id);
                    return $value;
            });
                //combines the subject and primary exam if primaryexam is not empty
            if(count($primaryexam) > 0){
                    $subjects = $subj_transform->transform(function($item, $key) use ($primaryexam){
                        $found = $primaryexam->where('subject',$item->subject);
                        if(count($found) > 0){
                            return $found->first();
                        }else{
                            return $item;
                        }
                    });
            }
            
            $term = $settings->term;
            $session = $settings->session;
            $section = $request->section;
                return inertia('Students/addstudentscores',compact('students','page','student','grade','currentpage','selectedstudent','subjects','primaryexam','section', 'session', 'term'));
         }

         //for pre nursery
         if($request->section == 'pre nursery'){
          
            if($request->arm && $request->arm !=='null'){
                $students = Student::with('subjects')->where('grade',$request->grade)->where('arm', $request->arm)->paginate(20)->withQueryString();
            }else{
                $students = Student::with('subjects')->where('grade',$request->grade)->paginate(20)->withQueryString();
            }

            $page = 0;
            // if(!$request->newpage){
            //     if(!$request->page){
            //             $student = [0];
            //             $selectedstudent = 0;
            //     }else{
            //             $student = $students[$request->currentStudent];
            //             $selectedstudent=$request->currentStudent;
            //     }
            // }else{
            //         $student = $students[0];
            //         $selectedstudent = 0;
            // }
         
             if(!$request->singleStudent){
                if($request->currentStudent == 0){
                    $student = $students[0];
                    $selectedstudent = 0;
                }else{
                    $student = $students->filter(function($data) use($request){
                        return $data->id == $request->studentid;
                    });
                    if($student){
                        $student = $student->first();
                        $selectedstudent=$request->currentStudent;
                    }
                }
             
            }else{
                 $student = $students->where('id', $request->studentid);
                    if($student->count() > 0){
                        $keys = $student->keys();
                        $student = $student->first();
                        $selectedstudent=$keys[0];
                    }
                     $selectedstudent=$keys[0];
            }

            if(request()->page > 1){
                $page = $students->firstItem()-1;
            }

            $currentpage = $request->page? $request->page: 1;
            $grade = $request->grade;
            $section = $request->section;

            $subjects = isset($student->subjects)? $student->subjects->groupBy('category') : [];

            if($request->studentid){
                $scores = PreNurseryExam::where('student_id',$request->studentid)->where('session',$settings->session)->where('term',$settings->term)->get();
                $subj_transform = collect($subjects);
             
                //remove the id from the object
                $subj_transform->map(function($value, $key){
                    unset($value->id);
                        return $value;
                });

                if(count($subj_transform) > 0){
                    $newsubj = [];
                    $subj_transform = $subj_transform->transform(function ($item) use ($scores) {
                
                        $matched = $scores->where('category', $item->first()->category);
                       
                        $newval = $matched->isNotEmpty() ? $matched->concat($item) : $item;
                        return $newval->unique('subject');
                      
                    });
                }
                $subjects = $subj_transform;
//dd($subjects);
            }
           
            $session = $settings->session;
            $term = $settings->term;

            return inertia('Students/addprenurseryscores',compact('students','student','selectedstudent','page','currentpage','grade','section','subjects', 'session', 'term'));
         }
        
        
        if ($request->section == "junior secondary") { 
            // Fetch class with related subjects
            $clas = Classes::with('subjects', 'arms.subjects')
                ->where('class_name', $request->grade)
                ->where('section', $request->section)
                ->first();
    
            if (!$clas) {
                return redirect()->to('/assign-subjects?grade='.$request->grade.'&section='.$request->section);
            }
        
            // Determine the subjects based on arm selection
            if ($request->arm && $request->arm !== 'null') {
                $subjects = optional($clas->arms->where('arm_name', $request->arm)->first())->subjects ?? collect();
            } else {
                $subjects = $clas->subjects;
            }
       
            if ($subjects->isEmpty()) {
                return redirect()->to('/assign-subjects?grade='.$request->grade.'&section='.$request->section);
            }
        
            // Determine the selected subject
            $subj = $request->subject ?? $subjects->first()->subject;
      
            // Fetch students for the subject in the given section
            $arm = $request->arm === 'null' ? null : $request->arm;

            $studentsubject = Subjects::with(['student' => function ($query) use ($request, $arm) {
                    $query->where('grade', $request->grade)
                    ->when($request->arm, function($query)use ($arm){
                        $query->where('arm',$arm);
                    });
                }])
                ->where('subject', $subj)
                ->where('section', $request->section)
                ->first();
        //dd($studentsubject);
            if (!$studentsubject || $studentsubject->student->isEmpty()) {
                return redirect('/assign-subjects?section='.$request->section.'&grade='.$request->grade);
            }
        
            $students = $studentsubject->student;
            $grade = $request->grade;
            $section = $request->section;
            $currentpage = ($request->currentpage === '0' || $request->currentpage) ? $request->currentpage : "not";
            $subject = $studentsubject->subject;
        
            // Fetch secondary exams related to the subject
            $secondaryExam = secondaryExam::where('subject', $subj)
                ->where('grade', $request->grade)
                ->where('term', $settings->term)
                ->where('session', $settings->session)
                ->get();
        
            // Merge exam results with students
            if ($secondaryExam->isNotEmpty()) {
                $students = $students->map(function ($student) use ($secondaryExam) {
                    $examRecord = $secondaryExam->where('student_id', $student->id)->first();
                    
                    if ($examRecord) {
                        $student->first_ca = $examRecord->first_ca;
                        $student->second_ca = $examRecord->second_ca;
                        $student->exam = $examRecord->exam;
                        $student->total = $examRecord->total;
                        $student->remark = $examRecord->remark;
                        $student->subject_id = $examRecord->id;
                    }
                    
                    return $student;
                });
            }
        
            return inertia('Students/addsecondarystudentscore', compact(
                'students', 'subject', 'clas', 'subjects', 'currentpage', 'grade', 'section', 'settings'
            ));
        }
        
        
        if ($request->section == 'senior secondary') {

            // Fetch class with related subjects
            $clas = Classes::with('subjects', 'arms.subjects')
                ->where('class_name', $request->grade)
                ->where('section', $request->section)
                ->first();
       
            if (!$clas) {
                return redirect()->to('/assign-subjects?grade=' . $request->grade . '&section=' . $request->section);
            }
        
            // Determine the subjects based on arm selection
            $subjects = $request->arm
                ? $clas->arms->where('arm_name', $request->arm)->first()->subjects()->get()
                : $clas->subjects;
    
            if ($subjects->isEmpty()) {
                return redirect()->to('/assign-subjects?grade=' . $request->grade . '&section=' . $request->section);
            }
        
            // Determine the selected subject
            $subj = $request->subject ?? $subjects->first()->subject;
        
            // Fetch students for the subject in the given section
            $studentsubject = Subjects::with(['student' => function ($query) use ($request) {
                $query->where('grade', $request->grade)
                      ->when($request->arm, function ($query) use ($request) {
                          $query->where('arm', $request->arm);
                      });
            }])
            ->where('subject', $subj)
            ->where('section', $request->section)
            ->first();
       
            if (!$studentsubject || $studentsubject->student->isEmpty()) {
                //return redirect('/assign-subjects?section=' . $request->section . '&grade=' . $request->grade);
                return redirect('/add-students');
            }
        
            $students = $studentsubject->student;
            $grade = $request->grade;
            $arm = $request->arm;
            $currentpage = ($request->currentpage === '0' || $request->currentpage) ? $request->currentpage : "not";
            $section = $request->section;
            $subject = $studentsubject->subject;
        
            // Fetch senior secondary exams related to the subject
            $seniorSecondaryExam = SeniorSecondaryExam::where('subject', $subj)
                ->where('grade', $request->grade)
                ->where('term', $settings->term)
                ->where('session', $settings->session)
                ->get();
        
            // Merge exam results with students
            if ($seniorSecondaryExam->isNotEmpty()) {
                $students = $students->map(function ($student) use ($seniorSecondaryExam) {
                    $examRecord = $seniorSecondaryExam->where('student_id', $student->id)->first();
        
                    if ($examRecord) {
                        $student->first_ca = $examRecord->first_ca;
                        $student->second_ca = $examRecord->second_ca;
                        $student->exam = $examRecord->exam;
                        $student->total = $examRecord->total;
                        $student->remark = $examRecord->remark;
                        $student->subject_id = $examRecord->id;
                    }
        
                    return $student;
                });
            }
        
            return inertia('Students/addseniorsecondarystudentscore', compact(
                'students', 'subject', 'clas', 'subjects', 'currentpage', 'grade', 'section', 'settings', 'arm'
            ));
        }
        
     }

     /**
      * Save student's scores
      */
      public function saveScores(Request $request){

          //save the junior secondary exam
          $settings = $this->getSettings();
         if($request->section == "junior secondary"){
            foreach($request->data as $data){
                secondaryExam::where('term', $data['term'])
                ->where('session', $data['session'])
                ->where('student_id', $data['student_id'])
                ->where('subject', $data['subject'])
                ->delete();
               }

            $success= secondaryExam::upsert(
               $request->data,
                ['id', 'term', 'session'],
                ['subject','first_ca','second_ca','exam','total','remark']
            );

            if($success){
                return ['success'=>true];
            }
         }

         //senior secondary
         if($request->section == 'senior secondary'){
            
            // SeniorSecondaryExam::where('term', $request->data[0]['term'])
            // ->where('session', $request->data[0]['session'])
            // ->where('student_id', $request->data[0]['student_id'])
            // ->delete();

           foreach($request->data as $data){
            SeniorSecondaryExam::where('term', $data['term'])
            ->where('session', $data['session'])
            ->where('student_id', $data['student_id'])
            ->where('subject', $data['subject'])
            ->delete();
           }

            $success= SeniorSecondaryExam::upsert(
                $request->data,
                 ['id', 'term', 'session'],
                 ['subject','first_ca','second_ca','exam','total','remark']
             );
             if($success){
                 return ['success'=>true];
             }
         }

         //pre nursery section 
        //  if($request->section == "pre nursery"){
        //     PreNurseryExam::where('student_id', $request->student_id)->where('term', $settings->term)->where('session', $settings->session)->delete();
        //     $scores = $request->except('student_id','section','grade');
        //    // PreNurseryExam::upsert($scores['data'], ['student_id', 'id', 'term', 'session'],['value']);
        //    $pre_nursery_data = [];
        //    foreach($scores['data'] as $score){
        //     PreNurseryExam::updateOrCreate(
        //         ['student_id'=>$score['student_id'], 'id'=>$score['id'], 'term'=>$score['term'], 'session'=>$score['session']],
        //         $score
        //     );
        //     array_push($pre_nursery_data, $score);
        //    }
        //    PreNurseryExam::insert($pre_nursery_data);
        //     return response()->json(['success'=>true]);
        //  }
         //pre nursery section 
         if($request->section == "pre nursery"){
        
            $scores = $request->except('section','grade','id');
           	$scores = array_values(array_pop($scores));
            if(count($scores) > 0){
           PreNurseryExam::where('student_id', $scores[0]['student_id'])->where('term', $settings->term)->where('session', $settings->session)->delete();
       
           PreNurseryExam::insert($scores);
            }
            return response()->json(['success'=>true]);
         }

          $scores = collect($request->data);
          $chunk = $scores->chunk(12);

       //transforms the data collected from the exam page
       $result = $chunk->transform(function($item, $key){
            $items = $item->flatten();
           
            return [
                $items[0]=>$items[1],
                $items[2]=>$items[3],
                $items[4]=>$items[5],
                $items[6]=>$items[7],
                $items[8]=>$items[9],
                $items[10]=>$items[11],
                $items[12]=>$items[13],
                $items[14]=>$items[15],
                $items[16]=>$items[17],
                $items[18]=>$items[19],
                $items[20]=>$items[21],
                $items[22]=>$items[23]
            ];
        });
        //dd($result->toArray());
        // PrimaryExam::insert($result->toArray());
        
        //dd(PrimaryExam::where('student_id', $request->student_id)->where('term', $settings->term)->where('session', $settings->session)->get());
        PrimaryExam::where('student_id', $request->student_id)->where('term', $settings->term)->where('session', $settings->session)->delete();

       $success= PrimaryExam::upsert(
            $result->toArray(),
            ['id', 'student_id', 'term', 'session'],
            ['subject','first_ca','second_ca','exam','total','remark']
        );

        if($success){
            return ['success'=>true];
        }
      }
      /**
       * Gets the page for the affective disposition
       */
      public function affectiveDisposition(Request $request){
        if($request->section == 'pre nursery'){
            if($request->arm){
                $students = Student::with(['preNurseryAffective'])->where('grade',$request->grade)->where('arm',$request->arm)->paginate(20)->withQueryString();
             }else{
                 $students = Student::with(['preNurseryAffective'])->where('grade',$request->grade)->paginate(20)->withQueryString();
             }
        }else{
            if($request->arm){
                $students = Student::with(['primaryAffective', 'secondaryAffective'])->where('grade',$request->grade)->where('arm',$request->arm)->paginate(20)->withQueryString();
            }else{
                $students = Student::with(['primaryAffective', 'secondaryAffective'])->where('grade',$request->grade)->paginate(20)->withQueryString();
            }
        }
       
       
        //paginator increment
        $page= 0;
        if(request()->page > 1){
            $page = $students->firstItem()-1;
        }
        $grade = $request->grade;
        $selectedstudent = $request->currentStudent;
        if($request->section == 'pre nursery'){
            $affective= isset($students[$selectedstudent]->preNurseryAffective)? $students[$selectedstudent]->preNurseryAffective: [];
        }elseif($request->section == 'primary' || $request->section == 'nursery'){
            $affective= isset($students[$selectedstudent]->primaryAffective[0])? $students[$selectedstudent]->primaryAffective[0]: [];
        }elseif($request->section == 'junior secondary' || $request->section == 'senior secondary'){
            $affective= isset($students[$selectedstudent]->secondaryAffective[0])? $students[$selectedstudent]->secondaryAffective[0]: [];
        }
     
        if($request->currentStudent == 0){
            $studentid = isset($students[0]->id)? $students[0]->id : 0;
        }else{
            $studentid = $request->studentid;
        }
        $section = $request->section;
          return inertia('Students/affectivedisposition', compact('students','page','grade','selectedstudent','studentid','affective','section'));
      }
      /**
       * Saves the affective disposition
       */
      public function saveAffective(Request $request){
          $settings = $this->getSettings();
          if(!$settings){
            return redirect()->back();
          }

          $request->validate([
           
              'studentid'=>'required',
          ]);
          $request->merge(['term'=>$settings->term,'session'=>$settings->session]);

          if($request->section == 'pre nursery'){
            preNurseryAffective::updateOrCreate(['id'=>$request->id, 'studentid'=>$request->studentid, 'term'=>$request->term, 'session'=>$request->session], $request->all());
          }else if($request->section == 'primary' || $request->section == 'nursery'){
            primaryAffective::updateOrCreate(['id'=>$request->id, 'studentid'=>$request->studentid, 'term'=>$request->term, 'session'=>$request->session], $request->all());
          }else if($request->section == 'junior secondary' || $request->section == 'senior secondary'){
            secondaryAffective::updateOrCreate(['id'=>$request->id, 'studentid'=>$request->studentid, 'term'=>$request->term, 'session'=>$request->session], $request->all());
          }

          return $request->all();
      }

      /**
       * Get the attendance page
       */
      public function getAttendance(Request $request){
        if($request->arm){
            $students = Student::with('attendance')->where('grade',$request->grade)->where('arm',$request->arm)->get();
        }else{
           $students = Student::with('attendance')->where('grade',$request->grade)->get(); 
        }
          
          $grade = $request->grade;
        return inertia('Students/attendance',compact('students','grade'));
      }
      /**
       * Save attendance
       */
      public function attendance(Request $request){
        $settings = $this->getSettings();
        if(!$settings){
            return redirect()->back();
        }

          $form = collect($request->all());
          $chunk = $form->chunk(7);
         
        $transformed = $chunk->transform(function($item, $key) use($settings){
            $item = $item->flatten();
           return [
               $item[0] => $item[1],
               $item[2] => $item[3],
               $item[4] => $item[5],
               $item[6] => $item[7],
               $item[8] => $item[9],
               $item[10] => $item[11],
               $item[12] => $item[13],
               'session' => $settings->session,
               'term' => $settings->term
           ];
        });

        $success= attendance::upsert(
            $transformed->toArray(),
            ['id'],
            ['no_of_times_school_opened','no_of_times_present','no_of_times_absent','no_of_times_late','no_of_times_absent_from_drills','studentid']
        );

        return $success;
      }
      /**
       * Gets physical development
       */
      public function getPhysicalDevelopment(Request $request){
        if($request->arm){
            $students = Student::with('physicaldevelopment')->where('grade',$request->grade)->where('arm',$request->arm)->get();
        }else{
            $students = Student::with('physicaldevelopment')->where('grade',$request->grade)->get();
        }
          
          return inertia('Students/physicaldevelopment', compact('students'));
      }
      /**
       * Save the physical development
       */
      public function physicalDevelopment(Request $request){
        $settings = $this->getSettings();
        if(!$settings){
            return redirect()->back();
        }
        $form = collect($request->all());
        $chunk = $form->chunk(6);
        $transformed = $chunk->transform(function($item, $key) use($settings){
            $item = $item->flatten();
              return [
                $item[0] => $item[1],
                $item[2] => $item[3],
                $item[4] => $item[5],
                $item[6] => $item[7],
                $item[8] => $item[9],
                $item[10] => $item[11],
                'session' => $settings->session,
                'term'   => $settings->term,
              ];
        });

        $success= physicaldevelopment::upsert(
            $transformed->toArray(),
            ['id'],
            ['initial_height','current_height','initial_weight','current_weight','studentid','session','term','studentid']
        );
        return $success;
      }

      /**
       * Student's remarks
       */
      public function getStudentsRemarks(Request $request){
        $settings = $this->getSettings();
        if(!$settings){
            return redirect()->back();
        }

        if($request->arm){
            $students = Student::with(['remarks'=>function($query) use ($request, $settings) {
                $query->where('term',$settings->term)->where('session',$settings->session);
            }])->where('grade',$request->grade)->where('arm',$request->arm)->get();
        }else{
            $students = Student::with(['remarks'=>function($query) use ($request, $settings) {
                $query->where('term',$settings->term)->where('session',$settings->session);
            }])->where('grade',$request->grade)->get();
        }
        
        $grade = $request->grade;
        return inertia('Students/remarks',compact('students','grade', 'settings'));
      }

      /**
       * Saves student's remarks
       */
      public function saveStudentsRemarks(Request $request){
        //dd($request->all());
        remark::updateOrCreate([
            'id'=>$request->id
        ],$request->all());
        return response()->json(['success'=>true]);
      }

      public function studentBehaviourPage(Request $request){
        $settings = $this->getSettings();
        if(!$settings){
            return redirect()->back();
        }

        if($request->arm){
            $students = Student::with(['behaviour'=>function($query) use ($request, $settings) {
                $query->where('term',$settings->term)->where('session',$settings->session);
            }])->where('grade',$request->grade)->where('arm',$request->arm)->get();
        }else{
            $students = Student::with(['behaviour'=>function($query) use ($request, $settings) {
                $query->where('term',$settings->term)->where('session',$settings->session);
            }])->where('grade',$request->grade)->get();
        }
        $grade = $request->grade;
        
        return inertia('Students/behaviour', compact('students', 'grade', 'settings'));
      }

      public function saveStudentBehaviour(Request $request){
        //dd($request->all());
        Behaviour::updateOrCreate([
            'id'=>$request->id
        ],$request->all());
        return response()->json(['success'=>true]);
      }

      public function uploadScores(Request $request){
        $section = $request->section;
        $class= $request->grade;
        $arm = $request->arm; 
        $class_id = $request->class_id;
        $arm_id = $request->arm_id;

        return inertia('Students/uploadScores', compact('section', 'class', 'arm', 'class_id', 'arm_id'));
      }

      public function savePrimaryUploadedScores(Request $request){
        $allScores = $request->all();
        $settings = $this->getSettings();
        $section = collect($allScores)->first()[0]['section'];
       
        foreach($allScores as $key=>$scores){
           $new_scores = [];
            foreach($scores as &$item){
                $new_item = array_merge($item, ['term'=>$settings->term, 'session'=>$settings->session]);
                unset($new_item['student']);
                unset($new_item['section']);
                array_push($new_scores, $new_item);
            }
            if($section == 'primary'){
                PrimaryExam::where('student_id', $key)->where('term', $settings->term)->where('session', $settings->session)->delete();
                PrimaryExam::insert($new_scores);
            }else if($section == 'junior secondary'){
                secondaryExam::where('student_id', $key)->where('term', $settings->term)->where('session', $settings->session)->delete();
                secondaryExam::insert($new_scores);
            }else if($section == 'senior secondary'){
                SeniorSecondaryExam::where('student_id', $key)->where('term', $settings->term)->where('session', $settings->session)->delete();
                SeniorSecondaryExam::insert($new_scores);
            }
        }
        return response()->json(['success'=>true]);
      }

}
