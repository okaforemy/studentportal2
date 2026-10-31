<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Subjects;
use App\Models\Classes;
use App\Models\Question;
use Illuminate\Support\Facades\DB;
use App\Models\CBTSetting;
use Session;
use App\Models\CBTStudents;
use Illuminate\Support\Facades\Auth;
use App\Models\Setting;
use App\Models\CBTResult;
use Str;
use Carbon\Carbon;

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
        $data = [];
        $parts = preg_split('/<\/ol>/i', $text);
        $letters = ['a', 'b', 'c', 'd', 'e'];

        foreach ($parts as $part) {
            $listPosition = stripos($part, '<ol');
            if ($listPosition === false) {
                continue;
            }

            $questionMarkup = trim(substr($part, 0, $listPosition));
            $optionsMarkup = substr($part, $listPosition);
            $question = stripos($questionMarkup, '<img') !== false
                ? $questionMarkup
                : trim(strip_tags($questionMarkup));
            preg_match_all('/<li\b[^>]*>(.*?)<\/li>/is', $optionsMarkup, $matches);
            $options = $matches[1] ?? [];
            if ($question === '' || count($options) < 4 || count($options) > 5) {
                continue;
            }

            $answerIndex = null;
            $optionValues = [];
            foreach ($options as $index => $option) {
                if (stripos($option, '<strong') !== false) {
                    if ($answerIndex !== null) {
                        $answerIndex = null;
                        break;
                    }
                    $answerIndex = $index;
                }
                $optionValues[] = html_entity_decode(trim(strip_tags($option)), ENT_QUOTES, 'UTF-8');
            }
            if ($answerIndex === null || !isset($letters[$answerIndex])) {
                continue;
            }

            $data[] = [
                'question' => $question,
                'options' => $optionValues,
                'answer' => 'option_'.$letters[$answerIndex],
            ];
        }

        return $data;
    }
    

    public function saveQuestions(Request $request){ 
        $settings = Setting::firstOrFail();
        $isRichText = $request->boolean('isRichText');
        if ($isRichText) {
            $validated = $request->validate([
                'question' => 'required|string',
                'grade' => 'required|string|exists:classes,class_name',
                'subject' => 'required|integer|exists:subjects,id',
            ]);
            $richText = $this->convertToPhpArray($validated['question']);
            preg_match_all('/<ol\b/i', $validated['question'], $questionLists);
            if (!$richText || count($richText) !== count($questionLists[0])) {
                return redirect()->back()
                    ->withErrors(['question' => 'Each question must have four or five options and exactly one correct option formatted in bold.'])
                    ->withInput();
            }

            $now = now();
            $data = array_map(function ($parsedQuestion) use ($validated, $settings, $now) {
                return [
                    'question' => $parsedQuestion['question'],
                    'answer_to_question' => $parsedQuestion['answer'],
                    'option_a' => $parsedQuestion['options'][0],
                    'option_b' => $parsedQuestion['options'][1],
                    'option_c' => $parsedQuestion['options'][2],
                    'option_d' => $parsedQuestion['options'][3],
                    'option_e' => $parsedQuestion['options'][4] ?? null,
                    'subject_id' => $validated['subject'],
                    'grade' => $validated['grade'],
                    'session' => $settings->session,
                    'term' => $settings->term,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }, $richText);

            DB::transaction(function () use ($data) {
                Question::insert($data);
            });
            session(['grade' => $validated['grade'], 'subject' => $validated['subject']]);
            return redirect()->route('add-question');
        }
        
        $validated = $request->validate([
            'question' => 'required|string',
            'grade' => 'required|string|exists:classes,class_name',
            'subject' => 'required|integer|exists:subjects,id',
            'option_a' => 'required|string|max:255',
            'option_b' => 'required|string|max:255',
            'option_c' => 'required|string|max:255',
            'option_d' => 'required|string|max:255',
            'option_e' => 'nullable|string|max:255',
            'answer' => 'required|in:option_a,option_b,option_c,option_d,option_e',
            'question_id' => 'nullable|integer|exists:questions,id',
        ]);

        if ($validated['answer'] === 'option_e' && empty($validated['option_e'] ?? null)) {
            return redirect()->back()
                ->withErrors(['answer' => 'Choose an answer that has an option.'])
                ->withInput();
        }

        if (!empty($validated['question_id'])) {
            $q = Question::findOrFail($validated['question_id']);
        }else{
            $q = new Question();
        }
        
        $q->grade = $validated['grade'];
        $q->subject_id = $validated['subject'];
        $q->question = $validated['question'];
        $q->option_a = $validated['option_a'];
        $q->option_b = $validated['option_b'];
        $q->option_c = $validated['option_c'];
        $q->option_d = $validated['option_d'];
        $q->option_e = $validated['option_e'] ?? null;
        $q->answer_to_question = $validated['answer'];
        $q->session = $settings->session;
        $q->term = $settings->term;
        $q->save();

        session(['grade' => $validated['grade'], 'subject' => $validated['subject']]);
        if (!empty($validated['question_id'])) {
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
        $validated = $request->validate([
            'firstname' => 'required|string|max:100',
            'lastname' => 'required|string|max:100',
            'grade' => 'required|string|exists:classes,class_name',
        ]);
        $settings = Setting::firstOrFail();

        do {
            $id = Str::upper(Str::random(6));
        } while (CBTStudents::where('student_id', $id)->exists());

        CBTStudents::create([
            'firstname' => $validated['firstname'],
            'lastname' => $validated['lastname'],
            'student_id' => $id,
            'session' => $settings->session,
            'term' => $settings->term,
            'grade' => $validated['grade'],
        ]);

        session(['grade' => $validated['grade']]);
        return redirect()->route('add-cbt-student');
    }

    public function deleteCBTStudent(Request $request){
        $request->validate(['id' => 'required|integer|exists:cbtstudents,id']);
        CBTStudents::whereKey($request->id)->delete();
        return redirect()->route('cbt-students');
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
        $settings = Setting::firstOrFail();
        $questions = Question::where('subject_id', $request->subject)
                    ->where('grade', $request->grade)
                    ->where('term', $settings->term)
                    ->where('session',$settings->session)
                    ->get();
        return response()->json($questions);
    }

    public function deleteQuestion(Request $request){
        $request->validate(['id' => 'required|integer|exists:questions,id']);
        Question::whereKey($request->id)->delete();
        return redirect()->back();
    }

    public function settings(){
        $subjects = Subjects::orderBy('subject','ASC')->get();
        $existingSettings = CBTSetting::all()->keyBy(function ($setting) {
            return $setting->subject_id.'|'.$setting->section;
        });
        $settings = $subjects->map(function ($subject) use ($existingSettings) {
            $setting = $existingSettings->get($subject->id.'|'.$subject->section);
            return [
                'id' => $setting->id ?? null,
                'subject' => $subject->subject,
                'section' => $subject->section,
                'subject_id' => $subject->id,
                'date' => $setting->date ?? null,
                'time' => $setting->time ? substr($setting->time, 0, 5) : '00:00',
                'duration' => $setting->duration ?? null,
                'is_started' => (bool) ($setting->is_started ?? false),
            ];
        });
        return inertia('cbt/settings', compact('subjects','settings'));
    }

    public function saveSettings(Request $request){
        $validated = $request->validate([
            'subject' => 'required|array',
            'subject.*' => 'required|string|max:255',
            'subject_id' => 'required|array',
            'subject_id.*' => 'required|integer|exists:subjects,id',
            'section' => 'required|array',
            'section.*' => 'required|string|max:100',
            'date' => 'required|array',
            'date.*' => 'nullable|date',
            'time' => 'required|array',
            'time.*' => 'nullable|date_format:H:i',
            'duration' => 'required|array',
            'duration.*' => 'nullable|integer|min:1|max:600',
        ]);

        DB::transaction(function () use ($validated) {
            foreach ($validated['subject_id'] as $index => $subjectId) {
                $setting = CBTSetting::firstOrNew([
                    'subject_id' => $subjectId,
                    'section' => $validated['section'][$index],
                ]);
                $setting->subject = $validated['subject'][$index];
                $setting->date = $validated['date'][$index] ?? null;
                $setting->time = $validated['time'][$index] ?? '00:00';
                $setting->duration = $validated['duration'][$index] ?? null;
                $setting->is_started = $setting->exists ? $setting->is_started : false;
                $setting->save();
            }
        });

        return redirect()->back();
    }

    public function startExam(Request $request){
        $validated = $request->validate([
            'id' => 'required|integer|exists:cbt_settings,id',
            'is_started' => 'required|boolean',
        ]);
        $setting = CBTSetting::findOrFail($validated['id']);
        if ($validated['is_started'] && (!$setting->date || !$setting->duration)) {
            return redirect()->back()->withErrors([
                'settings' => 'Set an exam date and duration before starting it.',
            ]);
        }

        $setting->is_started = $validated['is_started'];
        $setting->save();

        return redirect()->back();
    }

    private function isExamAvailable(CBTSetting $setting)
    {
        if (!$setting->is_started || !$setting->date || !$setting->duration) {
            return false;
        }

        $scheduledAt = Carbon::parse($setting->date.' '.($setting->time ?: '00:00'));
        return now()->greaterThanOrEqualTo($scheduledAt);
    }

    public function CBTLogin(){
        return view('cbt-login');
    }

    public function CBTHome(){
        $student = Auth::guard('cbt')->user();
        $class = Classes::where('class_name', $student->grade)->first();
        $schoolSettings = Setting::firstOrFail();
        $subjects = $class
            ? CBTSetting::where('is_started', 1)
                ->where('section', $class->section)
                ->whereNotIn('subject_id', CBTResult::select('subject_id')
                    ->where('student_id', $student->student_id)
                    ->where('term', $schoolSettings->term)
                    ->where('session', $schoolSettings->session))
                ->orderBy('subject')
                ->get()
                ->filter(function ($setting) {
                    return $this->isExamAvailable($setting);
                })
                ->values()
            : collect();
        $fullname = $student->lastname." ".$student->firstname;
        $error = session('error');
        return inertia('cbt/exam-home', compact('subjects', 'fullname', 'error'));
    }

    public function CBTLoginValidate(Request $request){
        $validated = $request->validate([
            'student_id' => 'required|string|max:20',
        ]);
        $user = CBTStudents::where('student_id', $validated['student_id'])->first();

        if($user){
            if(Auth::guard('cbt')->loginUsingId($user->id)){
                $request->session()->regenerate();
                $deviceToken = Str::random(32);
                $user->update(['device_token' => $deviceToken]);
                $request->session()->put('device_token', $deviceToken);

                return redirect()->route('exam-home');
            }
        }

        return redirect()->back()->with(['error'=>'Wrong ID']);
    }

    public function exam(){
        $student = Auth::guard('cbt')->user();
        $settings = Setting::firstOrFail();
        $subjectId = request()->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
        ])['subject_id'];
        $examSetting = CBTSetting::where('subject_id', $subjectId)
            ->where('section', Classes::where('class_name', $student->grade)->value('section'))
            ->where('is_started', 1)
            ->first();
        if (!$examSetting || !$this->isExamAvailable($examSetting)) {
            return redirect()->route('exam-home')->with('error', 'This exam is not currently available.');
        }

        $questionQuery = DB::table('student_questions')
            ->where('student_id', $student->student_id)
            ->where('subject', $subjectId)
            ->where('session', $settings->session)
            ->where('term', $settings->term);
        $questions = $questionQuery
            ->select('id', 'question', 'option_a', 'option_b', 'option_c', 'option_d', 'option_e', 'your_answer')
            ->orderBy('id')
            ->get();

        if ($questions->isEmpty()) {
            return redirect()->route('exam-home')->with('error', 'Start this exam from the subject list first.');
        }

        $remainingSeconds = $this->remainingExamSeconds(
            $student->student_id,
            $subjectId,
            $examSetting,
            $settings->session,
            $settings->term
        );
        if ($remainingSeconds === 0) {
            [$score, $maximumScore] = $this->recordExamResult($student, $subjectId, $settings);
            $this->LogOut();
            return redirect()->route('cbt-login')->with(
                'success',
                "Exam time expired. Your score is {$score}/{$maximumScore}."
            );
        }

        $fullname = $student->lastname." ".$student->firstname;
        $student_id = $student->student_id;
        $subjectId = (int) $subjectId;
        return inertia('cbt/exam', compact('fullname', 'student_id', 'subjectId', 'questions', 'remainingSeconds'));
    }

    public function prepareQuestion(Request $request){
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'section' => 'required|string|max:100',
        ]);
        $student = Auth::guard('cbt')->user();
        $classSection = Classes::where('class_name', $student->grade)->value('section');
        if ($classSection !== $validated['section']) {
            return redirect()->route('exam-home')->with('error', 'The selected subject is not available for your class.');
        }
        $subject = Subjects::where('subject', $validated['subject'])
            ->where('section', $classSection)
            ->first();
        if (!$subject) {
            return redirect()->route('exam-home')->with('error', 'The selected subject is unavailable.');
        }

        $settings = Setting::firstOrFail();
        $examSetting = CBTSetting::where('subject_id', $subject->id)
            ->where('section', $validated['section'])
            ->where('is_started', 1)
            ->first();
        if (!$examSetting || !$this->isExamAvailable($examSetting)) {
            return redirect()->route('exam-home')->with('error', 'This exam is not currently available.');
        }

        $alreadySubmitted = CBTResult::where('subject_id', $subject->id)
            ->where('student_id', $student->student_id)
            ->where('term', $settings->term)
            ->where('session', $settings->session)
            ->exists();
        if ($alreadySubmitted) {
            return redirect()->route('exam-home')->with('error', 'You have already submitted this exam.');
        }

        $attempt = DB::table('student_questions')
            ->where('student_id', $student->student_id)
            ->where('subject', $subject->id)
            ->where('session', $settings->session)
            ->where('term', $settings->term)
            ->exists();

        if (!$attempt) {
            $questions = Question::where('grade', $student->grade)
                ->where('subject_id', $subject->id)
                ->where('session', $settings->session)
                ->where('term', $settings->term)
                ->get()
                ->shuffle();
            if ($questions->isEmpty()) {
                return redirect()->route('exam-home')->with('error', 'No questions are available for this exam.');
            }

            $now = now();
            $data = $questions->map(function ($question) use ($student, $subject, $settings, $now) {
                return [
                    'subject' => $subject->id,
                    'question' => $question->question,
                    'option_a' => $question->option_a,
                    'option_b' => $question->option_b,
                    'option_c' => $question->option_c,
                    'option_d' => $question->option_d,
                    'option_e' => $question->option_e,
                    'correct_answer' => $question->answer_to_question,
                    'student_id' => $student->student_id,
                    'session' => $settings->session,
                    'term' => $settings->term,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            })->all();

            DB::transaction(function () use ($student, $subject, $settings, $data) {
                CBTStudents::whereKey($student->id)->lockForUpdate()->firstOrFail();
                $attemptExists = DB::table('student_questions')
                    ->where('student_id', $student->student_id)
                    ->where('subject', $subject->id)
                    ->where('session', $settings->session)
                    ->where('term', $settings->term)
                    ->exists();
                if (!$attemptExists) {
                    DB::table('student_questions')->insert($data);
                }
            });
        }

        session([
            'student_id' => $student->student_id,
            'subject' => $subject->id,
            'grade' => $student->grade,
            'fullname' => $student->firstname." ".$student->lastname,
        ]);

        return redirect()->route('exam', ['subject_id' => $subject->id]);
    }

    public function answerQuestion(Request $request){
        $validated = $request->validate([
            'question_id' => 'required|integer',
            'subject_id' => 'required|integer|exists:subjects,id',
            'your_answer' => 'nullable|string|in:option_a,option_b,option_c,option_d,option_e',
        ]);
        $student = Auth::guard('cbt')->user();
        $settings = Setting::firstOrFail();
        $question = DB::table('student_questions')
            ->where('id', $validated['question_id'])
            ->where('student_id', $student->student_id)
            ->where('subject', $validated['subject_id'])
            ->where('session', $settings->session)
            ->where('term', $settings->term)
            ->first();
        if (!$question) {
            abort(404, 'Exam question not found.');
        }

        $examSetting = CBTSetting::where('subject_id', $question->subject)
            ->where('section', Classes::where('class_name', $student->grade)->value('section'))
            ->firstOrFail();
        if (!$this->isExamAvailable($examSetting)) {
            return response()->json(['message' => 'This exam is no longer available.'], 409);
        }
        if ($this->remainingExamSeconds(
            $student->student_id,
            $question->subject,
            $examSetting,
            $settings->session,
            $settings->term
        ) === 0) {
            return response()->json(['message' => 'Exam time has expired.'], 409);
        }

        if ($validated['your_answer'] !== null && empty($question->{$validated['your_answer']})) {
            return response()->json(['message' => 'That answer choice is not available.'], 422);
        }

        DB::table('student_questions')
            ->where('id', $question->id)
            ->update(['your_answer' => $validated['your_answer'], 'updated_at' => now()]);

        return response()->json(['saved' => true]);
    }

    private function remainingExamSeconds($studentId, $subjectId, CBTSetting $examSetting, $session, $term)
    {
        $startedAt = DB::table('student_questions')
            ->where('student_id', $studentId)
            ->where('subject', $subjectId)
            ->where('session', $session)
            ->where('term', $term)
            ->min('created_at');

        if (!$startedAt) {
            return 0;
        }

        $elapsedSeconds = Carbon::parse($startedAt)->diffInSeconds(now());
        return max(0, ((int) $examSetting->duration * 60) - $elapsedSeconds);
    }

    public function totalCBTMark($result){
        return collect($result)->filter(function ($question) {
            return $question->correct_answer !== null
                && $question->correct_answer === $question->your_answer;
        })->count() * 2;
    }

    public function result(Request $request){ 
        $student = Auth::guard('cbt')->user();
        $settings = Setting::firstOrFail();
        $subjectId = $request->validate([
            'subject_id' => 'required|integer|exists:subjects,id',
        ])['subject_id'];

        [$score, $maximumScore] = $this->recordExamResult($student, $subjectId, $settings);
        $this->LogOut();
        return redirect()->route('cbt-login')->with(
            'success',
            "Exam submitted successfully. Your score is {$score}/{$maximumScore}."
        );
    }

    private function recordExamResult(CBTStudents $student, $subjectId, Setting $settings)
    {
        $result = DB::table('student_questions')
            ->where('student_id', $student->student_id)
            ->where('subject', $subjectId)
            ->where('session', $settings->session)
            ->where('term', $settings->term)
            ->get();
        if ($result->isEmpty()) {
            abort(404, 'No active exam attempt was found.');
        }

        $score = $this->totalCBTMark($result);
        $maximumScore = $result->count() * 2;
        DB::transaction(function () use ($result, $student, $subjectId, $settings, $score) {
            CBTStudents::whereKey($student->id)->lockForUpdate()->firstOrFail();
            CBTResult::firstOrCreate(
                [
                    'student_id' => $student->student_id,
                    'subject_id' => $subjectId,
                    'term' => $settings->term,
                    'session' => $settings->session,
                ],
                [
                    'fullname' => $student->lastname." ".$student->firstname,
                    'marks_obtainable' => $result->count() * 2,
                    'marks_obtained' => $score,
                    'grade' => $student->grade,
                ]
            );
        });

        return [$score, $maximumScore];
    }

    public function LogOut(){
        $student = Auth::guard('cbt')->user();
        if ($student) {
            $student->update(['device_token' => null]);
        }
        Auth::guard('cbt')->logout();
        session()->forget(['device_token', 'grade', 'subject', 'student_id', 'fullname']);
    }

    public function logOutStudent(){
        $this->LogOut();
        return redirect()->route('cbt-login');
    }

    public function getUploadQuestion(){
        return inertia('cbt/upload');
    }

    public function CBTResults(Request $request){
        $settings = Setting::firstOrFail();
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
        $grades = Classes::where('section','junior_secondary')->orWhere('section','senior_secondary')->orWhere('section', 'primary')->get();
        return inertia('cbt/results',compact('grades'));
    }

    public function getStudentsInAClass(Request $request){
        $students = CBTStudents::where('grade', $request->grade)->get();
        return response()->json($students);
    }
    
    public function StudentResult(Request $request){
        $settings = Setting::firstOrFail();
        $result = CBTResult::with('subject')->where('student_id', $request->student_id)->where('term',$settings->term)->where('session', $settings->session)->get();
        return response()->json($result);
    }

    public function CBTResultsBySubjects(Request $request){
        $validated = $request->validate([
            'subject' => 'required|integer|exists:subjects,id',
            'grade' => 'required|string|exists:classes,class_name',
        ]);
        $settings = Setting::firstOrFail();

        $studentIds = CBTStudents::where('grade', $validated['grade'])->pluck('student_id');
        $results = CBTResult::whereIn('student_id', $studentIds)
            ->where('subject_id', $validated['subject'])
            ->where('session', $settings->session)
            ->where('term', $settings->term)
            ->get()
            ->map(function ($result) {
                return [
                    'score' => $result->marks_obtained,
                    'name' => $result->fullname,
                    'mark_obtainable' => $result->marks_obtainable,
                ];
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
