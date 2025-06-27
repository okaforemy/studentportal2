<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Section;
use App\Models\FeeConfiguration;
use App\Models\Student;
use App\Models\Setting;

class FeeController extends Controller
{
    public function getConfigureFeePage(){
        $classes = Classes::with('Arms')->get();
        $section = Section::get();
        return inertia('Fee/configureFees', compact('classes', 'section'));
    }

    public function getFeesConfiguration(Request $request){
        $fees = FeeConfiguration::where('section', $request->section)
                ->where('class_id', $request->class_id)
                ->when($request->arm, function($query) use($request){
                    $query->where('arm', $request->arm);
                })
                ->get();
        return response()->json($fees);
    }

    public function configureFee(Request $request){
        $request->validate([
            'description' => 'required',
            'amount'=> 'required|numeric',
            'section' =>'required',
            'class_id' => 'required',
        ]);

        $fee = $request->id? FeeConfiguration::find($request->id): new FeeConfiguration();
        $fee->description = $request->description;
        $fee->amount = $request->amount;
        $fee->section = $request->section;
        $fee->class = $request->class_name;
        $fee->class_id = $request->class_id;
        $fee->is_optional = $request->is_optional;
        $fee->arm = $request->arm;
        $fee->save();

        return redirect()->back();
    }

    public function deleteFeeConfiguration($id){
        $fee = FeeConfiguration::find($id);
        if($fee){
            $fee->delete();
        }
        return response()->json(['success'=>true]);
    }

    //new fees 
    public function fees(){
        $feeStructures = FeeConfiguration::orderBy('class', 'ASC')->paginate(5);
        return inertia('Fees/fees', compact('feeStructures'));
    }

    public function getStudentFees(Request $request){
        $settings = Setting::first();
         $classes = Classes::get();
        if($request->class_id){
            $class_id = $request->class_id;
            $class = $classes->where('id', $class_id)->first();
        }else{
            $class = $classes->first();
            $class_id = $class->id;
        }
        
        $students = Student::with(['studentFee' =>function($query) use($settings){
            $query->where('term', $settings->term)->where('session', $settings->session);
        }])->where('class_id', $class_id)->get();

        $students = $students->map(function($student){
            $student->studentFee = $student->studentFee->map(function($fee){
                if($fee->outstanding == 0){
                    $fee->status = 'Paid';
                }elseif($fee->outstanding !==0 && $fee->total_paid !==0  && $fee->outstanding !== $fee->total_fee){
                    $fee->status = "Partial";
                }elseif($fee->total_paid == 0){
                    $fee->status = "Unpaid";
                }else{
                    $fee->status = "Fees not found";
                }

                return $fee;
            });
            return $student;
        });

        $data = [
            'classes' => $classes,
            'class' => $class,
            'students' => $students
        ];
        return response()->json($data);
    }
}
