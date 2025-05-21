<style scoped>
    .page-item:nth-child(1) {
        display: none;
    }
    .page-item:last-child{
        display: none;
    }

    @media print {
        .no-print {
            display: none;
        }
    }

</style>
<template>
    <div class="col-md-12">
        <div class="mx-auto col-md-5 py-3 no-print">
            <select class="form-control" @change="getResult" ref="student_id">
                <option value="">select pupil</option>
                <option :value="student.id" v-for="(student, ind) in students" :key="ind">{{ student.surname+" "+student.othernames }}</option>
            </select>
        </div>
        <div>
            <div class="col-md-12 text-center mt-4">
                <img src="/images/midterm.png" width="50%" alt="" class="result_logo">
            </div>
        </div>
        <div class="text-center my-4"  v-if="picture">
            <img :src="picture.path" style="height: 140px;" alt="student" class="img-thumbnail">
        </div>
        <div>
            <p class="text-center" v-if="result && result.settings">Holiday Assessment/Midterm Progress Report for <span class="term">{{ (result.settings.term).split("_").join(" ") }},</span> <span class="session">{{result.settings.session  }}</span> Session.</p>
            <p class="text-center">
                <span class=" font-weight-bold"> PUPIL'S NAME:</span> 
                <span class="std_name mr-5">{{ result.length !=0 ? result.surname+" "+result.othernames:'' }}</span>
                <!-- <span class=" font-weight-bold">	DATE:</span> 
                <span class="date" style="text-transform: uppercase;">May 11, 2023, 12:56 pm</span> -->
            </p>
            <p class="text-center">
                <span class=" font-weight-bold">CLASS:</span> 
                <span class="clas mr-5">{{result.grade}}</span>
                <span class=" font-weight-bold">	SCHOOL RESUMES:</span> 
                <span class="resume">{{getResumptionDate()}}</span>
            </p>

            <table class="table table-bordered table-sm primary2">
			<thead>
				<tr>
					<th>SUBJECTS</th>
					<th>MARKS OBTAINABLE</th>
					<th>MARKS OBTAINED</th>
				</tr>
			</thead>
			<tbody class="midterm-td">
                <tr v-for="(primary, ind) in result.primary_exam" :key="ind">
                    <td style="text-transform: uppercase;">{{primary.subject}}</td> 
                    <td class="text-center">100</td>
                    <td class="text-center">{{((primary.first_ca /20) * 100).toFixed(1)}}</td>
                </tr>
                <tr>
                        <td class="text-center font-weight-bold">CLASS PERCENTAGE</td>
                        <td></td>
                        <td class="text-center font-weight-bold" v-if="result && result.class_avg">{{ ((result.class_avg/20)*100).toFixed(2) }}</td>
                    </tr>
                    <tr>
                        <td class="text-center font-weight-bold">PUPIL'S PERCENTAGE</td>
                        <td></td>
                        <td class="text-center font-weight-bold" v-if="result && result.percentage">{{((result.percentage/20) * 100).toFixed(2)}}</td>
                    </tr>
                    <tr>
                        <td class="text-center font-weight-bold">GRADE</td>
                        <td></td>
                        <td class="text-center font-weight-bold" v-if="percentage">{{ getGrades() }}</td>
                    </tr>
            </tbody>
		</table>

        <table class="table table-bordered table-sm primary">
			<tbody><tr>
                    <td colspan="2" class="font-weight-bold">HOLIDAY ASSESSMENT REPORT</td>
                </tr>
                <tr>
                    <td class="subject1" v-for="(holiday,ind) in result.holiday_assessment " :key="ind">{{ holiday.subject }}</td>
                </tr>
                <tr>
                    <td v-for="(holiday,ind) in result.holiday_assessment " :key="ind">{{ holiday.score }}</td>
                </tr>
		    </tbody>
        </table>

        <p><span class="font-weight-bold">Class Teacher's Comment:</span> <span class="ct_remarks" v-if="result.remarks && result.remarks.length > 0">{{ result.remarks[0].CT_remarks }}</span></p>

        <p>Class Teacher's Sign:...............................     Head Teacher's Sign: ....................................</p>
        </div>
    </div>
</template>
<script>
import { Link } from '@inertiajs/inertia-vue'
export default {
    props:['students','section'],
    components:{Link},
    data(){
        return{
            result: [],
            student_id:"",
            percentage: 0,
            class_avg: 0,
            picture: []
        }
    },
    methods:{
        getResult(){
            this.student_id = this.$refs.student_id.value;
            axios.get('/get-midterm-result',{params:{student_id:this.student_id, section: this.section}}).then((response)=>{
                this.result = response.data
                this.calculatePercentage();
                this.picture = response.data.picture
                this.class_avg = ((this.result.class_avg/20) *100).toFixed(2)
            })
        },
        calculatePercentage(){
            let total = 0;
            for(let exam of this.result.primary_exam){
                total += Number((exam.first_ca/20) * 100)
                
            }
           this.percentage = (total/this.result.primary_exam.length).toFixed(2)
        },
        getGrades(){
            if(this.percentage >= 90){
                return "A+"
            }else if(this.percentage <= 89.99 && this.percentage >= 79.99){
                return "A"
            }else if(this.percentage <= 79.98 && this.percentage >= 69.99){
                return "B+"
            }else if(this.percentage <=69.98 && this.percentage >=59.99){
                return "B"
            }else if(this.percentage <=59.98 && this.percentage >= 49.99){
                return "C"
            }else if(this.percentage <=49.98 && this.percentage >= 39.99){
                return "D"
            }else if(this.percentage <= 39.98){
                return "E"
            }
        },
        getResumptionDate(){
                if(this.result && this.result.settings && this.result.settings.mid_term_resumption){
                    const date = new Date(this.result.settings.mid_term_resumption);
                    const formattedDate = new Intl.DateTimeFormat("en-US", {
                    weekday: "long",
                    year: "numeric",
                    month: "long",
                    day: "numeric",
                    }).format(date);

                    return formattedDate;
                }
               
            },


    }
}
</script>