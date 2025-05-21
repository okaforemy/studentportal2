<?php

namespace App\Exports;

use App\Models\Classes;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Models\Student;
use App\Models\Subjects;

class ScoreSheetExport implements WithMultipleSheets
{
    /**
    * @return \Illuminate\Support\Collection
    */

    use Exportable;

    private $class;
    private $students;
    private $max_ca;
    private $max_exam;

     public function __construct($class, $students, $max_ca, $max_exam)
    {
        $this->class = $class;
        $this->students = $students;
        $this->max_ca = $max_ca;
        $this->max_exam = $max_exam;
    }

     /**
     * @return array
     */
    public function sheets(): array
    {
      
        
       // $subjects = Subjects::whereIn('id', $subjectIds)->get();

       if(isset($this->class->subjects) && count($this->class->subjects) > 0){
        $subjects = $this->class->subjects;
         return $subjects->map(function ($subject) {
            return new ScoreSheetperSheet($subject, $this->students, $this->max_ca, $this->max_exam);
        })->toArray();
       }
    }
}
