<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Parents;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Classes;
use App\Models\PreNurseryExam;
use App\Models\SeniorSecondaryExam;
use App\Models\secondaryExam;
use App\Models\PrimaryExam;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\Fee;
use Hash;

class ParentsController extends Controller
{
    //get the parents of the child
    public function getParent($id){
        $student=Student::with('parents')->find($id);
        $parents = $student->parents;
        return $parents;
    }

    //get the addparent page
    public function index(Request $request){
        $student_id = $request->id;
        $surname =$request->surname;
        $othernames = $request->othernames;
        $parents = $this->getParent($request->id);
        return inertia('Parents/addParent',compact('student_id','surname','othernames','parents'));
    }

    //get the edit parent page
    public function getEditParent(Request $request){
        $student_id = $request->id;
        $surname =$request->surname;
        $othernames = $request->othernames;
        $student =  $student=Student::with('parents')->find($request->id);
        $parents = $student->parents;
        $parent=$parents->where('id',$request->parent_id)->values();
        return inertia('Parents/editParent',compact('student_id','surname','othernames','parents','parent'));
    }

    //stores the parents record
    public function store(Request $request){
        $id = $request->id;
        if($request->id == ""){
            return redirect()->back()->withErrors(['error'=>'something went wrong']);
        }

        $request->validate([
            'fullname'=>'required',
            'nationality'=>'required',
            'home_address'=>'required',
            'phone'=>'required',
            'email'=>'nullable|email',
            'isparent'=>'required'
        ]);
        /* might need to validate the parents to avoid double entry should someone tamper with the frontend
            already taken care of. thanks
        */
       
        $st = Student::find($id);
        $parents = Parents::where('phone',$request->phone)->where('isparent', $request->isparent)->get();
        if(count($parents)>0){
            $parent = $parents->first();
                $parent->students()->attach($st->id);
        
                if($st->reg_progress < 100){
                    $st->reg_progress = $st->reg_progress+25;
                    $st->save();
                }
    
                return redirect()->back()->with('success',true);
        }

       \DB::transaction(function () use($request,$id,$st) {
            $success = Parents::create($request->all());
            $success->students()->attach($st->id);
            if($st->reg_progress < 100){
                $st->reg_progress = $st->reg_progress+25;
                $st->save();
            }
        });
       
       return redirect()->back()->with('success',true);
    }

    //searches for registered parents
    public function searchParent(Request $request){
        $search = Parents::query()
        ->when($request->input('search'), function($query,$search){
            $query->where('fullname','like',"%{$search}%")
            ->orWhere('email','like',"%{$search}%")
            ->orWhere('phone','like',"%{$search}%");
        })->get();
        return $search;
    }

    //edit parents
    public function editParent(Request $request){
        $request->validate([
            'fullname'=>'required',
            'nationality'=>'required',
            'home_address'=>'required',
            'phone'=>'required',
            'email'=>'nullable|email',
            'isparent'=>'required'
        ]);

        Parents::where('id',$request->id)->update([
            "fullname" => $request->fullname,
            "nationality" => $request->nationality,
            "occupation" => $request->occupation,
            "home_address" => $request->home_address,
            "office_address" => $request->office_address,
            "email" => $request->email,
            "phone" => $request->phone,
            "isparent" => $request->isparent,
        ]);
        return redirect()->back()->with('success','field edited successfully');
    }

    //add only parents
    public function getAddParentsOnly(){
        return inertia('Parents/addParentsOnly');
    }

    //submit the parents form
    public function addParentsOnly(Request $request){
        $request->validate([
            'fullname'=>'required',
            'nationality'=>'required',
            'home_address'=>'required',
            'phone'=>'required|unique:parents',
            'email'=>'nullable|email|unique:parents',
            'isparent'=>'required'
        ]);

        $request->merge(['password' => Hash::make('password')]);

        Parents::create($request->all());
        return redirect()->back();
    }

    //all parents
    public function Parents(){
        $parents = Parents::with('students')->paginate(10);
        return inertia('Parents/parents',compact('parents'));
    }

    //assign children to parents
    public function assignChildrenToParents(Request $request){
        $ids = [];
        $tags = $request->tags;
        foreach($tags as $tag){
            array_push($ids, $tag['id']);
        }
        
        $parent = Parents::find($request->parent_id);
        if($request->reassign){
            $parent->students()->detach();
        }
        $parent->students()->sync($ids);
       return redirect()->back();
    }

