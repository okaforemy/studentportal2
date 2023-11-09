<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subjects;
use App\Models\Classes;
use App\Models\Question;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\CBTSetting;
use Session;
use App\Models\CBTStudents;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpWord\IOFactory;

class CBTController extends Controller
{
    public function addQuestion(){
        $subjects = Subjects::all();
        $classes = Classes::all();
        $questions = [];
        if(Session::has('grade')){
            $questions = Question::where('grade',session('grade'))->where('subject', session('subject'))->orderBy('created_at','DESC')->get();
        }
        return inertia('cbt/questions',compact('subjects','classes','questions'));
    }

    function convertToPhpArray($text) {
        $questions = explode("</ol>", $text);
        $data = [];
   
        foreach ($questions as $question) {
            $lines = explode("<ol>", $question);
            //if the first element is empty go to the next or get the first element
            $currentLine = $lines[0] ==""? next($lines) : $lines[0];
           
                $currentQuestion = strip_tags(trim($currentLine));
              
               $newLine = next($lines);
                if(isset($newLine) && $newLine !=""){
                    
                    $options = explode("<li>", $newLine);
                    //remove empty elements
                    $options =  array_filter($options, function ($value) {
                        return $value !== "" && $value !== null;
                    });
                    //reset the array index
                    $options = array_values($options);

                    $list = ['a','b','c','d','e','f'];
                    $position = 0;
                   
                    foreach ($options as $index=> $option) {
                       
                        if(strpos($option, '<strong>') !== false){
                            $position = $index;
                        }
                        $option = trim(strip_tags($option));
                        if (!empty($option)) {
                           
                            $data[$currentQuestion][$list[$index]] = $option;
                        }
                    }
                    $data[$currentQuestion]['answer'] = $list[$position];
                }
           // }
            
        }
    
        return $data;
    }
    

    public function saveQuestions(Request $request){
       
        if($request->isRichText){
            $richText = $this->convertToPhpArray($request->question);
            $data = [];
            foreach($richText as $index => $rts){
               $arr = [
                'question' => $index,
                'answer_to_question' => "option_".$rts['answer'],
                'option_a' => $rts['a'],
                'option_b' => $rts['b'],
                'option_c' => $rts['c'],
                'option_d' => $rts['d'],
                'option_e' => isset($rts['e'])? $rts['e']:'',
                'subject' => $request->subject,
                'subject_id' => $request->subject,
                'grade' => $request->grade,
               ];
               array_push($data, $arr);
            }
           
            Question::insert($data);
            return redirect()->route('add-question');
        }
        
        $request->validate([
            'question'=>'required',
            'grade' =>'required',
            'subject' => 'required',
            'answer' => 'required'
        ]);

        if($request->question_id){
            $q = Question::find($request->question_id);
        }else{
            $q = new Question();
        }
        
        $q->grade = $request->grade;
        $q->subject = $request->subject;
        $q->subject_id = $request->subject;
        $q->question = $request->question;
        $q->option_a = $request->option_a;
        $q->option_b = $request->option_b;
        $q->option_c = $request->option_c;
        $q->option_d = $request->option_d;
        $q->option_e = $request->option_e;
        $q->answer_to_question = $request->answer;
        $q->save();

        session(['grade'=>$request->grade, 'subject'=>$request->subject]);
       // return redirect()->back()->with('questions', $questions);
       if($request->question_id){
        return redirect()->route('view-cbt-questions');
       }
       return redirect()->route('add-question');
    }

    public function addStudent(){
        $classes = Classes::all();
        $students = DB::table('cbtstudents')->where('grade',session('grade'))->orderByDesc('created_at')->get();
        return inertia('cbt/add-user', compact('classes','students'));
    }

    public function saveStudent(Request $request){
        $id = substr(uniqid() . mt_rand(), 7, 6);
       
        DB::table('cbtstudents')->insert([
            'firstname'=>$request->firstname,
            'lastname' => $request->lastname,
            'student_id'=> $id,
            'session' =>  '2022/2023',
            'term' => 'First term',
            'grade' => $request->grade,
            'created_at' =>\Carbon\Carbon::now()
        ]);

        session(['grade'=>$request->grade]);
        return redirect()->route('save-cbt-students');

    }

    public function deleteCBTStudent(Request $request){
        DB::table('cbtstudents')->where('id',$request->id)->delete();
        return redirect()->route('save-cbt-students');
    }

    public function CBTUsers(){
        $students = DB::table('cbtstudents')->get()->groupBy('grade');
        return inertia('cbt/users', compact('students'));
    }

    public function viewQuestions(){
        $classes = Classes::all();
        $subjects = Subjects::all();
        return inertia('cbt/view-questions',compact('classes','subjects'));
    }

