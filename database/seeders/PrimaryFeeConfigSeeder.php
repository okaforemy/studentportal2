<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FeeConfiguration;
use App\Models\Classes;
use Illuminate\Support\Facades\Log;

class PrimaryFeeConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function isOptional($val){
        if($val == 'TUITION' || $val == 'EDUCATIONAL MATERIALS'){
            return false;
        }
        return true;
    }

    public function run()
    {
        FeeConfiguration::where('section', 'primary')->delete();
        $classes = Classes::with('Arms')->get();
        $fees = [
            'TUITION' => 70000,
            'EDUCATIONAL MATERIALS' => 40000,
            'CAUTION' => 10000,
            'ECA: GIRL GUIDES' => 11000,
            'ECA: BOY SCOUTS' => 11000,
            'ECA: HOME MANAGEMENT' => 9000,
            'ECA: FRENCH CLUB' => 9000,
            'ECA: MUSIC CLUB' => 9000,
            'ECA: JETs CLUB' => 9000,
            'ECA: ARTS WORLD' => 9000,
            'ECA: LITERARY AND DEBATING'=> 9000,
            'PHONETICS' => 5000,
            'KARATE' => 14000,
            'BALLET' => 14000,
            'CHESS' => 7000,
            'LSP' => 5000,
            'MEAL' => 50000,
            'MEDICAL' => 5000,
            'FORM' => 3000,
            'SHIRT' => 15000,
            "BOY'S SHORT" => 15000,
            'CARDIGAN' => 15000,
            'NECK TIE' => 8000,
            'SPORTSWEAR' => 15000,
            'PINAFORE' => 24000,
            "GIRL'S BLOUSE" => 15000,
            'BUS SERVICE: NURSES ESTATE/MTN MAST(TO AND FRO)' => 60000,
            'BUS SERVICE: NURSES ESTATE/MTN MAST(ONE WAY)' => 45000,
            'BUS SERVICE: VINTAGE/DE-ROYAL NOODLES/BECKY 2(TO AND FRO)' => 70000,
            'BUS SERVICE: VINTAGE/DE-ROYAL NOODLES/BECKY 2(ONE WAY)' => 54000,
            'BUS SERVICE: BECKY 1(TO AND FRO)' => 70000,
            'BUS SERVICE: BECKY 1(ONE WAY)' => 52500,
            'BUS SERVICE: ABACHA ROAD/LAPE ESTATE/MASTER K(TO AND FRO)' => 85000,
            'BUS SERVICE: ABACHA ROAD/LAPE ESTATE/MASTER K(ONE WAY)' => 63750,
        ];
        foreach ($classes as $class) {
           if($class->section == 'primary'){
             if($class->Arms && $class->Arms->count() == 0){
                 $feeEntries = [];
               foreach ($fees as $key => $amount) {
                $feeEntries[] = [
                    'section' => 'primary',
                    'class' => $class->class_name,
                    'arm' => null,
                    'class_id' => $class->id,
                    'description' => $key,
                    'amount' => $amount,
                    'is_optional' => $this->isOptional($key),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                
               }

               FeeConfiguration::insert($feeEntries);
            }else{
               if($class->Arms && $class->Arms->count() > 0){
                    foreach($class->Arms as $arm){
                        $feeEntries = [];
                         foreach ($fees as $key => $amount) {
                            $feeEntries[] = [
                                'section' => 'primary',
                                'class' => $class->class_name." ".$arm->arm_name,
                                'arm' => $arm->arm_name,
                                'class_id' => $class->id,
                                'description' => $key,
                                'amount' => $amount,
                                'is_optional' => $this->isOptional($key),
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                         FeeConfiguration::insert($feeEntries);
                    }
               }
            }
           }
        }
    }
}
