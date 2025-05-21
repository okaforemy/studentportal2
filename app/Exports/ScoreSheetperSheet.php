<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Protection;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class ScoreSheetperSheet implements FromCollection, WithHeadings, WithTitle, WithEvents, WithColumnWidths, WithStyles
{
    /**
    * @return \Illuminate\Support\Collection
    */

    protected $subject;
    protected $students;
    protected $max_ca;
    protected $max_exam;

    public function __construct($subject, $students, $max_ca, $max_exam)
    {
        $this->subject = $subject;
        $this->students = $students;
        $this->max_ca = $max_ca;
        $this->max_exam = $max_exam;
    }

    public function collection()
    {
         return $this->students->map(function ($student) {
            //$score = optional($student->subjects->firstWhere('id', $this->subject->id)->pivot->score);
            if($student->primaryExam->isNotEmpty()){
                $score = $student->primaryExam->where('subject', $this->subject->subject)->first();
            }else if($student->secondaryExam->isNotEmpty()){
                $score = $student->secondaryExam->where('subject', $this->subject->subject)->first();
            }else if($student->seniorSecondaryExam->isNotEmpty()){
                $score = $student->seniorSecondaryExam->where('subject', $this->subject->subject)->first();
            }
            $first_ca = $score? $score->first_ca:'';
            $second_ca = $score? $score->second_ca:'';
            $exam = $score? $score->exam:'';
           
            return [
                'STUDENT' => $student->fullname,
                '1ST CA' => $first_ca,
                '2ND CA' => $second_ca,
                'EXAM' => $exam,
                'TOTAL' =>'',
                'STUDENT ID' => $student->student_id,
                'ID' =>$student->id
            ];
        });
    }

    public function headings(): array
    {
        return ['STUDENTS', '1ST CA', '2ND CA', 'EXAM', 'TOTAL','STUDENT ID','ID'];
    }

    public function title(): string
    {
        return $this->subject->subject;
    }

    public function styles(Worksheet $sheet)
    {
        //$sheet->getStyle('A1:Z1000')->getFont()->setSize(12);
        $lastRow = $sheet->getHighestRow(); // gets the last used row
        $sheet->getStyle("A1:D{$lastRow}")->getFont()->setSize(14);

        $sheet->getRowDimension(1)->setRowHeight(26); // header row

        // Set row height for content rows (2 to last)
        for ($i = 2; $i <= $lastRow; $i++) {
            $sheet->getRowDimension($i)->setRowHeight(22);
        }

        return [
            // Style for the heading row (row 1)
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'uppercase' => true, // Note: Excel doesn't have "uppercase", so do it in PHP
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        
        return [
            AfterSheet::class => function(AfterSheet $event) {
              

                // $sheet = $event->sheet->getDelegate();
                // $lastRow = $sheet->getHighestRow();

                // // Unlock all cells first
                // $sheet->getStyle("A1:D{$lastRow}")
                //       ->getProtection()
                //       ->setLocked(Protection::PROTECTION_UNPROTECTED);

                // // Lock only the student_id column (column D)
                // $sheet->getStyle("D1:D{$lastRow}")
                //       ->getProtection()
                //       ->setLocked(Protection::PROTECTION_PROTECTED);

                // // Enable protection (this applies the locking)
                // $sheet->getProtection()->setSheet(true);
                 $sheet = $event->sheet->getDelegate();
                $rows = $sheet->getHighestRow();

                for ($i = 2; $i <= $rows; $i++) {
                    $sheet->setCellValue("E{$i}", "=B{$i}+C{$i}+D{$i}");
                }

                // Set validation for 1ST CA (C), 2ND CA (D) → Max 20
                foreach (['B', 'C'] as $col) {
                    for ($i = 2; $i <= $rows; $i++) {
                        $cell = $col . $i;

                        $validation = $sheet->getCell($cell)->getDataValidation();
                        $validation->setType(DataValidation::TYPE_WHOLE);
                        $validation->setErrorStyle(DataValidation::STYLE_STOP);
                        $validation->setAllowBlank(false);
                        $validation->setShowInputMessage(true);
                        $validation->setShowErrorMessage(true);
                        $validation->setFormula1(0);
                        $validation->setFormula2($this->max_ca);
                        $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
                        $validation->setPromptTitle('CA Limit');
                        $validation->setPrompt('Enter a number between 0 and '.$this->max_ca);
                        $validation->setErrorTitle('Invalid CA');
                        $validation->setError('1ST and 2ND CA must be between 0 and '.$this->max_ca);
                    }
                }

                // Set validation for EXAM (E) → Max 60
                for ($i = 2; $i <= $rows; $i++) {
                    $cell = 'D' . $i;

                    $validation = $sheet->getCell($cell)->getDataValidation();
                    $validation->setType(DataValidation::TYPE_WHOLE);
                    $validation->setErrorStyle(DataValidation::STYLE_STOP);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setFormula1(0);
                    $validation->setFormula2($this->max_exam);
                    $validation->setOperator(DataValidation::OPERATOR_BETWEEN);
                    $validation->setPromptTitle('Exam Limit');
                    $validation->setPrompt('Enter a number between 0 and '.$this->max_exam);
                    $validation->setErrorTitle('Invalid Exam Score');
                    $validation->setError('Exam score must be between 0 and '.$this->max_exam);
                }
            }
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 10,
            'C' => 10,
            'D' => 10,
            'E' => 10,
            'F' => 30,
        ];
    }

}
