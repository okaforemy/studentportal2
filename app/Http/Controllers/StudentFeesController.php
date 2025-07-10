<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Student;
use App\Models\FeeConfiguration;
use App\Models\Transaction;
use App\Models\Fee;
use App\Models\Setting;
use App\Models\Classes;

class StudentFeesController extends Controller
{
    
    public function addClassFees(Request $request){
        if($request->arm){
            $students = Student::where('class_id', $request->grade)->where('arm', $request->arm)->get();
            $fee_config = FeeConfiguration::where('class_id', $request->grade)->where('arm', $request->arm)->where('is_optional', 0)->pluck('id');
            // if($students->count() >  0){
            //     $students->fees()->sync($fee_config);
            // }
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
        $section = $request->section;
        if($request->section =='section'){
            $class = Classes::find($student->class_id);
            $section = $class->section;
        }
        $feeConfig = FeeConfiguration::where('section', $section)
            ->where('class_id', $student->class_id)
            ->when($request->arm, function($query) use($request){
                $query->where('arm', $request->arm);
            })
            ->get();
        return inertia('Fee/addStudentFees', compact('student', 'studentFeeConfig', 'feeConfig'));
    }

    //this adds single fee config to student
    public function addSingleFee(Request $request){
        $student = Student::find($request->student_id);
        $student->fees()->attach($request->fee_config_id);
        return redirect()->back();
    }

    //removes fee configuration from students
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

        $amount = $student->fees()->sum('amount');

        if(is_null($fee_sumary)){
             
             $outstanding = Fee::where('student_id', $student->id)->sum('outstanding');
              $outstanding = (is_null($outstanding) || $outstanding == 0)? $amount: $outstanding;
                $fee = new Fee();
                $fee->student_id = $student->id;
                $fee->total_fee =    $amount;
                $fee->outstanding = $outstanding;
                $fee->credit = 0;
                $fee->total_paid = 0;
                //$fee->balance = $amount;
                $fee->term = $settings->term;
                $fee->session = $settings->session;
                $fee->save();

            $fee_sumary = Fee::where('student_id', $student->id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->first();
        }
       //should the school fees change, update the record
            // if($amount !== $fee_sumary->total_fee){
            //     $fee_sumary->update(['total_fee'=>$amount]);

            //     $fee_sumary = Fee::where('student_id', $request->id)
            //         ->where('term', $settings->term)
            //         ->where('session', $settings->session)
            //         ->first();
            // }
             $total_discount = $student->fees->sum('pivot.discount');
            $outstanding = $amount - $fee_sumary->total_paid - $total_discount;
            $credit = $outstanding < 0? -$outstanding: 0;
            $outstanding = $outstanding < 0 ? 0: $outstanding;
            if($amount !== $fee_sumary->total_fee){
                $total_fee = $amount;
                $fee_sumary->update([
                    'discount'=> $total_discount, 
                    'total_fee'=> ($fee_sumary->balance - $total_discount),
                    'outstanding' => $outstanding,
                    'credit' => $credit
                ]);
                  $is_update = true;
            }

            $is_update = false;
            if($total_discount !== $fee_sumary->discount){
                $fee_sumary->update([
                    'discount'=> $total_discount, 
                    'total_fee'=> ($fee_sumary->balance - $total_discount),
                    'outstanding' => $outstanding,
                    'credit' => $credit
                ]);
                $is_update =true;
            }

             if($is_update){
                $fee_sumary = Fee::where('student_id', $request->id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->first();
            }
        return inertia('Fee/viewStudentFees', compact('student', 'fee_sumary', 'fees', 'transactions'));
    }

    public function addDiscount(Request $request){
       if($request->discount !==''){
         DB::table('student_fees')
            ->where('student_id', $request->student_id)
            ->where('fee_configuration_id', $request->fee_configuration_id)
            ->update(['discount'=> $request->discount]);

        //edit the fee
        // $settings= $this->getSettings();
        // $fees = Fee::where('student_id', $request->student_id)
        //         ->where('term', $settings->term)
        //         ->where('session', $settings->session)
        //         ->first();
        // if($fees){
        //     $fees->discount = $request->discount;
        //     $fees->total_fee = ($fees->total_fee - $request->discount);
        //     $fees->save();
        // }
       }
       return redirect()->back();
    }

    public function studentFeesAnalytics(){
        $settings = $this->getSettings();
        $classes = Classes::with(['Arms', 'fees'=>function($query)use ($settings){
            $query->where('term', $settings->term)->where('session', $settings->session);
        }, 'students'])->get();

        $classesCombination = $classes->flatMap(function($class){
            if($class->Arms->isNotEmpty()){
              
                return $class->Arms->map(function($arm) use ($class){
                    return [
                        'id' => $class->id,
                        'name' => "{$class->class_name} {$arm->arm_name}",
                        'students' =>$class->students->where('arm', $arm->arm_name)->values(),
                        'fees' => $class->fees->whereIn('student_id', $class->students->where('arm', $arm->arm_name)->pluck('id'))
                    ];
                });
            }

            return [
                       [ 'id' => $class->id,
                        'name' => "{$class->class_name}",
                        'students' =>$class->students->where('arm', null)->values(),
                        'fees' => $class->fees->whereIn('student_id', $class->students->where('arm', null)->pluck('id'))]
                    ];

        });

        $bar_data = [];
        $bar_label = $classesCombination->pluck('name');
        $total_fee = 0;
        $total_paid = 0;
        $outstanding = 0;
        $credit = 0;
       
        foreach($classesCombination as $class){
            array_push($bar_data, $class['fees']->sum('total_paid'));
            $total_fee +=  $class['fees']->sum('total_fee');
            $total_paid +=  $class['fees']->sum('total_paid');
            $outstanding +=  $class['fees']->sum('outstanding');
            $credit +=  $class['fees']->sum('credit');
        }
        return response()->json([
            'bar_label'=>$bar_label, 
            'bar_data'=>$bar_data,
            'total_fee' => $total_fee,
            'total_paid' => $total_paid,
            'outstanding' =>$outstanding,
            'credit' => $credit
        ]);
        
    }
}
