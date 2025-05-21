<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use App\Models\Arms;
use App\Models\Classes;
use App\Models\StudentImage;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Student::factory()
        // ->count(100)
        // ->create();
        $old_students = DB::table('student_details')->get();
        $old_images = DB::table('studentimages')->get();
        //$classes = DB::table('classes')->get();
        //$arms = DB::table('arms')->get();

        foreach($old_students as $old){
            $words = explode(' ',$old->class);
            $last_word = end($words);
            $first_two_words = implode(' ', array_slice($words, 0, 2));
            $arms = Arms::where('arm_name','LIKE','%'.$last_word.'%')->first();
            $grade = Classes::where('class_name','LIKE','%'.$first_two_words.'%')->first();
          
            if($arms){
     
                    $student = Student::create([
                        'surname' => $old->surname,
                        'othernames' => $old->other_names,
                        'fullname' => $old->surname." ".$old->other_names,
                        'dob' => $old->dob,
                        'sex' => $old->sex,
                        'student_id' => $old->student_id,
                        'grade' => $grade['class_name'],
                        'reg_progress' => 50,
                        'class_id' => $grade['id'],
                        'arm' => $arms['arm_name']
                    ]);
               
            }else{
                
                if($grade){
                   $student =  Student::create([
                        'surname' => $old->surname,
                        'othernames' => $old->other_names,
                        'fullname' => $old->surname." ".$old->other_names,
                        'dob' => $old->dob,
                        'sex' => $old->sex,
                        'student_id' => $old->student_id,
                        'grade' => $grade['class_name'],
                        'reg_progress' => 50,
                        'class_id' => $grade['id'],
                    ]);
                }
            }

            //insert the picture
            $img = $old_images->where('student_id', $old->student_id);
          
            if(count($img) > 0){
                $stimg = new StudentImage();
                $stimg->path = $img->first()->imagepath;
                $stimg->student_id = $student->id;
                $stimg->save();
            }
        }

    }
}
