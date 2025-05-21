<style scoped>
    .report-table tr{
        line-height: 0.8;
        font-size: 15px;
    }

    .result-table{
        line-height: 0.7;
        font-size: 14px;
    }
    .f2{
        font-size: 12px;
    }
    .remarks {
        border: 1px solid #dee2e6;
    }
    .remarks p {
        margin-left: 8px;
        text-decoration: underline;
        font-weight: bold;
        font-size: 14px;
        margin-top: 5px;
    }
    p.htremarks[data-v-72410b27] {
        text-decoration: none;
        font-size: 14px;
        font-style: italic;
        font-weight: normal;
        margin-left: 5px;
        margin-right: 5px;
        padding-left: 10px;
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
            <img src="/images/PHS.png"  alt="purplins">
        </div>

        <div class="text-center py-3">
            <h3 class=" font-weight-bold">MID-TERM REPORT SHEET</h3>
        </div>

        <div class="col-md-8 mx-auto">
            <table class="table table-sm table-bordered report-table">
                <tr>
                    <td colspan="2" class="text-center font-weight-bold text-capitalize" v-if="student && student.settings">{{(student.settings.term).split('_').join(" ").toUpperCase()}}, MID-TERM REPORT {{student.settings.session}} SESSION</td>
                </tr>
                <tr>
                    <td>NAME:</td>
                    <td>{{ student.fullname }}</td>
                    <td rowspan="6" v-if="picture">
                        <img :src="picture.path" alt="student's picture" id="student_img" style="height: 140px;">
                    </td>
                </tr>
                <tr>
                    <td>AGE:</td>
                    <td>{{ calculateAge() }} years</td>
                </tr>
                <tr>
                    <td>SEX:</td>
                    <td>{{ student.sex }}</td>
                </tr>
                <tr>
                    <td>ADMISSION NO.</td>
                    <td>{{ student.student_id }}</td>
                </tr>
                <tr>
                    <td>GRADE:</td>
                    <td>{{ student.grade }}</td>
                </tr>
                <tr>
                    <td>TERM CONTINUES ON:</td>
                    <td>{{ getResumptionDate() }}</td>
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
                        <td class=" text-uppercase">{{ subject.subject }}</td>
                        <td class="font-12">{{ subject.first_ca }}</td>
                        <td>{{ grading(subject.first_ca) }}</td>
                    </tr>
                </tbody>
            </table>

            <h4 class="text-center">Average: <span class="pl-2" v-if="student.class_avg">{{ (student.class_avg).toFixed(2) }}</span></h4>
        </div>

        <div class="row">
                <div class="col-md-12">
                    <div class="remarks report-table-hide">
                        <div class="row">
                            <div class="col-md-6" v-if="student.remarks && student.remarks.length > 0">
                                <p>Principal's Remark:</p><p class="principal htremarks" style="text-decoration: none">{{ student.remarks[0].HT_remarks }}</p>
                                <p>Principal's Signature:</p>
                            </div>
                            <div class="col-md-6" v-if="student.remarks && student.remarks.length > 0">
                                <p>Form Teacher's Remark:</p><p class="form_teacher htremarks" style="text-decoration: none">{{ student.remarks[0].CT_remarks }}</p>
                                <p>Form Teacher's Signature:</p>
                            </div>
                        </div>
                    </div>
            </div>
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
                student:[],
                picture: []
            }
        },
        methods:{
            getResult(e){
                if(e.target.value !==''){
                    axios.get('/get-midterm-result', {params:{student_id: this.student_id, section: this.section}})
                    .then((response)=>{
                        this.student = response.data
                        this.picture = response.data.picture
                    })
                }
                
            },
            getResumptionDate(){
                if(this.student && this.student.settings && this.student.settings.mid_term_resumption){
                    const date = new Date(this.student.settings.mid_term_resumption);
                    const formattedDate = new Intl.DateTimeFormat("en-US", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    }).format(date);

                    return formattedDate;
                }
               
            },

             calculateAge() {
                if(this.student && this.student.settings && this.student.dob){
                    const birth = new Date(this.student.dob);
                    const today = new Date();
                    
                    let age = today.getFullYear() - birth.getFullYear();
                    const monthDiff = today.getMonth() - birth.getMonth();
                    const dayDiff = today.getDate() - birth.getDate();

                    // Adjust age if the birthday hasn't occurred yet this year
                    if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                        age--;
                    }

                    return age;
                }
                
            }, 
            getActualResult(result){
                let val = (result/30) * 100
                return val.toFixed(1)
            },
            grading(val){
               
                let remark = '';
                let total = ((val/30)*100).toFixed(1);
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