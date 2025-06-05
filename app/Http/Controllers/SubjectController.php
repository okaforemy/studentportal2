<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Section;
use App\Models\Subjects;
use App\Models\Student;
use App\Models\StudentSubjects;
use Illuminate\Support\Facades\DB;
use App\Models\Classes;
use App\Models\Arms;

class SubjectController extends Controller
{
    /**
     * The assign subject page
     */
    public function index(){
        $sections = Section::all();
        $latestsubjects = Subjects::latest()->paginate(10);
        $page= 0;
        if(request()->page > 1){
            $page = 10 * (request()->page - 1);
        }
       
        return inertia('Subjects/addSubjects',compact('sections','latestsubjects','page'));
    }

    /**
     * Add subjects
     */
    public function AddSubject(Request $request){
     
        $sj = array();
        $subjects = $request->subjects;
        $allsubjects = Subjects::get();
        $sections = $request->sections;
       
        //check if the subjects already exist
        foreach($subjects as $key => $sub){
           $found = $allsubjects->where('subject',$sub)->where('section',$sections[0]['name'])->first();
           if($found){
               unset($subjects[$key]);

           }
        }
        if(empty($subjects)){
            return false;
        }
        
        foreach($sections as $key=>$value){
            foreach($subjects as $subj){
                $myarr = [
                    'subject'=>$subj,
                    'section' =>$value['name'],
                    'category' => $request->category,
                    'created_at'=>\Carbon\Carbon::now(),
                    'updated_at'=>\Carbon\Carbon::now(),
                ];
                array_push($sj, $myarr);
            }
        }

        $subj = new Subjects();
        $subj->insert($sj);
        
        return $sj;
    }

    /**
     * Get the list of subjects for a selected section
     */
    public function getAssignSubject(Request $request){
         $subjects = Subjects::where('section',$request->section)->get();
       
        $classes = Classes::with(['arms'=>function($query){
            $query->with('subjects');
        }, 'subjects'])->where('class_name',$request->grade)->first();

        if($classes){
            if($request->arm){
                $selected_subj = $classes->arms->where('arm_name', $request->arm)->first()->subjects()->get();
            }else{
                $selected_subj = $classes->subjects()->get();
            }
            //dd($selected_subj);
        }else{
            $selected_subj = [];
        }
        
        $grade = $request->grade;
        $arm = $request->arm;
        return inertia('Subjects/assignSubjects', compact('subjects','grade','arm','selected_subj'));
    }

    /**
     * Assigns subject to students
     */
    public function assignSubjects(Request $request){
       
        $subjects_id = collect($request->all()['subjects'])->pluck('id');
        $class = Classes::with('arms')->where('class_name', $request->grade)->first();
       
        if($request->arm){
          $students = Student::where('grade',$request->grade)->where('arm',$request->arm)->get();
            //$arm = Arms::where('arm_name', $request->arm)->first();
            
            $class->arms()->where('arm_name', $request->arm)->first()->subjects()->sync($subjects_id);
            //$arm->subjects()->sync($subjects_id);
        }else{
            if($class){
                $class->subjects()->sync($subjects_id);
            }
           $students = Student::where('grade',$request->grade)->get();
        }
      
        foreach($students as $key=>$student){
           $student->subjects()->sync($subjects_id);
        }

        $edited=true;
       return redirect()->route('classes')->with(['edited'=>$edited]);
    }

    /**
     * Delete subject
     */
    public function deleteSubject(Request $request){
        $subjects = Subjects::find($request->id);
        $subjects->student()->detach();
        $subjects->delete();
        return true;
    }

    public function subjects(Request $request){
        $subjects = Subjects::paginate(20);
        return inertia('Subjects/subjects', compact('subjects'));
    }

    public function editSubject($id){
        $subject = Subjects::find($id);
        $sections = Section::all();
        $latestsubjects = Subjects::latest()->paginate(10);
        $page= 0;
        if(request()->page > 1){
            $page = 10 * (request()->page - 1);
        }
        return inertia('Subjects/editSubject', compact('subject', 'sections', 'latestsubjects', 'page'));
    }

    public function updateSubject(Request $request){
        $subject = Subjects::find($request->id);
        $subject->subject = $request->subject;
        $subject->save();
        return redirect()->back();
    }

    public function searchSubjects(Request $request){
        $subjects = Subjects::where(function($query) use($request){
            $query->where('subject', 'LIKE', '%'.$request->search.'%')
                ->orWhere('section', 'LIKE', '%'.$request->search.'%' )
                ->orWhere('category', 'LIKE', '%'.$request->search.'%');
        })->paginate(20);

        return response()->json($subjects);
    }
}