    public function getQuestions(Request $request){
        $questions = Question::where('subject', $request->subject)->where('grade', $request->grade)->get();
        return response()->json($questions);
    }

    public function deleteQuestion(Request $request){
        Question::find($request->id)->delete();
        return redirect()->back();
    }

    public function settings(){
        $subjects = Subjects::where('section','junior_secondary')->orWhere('section','senior_secondary')->get();
        $settings = DB::table('cbt_settings')->get();
        return inertia('cbt/settings', compact('subjects','settings'));
    }

    public function saveSettings(Request $request){//dd($request->all());
        $data = [];
        foreach($request->subject as $index => $subject){
            $arr = [
                'subject'=>$subject,
                'date' => $request->date[$index],
                'time'=> $request->time[$index]?$request->time[$index]: \Carbon\Carbon::now(),
                'duration' => $request->duration[$index],
                'is_started' =>  0,
                'section' => $request->section[$index],
                'id'=> isset($request->id[$index])? $request->id[$index]:0,
            ];
            array_push($data, $arr);
        }
      
        CBTSetting::upsert($data,['id'],['date','duration','time', 'is_started']);
        return redirect()->back();
    }

    public function startExam(Request $request){
        DB::table('cbt_settings')->update(['is_started'=>0]);
        CBTSetting::where('id', $request->id)->update(['is_started'=> 1]);
        return redirect()->back();
    }

    public function CBTLogin(){
        return view('cbt-login');
    }

    public function CBTHome(){
        $subjects = CBTSetting::whereDate('date',now()->toDateString())->get();
        $fullname = auth()->user()->lastname." ".auth()->user()->firstname;
        return inertia('cbt/exam-home', compact('subjects','fullname'));
    }

    public function CBTLoginValidate(Request $request){
        $user = CBTStudents::where('student_id', $request->student_id)->first();
        if($user){
            if(Auth::loginUsingId($user->id)){
                return redirect()->route('exam-home');
            }
        }
        

        return redirect()->back()->with(['error'=>'Wrong ID']);
    }

    public function exam(){
        $fullname = auth()->user()->lastname." ".auth()->user()->firstname;
        $student_id = auth()->user()->student_id;
        $questions = DB::table('student_questions')->where('student_id',request()->student_id)->where('subject', request()->subject_id)->get();
       if($questions->isEmpty()){
        return redirect()->back();
       }
        return inertia('cbt/exam', compact('fullname','student_id','questions'));
       //return view('exam',compact('fullname','student_id'));
    }

    public function prepareQuestion(Request $request){
        $student = CBTStudents::find(auth()->user()->id);
        $subject = Subjects::where('subject',$request->subject)->where('section',$request->section)->first();
        
        $questions = Question::where('grade', $student->grade)->where('subject_id',$subject->id)->get();
        
        if($questions){
            $shuffled = $questions->shuffle();

            session(['student_id'=>$student->student_id, 'subject'=>$subject->id, 'grade'=>$student->grade, 'fullname'=>auth()->user()->firstname." ".auth()->user()->lastname]);
            $data = [];
            foreach($shuffled as $sh){
                $ar = [
                    'subject' =>$sh->subject,
                    'question' => $sh->question,
                    'option_a' => $sh->option_a,
                    'option_b' => $sh->option_b,
                    'option_c' => $sh->option_c,
                    'option_d' => $sh->option_d,
                    'option_e' => $sh->option_e,
                    'correct_answer' => $sh->answer_to_question,
                    'student_id' => $student->student_id,
                ];
                array_push($data, $ar);
            }
    
            $user_questions = DB::table('student_questions')->where('student_id',$student->student_id)->where('subject', $subject->id)->get();
            if(count($user_questions) <= 0){
                 DB::table('student_questions')->insert($data);
                 $user_questions = DB::table('student_questions')->where('student_id',$student->student_id)->where('subject', $subject->id)->get();
            }
          
           // return response()->json($user_questions);
           $questions = $user_questions;
           $fullname = auth()->user()->lastname." ".auth()->user()->firstname;
           //return inertia('cbt/exam',compact('questions','fullname'));
           return redirect("cbt-exam?subject_id=$subject->id&&student_id=$student->student_id")->with('questions');
        }else{
            return response()->json([]);
        }
       
    }

    public function answerQuestion(Request $request){
        $state = DB::table('student_questions')->where('id', $request->question_id)->update([
            'your_answer' => $request->your_answer
        ]);
        return response()->json($state);
    }

    public function result(Request $request){
        $result = DB::table('student_questions')->where('student_id',session('student_id'))->where('subject',session('subject'))->get();
        $fullname = session('fullname');
        Auth::logout();
        return view('result', compact('result','fullname'));
    }

    public function getUploadQuestion(){
        return inertia('cbt/upload');
    }
    
}