    //delete parent
    public function deleteParent(Request $request){
        $parent = Parents::find($request->id);
        $id=$parent->id;
        $parent->students()->detach();
        $parent->delete();
        return $id;
    }

    //get only parent edit page
    public function getEditOnlyParent(Request $request){
        $parent = Parents::find($request->id);
        return inertia('Parents/editParentsOnly',compact('parent'));
    }

    //edit only parent
    public function editOnlyParent(Request $request){
        $request->validate([
            'fullname'=>'required',
            'nationality'=>'required',
            'home_address'=>'required',
            'phone'=>'required|unique:parents,phone,'.$request->id,
            'email'=>'nullable|email|unique:parents,email, '.$request->id,
            'isparent'=>'required'
        ]);
        $parent = Parents::find($request->id);
        $request->request->remove('created_at');
        $request->request->remove('updated_at');
        $request->request->remove('deleted_at');
        //dd($request->all());
        Parents::where('id',$request->id)->update($request->all());
        return redirect('/parents');
    }

    public function searchParents(Request $request){

            $parents = Parents::with('students')->where(function($query)use($request){
                $query->where('fullname', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('nationality', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('occupation' , 'LIKE', '%'.$request->search.'%')
                    ->orWhere('home_address', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('office_address', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('email', 'LIKE', '%'.$request->search.'%')
                    ->orWhere('phone', 'LIKE', '%'.$request->search.'%');
            })->paginate(20);

            return response()->json($parents);
    }

    public function home(){
        $parent = auth()->user()->with(['students'=>function($query){
            $query->with('picture');
        }])->first();
        $students = $parent->students;
        return inertia('Parents/home', compact('students'));
    }

    public function parentLogin(){
        return view('parentLogin');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
 
        if (Auth::guard('parents')->attempt($credentials)) {
            $request->session()->regenerate();
 
            return redirect()->intended('/parents/home');
        }
 
        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ]);
    }

    public function getSingleChild(Request $request){
        $parent = auth()->user();

        // Ensure 'students' relationship is eager loaded
        $student = $parent->Students()
            ->with('picture')
            ->where('students.id', $request->student)
            ->first();

        if (!$student) {
            return redirect()->back()->with('error', 'Student not found or unauthorized.');
        }

        return inertia('Parents/singleChild', [
            'student' => $student
        ]);
    }

    public function checkResult(Request $request){
        $class = Classes::find($request->class_id);
         $result = 0;
        if($class){
            if($class->section == 'pre nursery'){
                $result = PreNurseryExam::where('term', $request->term)
                        ->where('student_id', $request->student_id)
                        ->where('session', $request->session)
                        ->where('is_approved', 0)
                        ->count();
            }

            if($class->section == 'nursery' || $class->section =='primary'){
                $result = PrimaryExam::where('term', $request->term)
                        ->where('student_id', $request->student_id)
                        ->where('session', $request->session)
                        ->where('is_approved', 0)
                        ->count(); 
            }

            if($class->section == 'junior secondary'){
                $result = secondaryExam::where('term', $request->term)
                        ->where('student_id', $request->student_id)
                        ->where('session', $request->session)
                        ->where('is_approved', 0)
                        ->count();
            }

            if($class->section == 'senior secondary'){
                $result = SeniorSecondaryExam::where('term', $request->term)
                        ->where('student_id', $request->student_id)
                        ->where('session', $request->session)
                        ->where('is_approved', 0)
                        ->count();
            }
        }

        $settings = Setting::where('term', $request->term)->where('session', $request->session)->first();

        return response()->json([
            'result' => $result,
            'term' => $request->term,
            'session' => $request->session,
            'section' => $class->section,
            'settings' =>$settings
        ]);
    }

    public function getFees(Request $request){
        $student = Student::with('fees')->find($request->student_id);
        return response()->json($student->fees);
    }

    public function getTransactions(Request $request){
      $settings = Setting::latest()->first();
        $transactions = Transaction::where('student_id', $request->student_id)->latest()->get();

                    $fee_sumary = Fee::where('student_id', $request->student_id)
                        ->where('term', $settings->term)
                        ->where('session', $settings->session)
                        ->first();
        return response()->json([
            'transactions' => $transactions,
            'fee_sumary' => $fee_sumary
        ]);
    }

}
