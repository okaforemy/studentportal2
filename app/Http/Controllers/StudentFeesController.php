<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\FeeConfiguration;
use App\Models\Transaction;
use App\Models\Fee;
use App\Models\Setting;

class StudentFeesController extends Controller
{
    
    public function addClassFees(Request $request){
        if($request->arm){
            $students = Student::where('class_id', $request->grade)->where('arm', $request->arm)->get();
            $fee_config = FeeConfiguration::where('class_id', $request->grade)->where('arm', $request->arm)->where('is_optional', 0)->pluck('id');
            if($students->count() >  0){
                $students->fees()->sync($fee_config);
            }
        }else{
            $students = Student::where('class_id', $request->grade)->whereNull('arm')->get();
            $fee_config = FeeConfiguration::where('class_id', $request->grade)->whereNull('arm')->where('is_optional', 0)->pluck('id');
            
        }

        if($students && $fee_config){
            foreach($students as $student){
                $student->fees()->sync($fee_config);
            }
        }

        return redirect()->back()->with('success','Success');
    }

    public function getSettings(){
        $settings = Setting::first();
        return $settings;
    }

    public function addStudentFees(Request $request){
        $student = Student::with(['fees'])->find($request->id);
        $studentFeeConfig = $student->fees;
        $feeConfig = FeeConfiguration::where('section', $request->section)->get();
       
        return inertia('Fee/addStudentFees', compact('student', 'studentFeeConfig', 'feeConfig'));
    }

    public function addSingleFee(Request $request){
        $student = Student::find($request->student_id);
        $student->fees()->attach($request->fee_config_id);
        return redirect()->back();
    }

    public function removeSingleFee(Request $request){
        $student = Student::find($request->student_id);
        $student->fees()->detach($request->fee_config_id);
        return redirect()->back();
    }

    public function viewFees(Request $request){
        $student = Student::with(['fees'])->find($request->id);
        $fees = $student->fees;
        $settings = $this->getSettings();
        $transactions = Transaction::where('student_id', $request->id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->paginate(10);
                $fee_sumary = Fee::where('student_id', $student->id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->first();
        return inertia('Fee/viewStudentFees', compact('student', 'fee_sumary', 'fees', 'transactions'));
    }
}
