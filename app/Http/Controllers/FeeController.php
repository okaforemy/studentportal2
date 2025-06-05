<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Classes;
use App\Models\Section;
use App\Models\FeeConfiguration;

class FeeController extends Controller
{
    public function getConfigureFeePage(){
        $classes = Classes::with('Arms')->get();
        $section = Section::get();
        return inertia('Fee/configureFees', compact('classes', 'section'));
    }

    public function getFeesConfiguration(Request $request){
        $fees = FeeConfiguration::where('section', $request->section)->where('class_id', $request->class_id)->get();
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
        $fee->class = $request->class;
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
}
