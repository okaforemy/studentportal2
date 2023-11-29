<style scoped>
    .report-table tr{
        line-height: 0.8;
        font-size: 15px;
    }

    .result-table{
        line-height: 0.7;
        font-size: 14px;
    }
</style>
<template>
    <div class="col-md-12 py-4">
        <div class="mx-auto col-md-8 no-print">
                <div class="col-md-6">
                    <select name="" id="" class="form-control" @change="getResult" v-model="student_id">
                        <option value="">Select students</option>
                        <option :value="student.id" v-for="(student, index) in students" :key="index">{{ student.surname+" "+student.othernames }}</option>
                    </select>
                </div>
        </div>

        <div class="text-center py-4">
            <img src="/images/PHS.png" alt="purplins">
        </div>

        <div class="text-center py-3">
            <h3>MID-TERM REPORT SHEET</h3>
        </div>

        <div class="col-md-8 mx-auto">
            <table class="table table-sm table-bordered report-table">
                <tr>
                    <td colspan="2">FIRST TERM, MID-TERM REPORT 2023/2024 SESSION</td>
                </tr>
                <tr>
                    <td>NAME:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>AGE:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>SEX:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>ADMISSION NO.</td>
                    <td></td>
                </tr>
                <tr>
                    <td>GRADE:</td>
                    <td></td>
                </tr>
                <tr>
                    <td>TERM CONTINUES ON:</td>
                    <td></td>
                </tr>
            </table>
        </div>

        <div class="pt-4 ">
            <table class="table table-striped col-md-12 table-md-responsive result-table">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>SUBJECTS</th>
                        <th>CA1 (30)</th>
                        <th>GRADE</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(subject, index) in student.secondary_exam" :key="index">
                        <td>{{ index +1 }}</td>
                        <td>{{ subject.subject }}</td>
                        <td>{{ subject.first_ca }}</td>
                        <td>{{ grading(subject.first_ca) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

   export default{
    props:['students', 'section'],

        data(){
            return{
                student_id:'',
                student:[]
            }
        },
        methods:{
            getResult(){
                axios.get('/get-midterm-result', {params:{student_id: this.student_id, section: this.section}})
                .then((response)=>{
                    this.student = response.data
                })
            },
            grading(val){
                let remark = '';
                let total = ((val/30)*100).toFixed(2);
                if(total >= 80){
                    remark = "A1"
                }else if(total <=79.99 && total >=75){
                    remark = "B2"
                }else if(total <= 74.99 && total >= 70){
                    remark = "B3"
                }else if(total <=69.99 && total >=65){
                    remark = "C4"
                }else if(total <=64.99 && total >= 60){
                remark = "C5"
                }else if(total <=59.99 && total >= 55){
                    remark = "C6"
                }else if(total <=54.99 && total >= 50){
                    remark = "D7"
                }else if(total <=49.99 && total >= 45){
                    remark = "E8"
                }else if(total <= 44.99){
                    remark = "F9"
                }

                return remark;
            }
        }
   }
</script>