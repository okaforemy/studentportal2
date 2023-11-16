<?php

namespace Database\Seeders;
use App\Models\Section;

use Illuminate\Database\Seeder;

class sectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['name'=>'pre_nursery'],
            ['name' => 'nursery'],
            ['name'=> 'primary'],
            ['name' =>'junior_secondary'],
            ['name' => 'senior_secondary']
        ];

        Section::insert($data);
    }
}
