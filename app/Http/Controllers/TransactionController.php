<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Setting;
use App\Models\Fee;
use App\Models\Student;
use App\Models\Transaction;

class TransactionController extends Controller
{
     public function getSettings(){
        $settings = Setting::first();
        return $settings;
    }

    public function makePayment($id){
        $settings = $this->getSettings();
        $student = Student::with(['fees', 'transactions'=>function($query) use($settings){
            $query->where('term', $settings->term)
            ->where('session', $settings->session)
            ->take(20)->latest();
        }])->find($id);
        $fees = $student->fees; 
        $fee_sumary = Fee::where('student_id', $id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->first();
          
            //check if there is change in term and pass on the outstanding fees
            if(is_null($fee_sumary)){
                 $outstanding = Fee::where('student_id', $id)->sum('outstanding');
 
                    $amount = $student->fees()->sum('amount');
                    $outstanding = (is_null($outstanding) || $outstanding == 0)? $amount: $outstanding;
                    $fee = new Fee();
                    $fee->student_id = $id;
                    $fee->total_fee =    $amount ;
                    $fee->outstanding = $outstanding;
                    $fee->credit = 0;
                    $fee->total_paid = 0;
                    $fee->balance = $amount;
                    $fee->term = $settings->term;
                    $fee->session = $settings->session;
                    //$fee->previous_outstanding = $outstanding;
                    $fee->save();

                     $fee_sumary = Fee::where('student_id', $id)
                        ->where('term', $settings->term)
                        ->where('session', $settings->session)
                        ->first();
            }

            //should the school fees change, update the record
            if($student->fees->sum('amount') !== $fee_sumary->total_fee){
                $fee_sumary->update(['total_fee'=>$student->fees->sum('amount')]);

            }
        return inertia('Fee/makePayment', compact('student', 'fees', 'fee_sumary'));
    }

    public function makeTransaction(Request $request){
        DB::beginTransaction();
        try {
             $settings = $this->getSettings();
            $fee = Fee::where('student_id', $request->student_id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->first();
            $amount = !is_null($fee)? $fee->total_fee: 0;
            if(is_null($fee)){
                $student = Student::with(['fees', 'studentGrade'])->find($request->student_id);
                //if the student does not have fees attached to them, return to the add fees page
                if(is_null($student->fees)){
                    return redirect()->to('/add-student-fees/'.$student->studentGrade->section."/".$student->id);
                }

                //$outstanding = Fee::where('student_id', $request->student_id)->sum('outstanding');

                $amount = $student->fees()->sum('amount');
                $fee = new Fee();
                $fee->student_id = $request->student_id;
                $fee->total_fee =    $amount;
                $fee->outstanding = $amount;
                $fee->credit = 0;
                $fee->total_paid = 0;
                $fee->balance = $amount;
                $fee->term = $settings->term;
                $fee->session = $settings->session;
                //$fee->previous_outstanding = $outstanding;
                $fee->save();

                $fee = Fee::where('student_id', $request->student_id)
                    ->where('term', $settings->term)
                    ->where('session', $settings->session)
                    ->first();
            }

            $trans = new Transaction();
            $trans->student_id = $request->student_id;
            $trans->amount = $request->amount;
            $trans->description = $request->description;
            $trans->payment_method = $request->payment_method;
            $trans->term = $settings->term;
            $trans->session = $settings->session;
            $trans->updated_by = auth()->user()->id;
            $trans->save();

            //update receipt number
            $latestId = Transaction::max('id') + 1;
            $receiptNumber = 'RCPT-' . now()->format('Ymd') . '-' . str_pad($latestId, 4, '0', STR_PAD_LEFT);

            //update the fees balance
            if($trans){
                $outstanding = ($fee->total_paid + $request->amount > $fee->total_fee)? 0 : ( $fee->total_fee - ($fee->total_paid + $request->amount));
                $bal = Fee::where('student_id', $request->student_id)->first();
                $bal->outstanding = $outstanding;
                $bal->total_paid = $bal->total_paid + $request->amount;
                $bal->credit = (($fee->total_paid + $request->amount) > $fee->total_fee)? ($fee->total_paid + $request->amount) - $fee->total_fee: 0;
                $bal->save();

                //update the transaction
                $trans->receipt_no = $receiptNumber;
                $trans->save();
            }

            DB::commit();
            return redirect()->back();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back();
        }
       
    }

    public function removeTransactionEntry($id){
        $transaction = Transaction::find($id);
        $settings = $this->getSettings();
        $fee = Fee::where('student_id', $transaction->student_id)
                ->where('term', $settings->term)
                ->where('session', $settings->session)
                ->first();
        if($fee){
            $fee->total_paid = ($fee->total_paid - $transaction->amount);
            $fee->outstanding = (($fee->total_paid - $transaction->amount) < $fee->total_fee)? ($fee->total_fee ) - $fee->total_paid: 0;
            $fee->credit =  ($fee->total_paid  > $fee->total_fee)? $fee->total_paid - $fee->total_fee : 0;
            $fee->save();

            $transaction->delete();
        }
        return response()->json(['success'=>true]);
    }

    //  public function getAllTransactionsForStudent(Request $request){
    //     $transactions = Transaction::where('student_id', $request->student_id)->latest()->paginate(2);
    //     return redirect()->back()->with('transactions', $transactions);
    // }
}
