<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\User;
use Hash;

class HomeController extends Controller
{
    public function home(){
       // sleep('60');
        return inertia('home');
    }

    public function Settings(){
        $settings = Setting::first();
        return inertia('general/settings', compact('settings'));
    }

    public function saveSettings(Request $request){
        $request->validate([
            'session'=>'required',
            'term'=>'required',
            'term_begin'=>'required',
            'term_end'=>'required',
            'mid_term_end'=>'required',
            'resumption'=>'required',
            'mid_term_resumption' => 'required',
        ]);

        $settings = Setting::first();
        $setting = $settings? $settings: new Setting();
        $setting->session = $request->session;
        $setting->term = $request->term;
        $setting->term_begin = $request->term_begin;
        $setting->term_end = $request->term_end;
        $setting->mid_term_end = $request->mid_term_end;
        $setting->resumption = $request->resumption;
        $setting->mid_term_resumption = $request->mid_term_resumption;
        $setting->save();
        
        return redirect()->back()->with('success',true);
    }

    public function register(){
        return view('addUser');
    }

    public function addUser(Request $request){
        $request->validate([
            'fullname'=>'required',
            'email' => 'required|email|unique:users'
        ]);

        $user  = new User();
        $user->name = $request->fullname;
        $user->email = $request->email;
        $user->password = Hash::make('cbtphs2023');
        $user->save();
        return redirect()->back()->with('success','saved successfully');
    }
}
