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
use App\Models\Setting;
use App\Models\CBTResult;
use App\Events\ResultSubmitted;
use App\Models\StudentQuestion;
use Str;

class CBTController extends Controller
{
    public function addQuestion(){
       // $subjects = Subjects::all();
        $classes = Classes::all();
        $questions = [];
        if(Session::has('grade')){
            $questions = Question::where('grade',session('grade'))->where('subject_id', session('subject'))->orderBy('created_at','DESC')->get();
        }
        return inertia('cbt/questions',compact('classes','questions'));
    }

    public function questionSubjects(Request $request){
        $subjects = Subjects::where('section', $request->section)->get();
        return response()->json($subjects);
    }

    function convertToPhpArray($text) {
        $questions = explode("</ol>", $text);
        $data = [];
   
        foreach ($questions as $question) {
            $lines = explode("<ol>", $question);
            //if the first element is empty go to the next or get the first element
            $currentLine = $lines[0] ==""? next($lines) : $lines[0];
          
                $currentQuestion = strpos($currentLine, "<img")? trim($currentLine) :strip_tags(trim($currentLine));
             //dd($currentQuestion);
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
       $settings = Setting::first();
        if($request->isRichText){ 
            $richText = $this->convertToPhpArray($request->question); 
            $data = [];
            foreach($richText as $index => $rts){
               $arr = [
                'question' => $index,
                'answer_to_question' => "option_".$rts['answer'],
                'option_a' => html_entity_decode(strip_tags($rts['a'])),
                'option_b' => html_entity_decode(strip_tags($rts['b'])),
                'option_c' => html_entity_decode(strip_tags($rts['c'])),
                'option_d' => html_entity_decode(strip_tags($rts['d'])),
                'option_e' => isset($rts['e'])? html_entity_decode(strip_tags($rts['e'])):'',
                'subject' => $request->subject,
                'subject_id' => $request->subject,
                'grade' => $request->grade,
                'session' => $settings->session,
                'term' => $settings->term,
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
        $q->session = $settings->session;
        $q->term = $settings->term;
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
        $settings = Setting::first();
        DB::table('cbtstudents')->insert([
            'firstname'=>$request->firstname,
            'lastname' => $request->lastname,
            'student_id'=> $id,
            'session' =>  $settings->session,
            'term' => $settings->term,
            'grade' => $request->grade,
            'created_at' =>\Carbon\Carbon::now()
        ]);

        session(['grade'=>$request->grade]);
        return redirect()->route('save-cbt-students');

    }

    public function deleteCBTStudent(Request $request){
        DB::table('cbtstudents')->where('id',$request->id)->delete();
        return redirect()->route('cbt-students');
    }

    public function CBTUsers(){
        $students = DB::table('cbtstudents')->get()->groupBy('grade');
        return inertia('cbt/users', compact('students'));
    }

    public function viewQuestions(){
        $classes = Classes::where('section', 'junior secondary')->orWhere('section', 'senior secondary')->get();
        $subjects = Subjects::all();
        return inertia('cbt/view-questions',compact('classes','subjects'));
    }

    public function getQuestions(Request $request){
        $settings = Setting::first();
        $questions = Question::where('subject_id', $request->subject)
                    ->where('grade', $request->grade)
                    ->where('term', $settings->term)
                    ->where('session',$settings->session)
                    ->get();
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
                'time'=> '00:00',
                'duration' => $request->duration[$index],
                'is_started' =>  0,
                'section' => $request->section[$index],
                'subject_id' => $request->subject_id[$index],
                'id'=> isset($request->id[$index])? $request->id[$index]:0,
            ];
            array_push($data, $arr);
        }
      //dd($data);
        CBTSetting::upsert($data,['id'],['date','duration', 'is_started']);
        return redirect()->back();
    }

    public function startExam(Request $request){
       // DB::table('cbt_settings')->update(['is_started'=>0]);
        if($request->is_started == 1){
            CBTSetting::where('id', $request->id)->update(['is_started'=> 0]);
        }else{
            CBTSetting::where('id', $request->id)->update(['is_started'=> 1]);
        }
        return redirect()->back();
    }

    public function CBTLogin(){
        return view('cbt-login');
    }

    public function CBTHome(){
        //$subjects = CBTSetting::whereDate('date',now()->toDateString())->get();
        $subjects = CBTSetting::where('is_started',1)->get();
        $fullname = auth()->user()->lastname." ".auth()->user()->firstname;
        return inertia('cbt/exam-home', compact('subjects','fullname'));
    }

    public function CBTLoginValidate(Request $request){
        $user = CBTStudents::where('student_id', $request->student_id)->first();

        if($user){
            if(Auth::guard('cbt')->loginUsingId($user->id)){
                
                $deviceToken = Str::random(32);
                $user->update(['device_token' => $deviceToken]);
                $request->session()->put('device_token', $deviceToken);

                return redirect()->route('exam-home');
            }
        }
        

        return redirect()->back()->with(['error'=>'Wrong ID']);
    }

    public function exam(){
        $fullname = auth()->user()->lastname." ".auth()->user()->firstname;
        $student_id = auth()->user()->student_id;
        $settings = Setting::first();
        $questions = DB::table('student_questions')
                    ->where('student_id',auth()->user()->student_id)
                    ->where('subject', request()->subject_id)
                    ->where('session',$settings->session)
                    ->where('term', $settings->term)
                    ->get();
       
       if($questions->isEmpty()){
        return redirect()->back();
       }
      $cbt_settings = CBTSetting::where('subject_id',$questions[0]->subject)->first();
      // $duration = $cbt_settings->duration;
      $duration = auth()->user()->timer;
    //    if(!$duration){
    //     Abort(401);
    //    }
        return inertia('cbt/exam', compact('fullname','student_id','questions','duration'));
    }

    public function prepareQuestion(Request $request){
        $student = CBTStudents::find(auth()->user()->id);
        $subject = Subjects::where('subject',$request->subject)
                    ->where('section',$request->section)
                    ->first();
        $settings = Setting::first();
        $student_score = CBTResult::where('subject_id', $subject->id)
                    ->where('student_id', auth()->user()->student_id)
                    ->where('term',$settings->term)
                    ->where('session', $settings->session)
                    ->first();
        
        if(is_null($student_score) == true){
            $questions = Question::where('grade', $student->grade)
                    ->where('subject_id',$subject->id)
                    ->where('session', $settings->session)
                    ->where('term', $settings->term)
                    ->get();
                 
            if(!$questions->isEmpty()){
                $shuffled = $questions->shuffle();

                session(['student_id'=>auth()->user()->student_id, 'subject'=>$subject->id, 'grade'=>$student->grade, 'fullname'=>auth()->user()->firstname." ".auth()->user()->lastname]);
                $data = [];
                foreach($shuffled as $sh){
                    $ar = [
                        'subject' =>$sh->subject_id,
                        'question' => $sh->question,
                        'option_a' => $sh->option_a,
                        'option_b' => $sh->option_b,
                        'option_c' => $sh->option_c,
                        'option_d' => $sh->option_d,
                        'option_e' => $sh->option_e,
                        'correct_answer' => $sh->answer_to_question,
                        'student_id' => auth()->user()->student_id,
                        'session' => $settings->session,
                        'term' => $settings->term
                    ];
                    array_push($data, $ar);
                }
        
                $user_questions = DB::table('student_questions')->where('student_id',auth()->user()->student_id)->where('subject', $subject->id)->where('term', $settings->term)->where('session', $settings->session)->get();
                if(count($user_questions) <= 0){
                    DB::table('student_questions')->insert($data);
                    $user_questions = DB::table('student_questions')->where('student_id',auth()->user()->student_id)->where('subject', $subject->id)->where('term', $settings->term)->where('session', $settings->session)->get();
                }

                //insert timer to the user table
                $cbt_setting = CBTSetting::where('subject_id', $subject->id)->first();
               
                CBTStudents::where('id', auth()->user()->id)->update(['timer'=>$cbt_setting->duration]);
               
                $questions = $user_questions;
                $fullname = auth()->user()->lastname." ".auth()->user()->firstname;
              
                return redirect("cbt-exam?subject_id=$subject->id&&student_id=$student->student_id")->with('questions');
               
            }
        
        }else{
            return redirect()->back()->with('error','Test taken for this subject');
        }
       
    }

    public function answerQuestion(Request $request){
        $settings = Setting::first();
        $state = DB::table('student_questions')->where('id', $request->question_id)->update([
            'your_answer' => $request->your_answer,
            'session' => $settings->session,
            'term' => $settings->term
        ]);

        CBTStudents::where('id', auth()->user()->id)->update(['timer'=>$request->duration]);
        return response()->json($state);
    }

    public function totalCBTMark($result){
        $count = 0;
        foreach($result as $r){
            if($r->correct_answer == $r->your_answer){
                $count++;
            }
        }
        return ($count * 2);
    }

    public function result(Request $request){ 
        $settings = Setting::first();
        //if the user is logged out, return to login page
        if(!session('student_id')){
            return redirect()->route('cbt-login');
        }
        $result = DB::table('student_questions')
                    ->where('student_id',session('student_id'))
                    ->where('subject',session('subject'))
                    ->where('session', $settings->session)
                    ->where('term', $settings->term)
                    ->get();
        $cbt_result = CBTResult::where('student_id', session('student_id'))
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->where('grade', session('grade'))
                    ->where('subject_id', session('subject'))
                    ->first();
        if(!$cbt_result){
            CBTResult::create([
                'fullname' => session('fullname'),
                'subject_id' => session('subject'),
                'student_id' => session('student_id'),
                'marks_obtainable' => (count($result) * 2),
                'marks_obtained' => $this->totalCBTMark($result),
                'grade' => session('grade'),
                'term' => $settings->term,
                'session' => $settings->session,
            ]);
        }

        //event(new ResultSubmitted(auth()->user()->firstname." ".auth()->user()->lastname));

        $fullname = session('fullname');
        $this->LogOut();
        return redirect()->route('cbt-login')->with('success','Exam submitted successfully, Goodluck!!!');
        //return view('result', compact('result','fullname'));
    }

    public function LogOut(){
        Auth::logout();
        session::forget('grade');
        session::forget('subject');
        session::forget('student_id');
    }

    public function logOutStudent(){
        $this->LogOut();
        return redirect()->route('cbt-login');
    }

    public function getUploadQuestion(){
        return inertia('cbt/upload');
    }

    public function CBTResults(Request $request){
        $settings = Setting::first();
        $results = DB::table('student_questions')
                    ->join('subjects', 'student_questions.subject', '=', 'subjects.id')
                    ->where('student_id',$request->student_id)
                    ->where('session', $settings->session)
                    ->where('term', $settings->term)
                    ->get();

        $results = $results->groupBy('subject');
        $keys = collect($results)->keys()->all();
        
        return response()->json(['results'=>$results,'keys'=>$keys]);
    }

    public function getResults(){
        $grades = Classes::where('section','junior_secondary')->orWhere('section','senior_secondary')->get();
        return inertia('cbt/results',compact('grades'));
    }

    public function getStudentsInAClass(Request $request){
        $students = CBTStudents::where('grade', $request->grade)->get();
        return response()->json($students);
    }
    
    public function StudentResult(Request $request){
        $settings = Setting::first();
        $result = CBTResult::with('subject')->where('student_id', $request->student_id)->where('term',$settings->term)->where('session', $settings->session)->get();
        return response()->json($result);
    }

    public function CBTResultsBySubjects(Request $request){
        $settings = Setting::first();
    //    $results= DB::table('student_questions')
    //         ->leftJoin('cbtstudents', 'student_questions.student_id','=', 'cbtstudents.student_id')
    //         ->select('cbtstudents.*', 'cbtstudents.firstname as student_name')
    //         ->where('subject', $request->subject)
    //         ->where('session', $settings->session)
    //         ->where('term', $settings->term)
    //         ->get();

        $results = StudentQuestion::with(['cbtstudents'=>function($query) use($request){
            $query->where('grade',$request->grade);
        }])
                ->where('subject', $request->subject)
                ->where('session', $settings->session)
                ->where('term', $settings->term)
                ->get();
        
        $results = $results->groupBy('cbtstudents.lastname');

        $results= $results->map(function($val){
            $score = $this->totalCBTMark($val);
            $name = $val->first()->cbtstudents;
            $mark_obtainable = count($val) * 2;
            return ['score'=>$score, 'name'=>$name, 'mark_obtainable'=>$mark_obtainable];
        });

        return response()->json($results);
    }

    public function imageUpload(Request $request){
        $request->validate([
            'upload' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;
    
            $request->file('upload')->move(public_path('images/cbt'), $fileName);
    
            $url = asset('images/cbt/' . $fileName);

            return response()->json(['fileName' => $fileName, 'uploaded'=> 1, 'url' => $url]);
    }
}
