<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Section;
use App\Models\FeeConfiguration;
use App\Models\Student;
use App\Models\Setting;
use Inertia\Inertia;

class FeeController extends Controller
{
    public function getConfigureFeePage(){
        $classes = Classes::with('Arms')->get();
        $classes = $classes->flatMap(function($class){
           if($class->Arms->isNotEmpty()){
            return $class->Arms->map(function($arm) use($class){
                return [
                    'id' => $class->id,
                    'class_name' => "{$class->class_name} {$arm->arm_name}",
                    'arm'=> $arm->arm_name,
                    'section' => $class->section,
                    'created_at' => $class->created_at,
                    'updated_at' => $class->updated_at
                ];
            });
           }

           return [
                    ['id' => $class->id,
                    'class_name' => "{$class->class_name}",
                    'arm'=> '',
                    'section' => $class->section,
                    'created_at' => $class->created_at,
                    'updated_at' => $class->updated_at]
                ];
        });
        $section = Section::get();
        return inertia('Fee/configureFees', compact('classes', 'section'));
    }

    public function getFeesConfiguration(Request $request)
{
    $grades = $request->grades;
    $class_ids = [];
    $arms = [];

    if(!$grades){
        return [];
    }

    foreach ($grades as $grade) {
        $new_grade = json_decode($grade);
        $class_ids[] = $new_grade->id;

        if (!empty($new_grade->arm)) {
            $arms[] = $new_grade->arm;
        }
    }

    $class_ids = array_unique($class_ids);

    $fees = FeeConfiguration::where('section', $request->section)
        ->whereIn('class_id', $class_ids)
        ->when(!empty($arms), function ($query) use ($arms) {
            $query->whereIn('arm', $arms);
        })
        ->get();

    return response()->json($fees);
}


    public function configureFee(Request $request){
        $request->validate([
            'description' => 'required',
            'amount'=> 'required|numeric',
            //'section' =>'required',
            //'class_id' => 'required',
        ]);

        $grades = $request->grades;
        if($grades){
            foreach($grades as $grade){
             $fee = $request->id? FeeConfiguration::find($request->id): new FeeConfiguration();
                $fee->description = $request->description;
                $fee->amount = $request->amount;
                $fee->section = $request->section;
                $fee->class = $grade['class_name'];
                $fee->class_id = $grade['id'];
                $fee->is_optional = $request->is_optional;
                $fee->arm = $grade['arm']? $grade['arm']: null;
                $fee->save();
            }
        }else{
             $fee = $request->id? FeeConfiguration::find($request->id): new FeeConfiguration();
                $fee->description = $request->description;
                $fee->amount = $request->amount;
                $fee->section = $request->section;
                $fee->class = $request->class_name;
                $fee->class_id = $request->class_id;
                $fee->is_optional = $request->is_optional;
                $fee->arm = $request->arm;
                $fee->save();
        }

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
    public function fees(Request $request){
        $feeStructures = FeeConfiguration::query();
        if($request->search){
            $search = $request->search;
            $feeStructures->where(function($query)use($search){
                $query->where('class', 'LIKE', '%'.$search.'%')
                    ->orWhere('arm', 'LIKE', '%'.$search.'%')
                    ->orWhere('description', 'LIKE', '%'.$search.'%')
                    ->orWhere('amount', 'LIKE', '%'.$search.'%');
            });
        }

        if($request->grade){
            $feeStructures->where('class', $request->grade);
        }

        if(!is_null($request->option)){
            $feeStructures->where('is_optional', $request->option);
        }
        $feeStructures = $feeStructures->orderBy('class', 'ASC')->paginate(20)->withQueryString();
        $classes = Classes::with('Arms')->get();
        $classes = $classes->flatMap(function ($class) {
        if ($class->Arms->isNotEmpty()) {
            return $class->Arms->map(function ($arm) use ($class) {
                return [
                    'id' => $class->id,
                    'section' => $class->section,
                    'class_name' => $class->class_name . ' ' . $arm->arm_name,
                    'arm' => $arm->arm_name,
                ];
            });
        }

        return [[
            'id' => $class->id,
            'section' => $class->section,
            'class_name' => $class->class_name,
            'arm' => null,
        ]];
    });

      if($request->expectsJson()){
            return response()->json($feeStructures);
        }
        return inertia('Fees/fees', compact('feeStructures', 'classes'));
    }

    // public function feeSearch(Request $request){
    //      $feeStructures = FeeConfiguration::query();
    //     if($request->search){
    //         $search = $request->search;
    //         $feeStructures->where(function($query)use($search){
    //             $query->where('class', 'LIKE', '%'.$search.'%')
    //                 ->orWhere('arm', 'LIKE', '%'.$search.'%')
    //                 ->orWhere('description', 'LIKE', '%'.$search.'%')
    //                 ->orWhere('amount', 'LIKE', '%'.$search.'%');
    //         });
    //     }
    //     $feeStructures = $feeStructures->orderBy('class', 'ASC')->paginate(20);
    //     if($request->expectsJson()){
    //         return response()->json($feeStructures);
    //     }
    //     return Inertia::render('Fees/fees', $feeStructures);
    // }

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
                if($fee->total_paid === $fee->total_fee || $fee->total_paid > $fee->total_fee){
                    $fee->status = 'Paid';
                }elseif($fee->total_paid > 0 && $fee->total_paid < $fee->total_fee){
                    $fee->status = "Partial";
                }elseif($fee->outstanding == $fee->total_fee || $fee->total_paid == 0){
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
