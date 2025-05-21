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
    table{
        margin-bottom: 0;
    }

    .hidden-check{
        visibility: hidden;
    }
    .table-sm th, .table-sm td {
        padding: 0rem;
    }

    td.py-1 {
        padding-top: 0.1rem !important;
        padding-bottom: 0.1rem !important;
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
        <div class="text-center my-4" v-if="picture">
            <img :src="picture.path" style="height: 140px;" alt="student" class="img-thumbnail">
        </div>
        <div v-if="result.student">
            <p class="text-center" v-if="result && result.settings">Midterm Progress Report for <span class="term">{{ (result.settings.term).split('_').join(" ") }}, </span> <span class="session">{{ result.settings.session }}</span> Session.</p>
            <p class="text-center">
                <span class=" font-weight-bold"> PUPIL'S NAME:</span> 
                <span class="std_name mr-5">{{ result.length !=0 ? result.student.surname+" "+result.student.othernames:'' }}</span>
                <!-- <span class=" font-weight-bold">	DATE:</span> 
                <span class="date" style="text-transform: uppercase;">May 11, 2023, 12:56 pm</span> -->
            </p>
            <p class="text-center">
                <span class=" font-weight-bold">CLASS:</span> 
                <span class="clas mr-5">{{result.student.grade}}</span>
                <span class=" font-weight-bold">	SCHOOL RESUMES:</span> 
                <span class="resume">{{ getResumptionDate() }}</span>
            </p>

            <table class="table table-bordered table-sm primary2 mb-2">
			<thead>
				<tr>
					<th>SUBJECTS</th>
					<th>CONCEPT</th>
					<th>MASTERED CONCEPT</th>
                    <th>NEEDS WORK</th>
				</tr>
			</thead>
			<tbody class="midterm-td">
                <tr v-for="(result, index) in prenurseryexam" :key="index">
                    <td class="pl-2">{{index}}</td>
                    <td>
                        <table class="table">
                            <tbody>
                                <tr v-for="(subj, ind) in result" :key="ind">
                                    <td class="px-2 py-1">{{ subj.subject }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table class="table">
                            <tbody>
                                <tr v-for="(subj, ind) in result" :key="ind">
                                    <td class="px-2 py-1" v-if="subj.value == 2"><i class="fas fa-check" ></i></td>
                                    <td v-else class="hidden-check px-2 py-1"><i class="fas fa-check text-gray "></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                    <td>
                        <table class="table">
                            <tbody>
                                <tr v-for="(subj, ind) in result" :key="ind">
                                    <td class="px-2 py-1" v-if="subj.value == 1"><i class="fas fa-check"></i></td>
                                    <td v-else class="hidden-check px-2 py-1"><i class="fas fa-check text-gray"></i></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </tbody>
               
		</table>

        <div class="row">
                <div class="col-md-12">
                    <div class="remarks report-table-hide">
                        <div class="row" v-if="result.student && result.student.remarks && result.student.remarks.length > 0">
                            <div class="col-md-6" v-if="result.student && result.student.remarks">
                                <p class=" font-weight-bold">Principal's Remark:</p><p class="principal htremarks" style="text-decoration: none">{{ result.student.remarks[0].HT_remarks }}</p>
                                <p>Principal's Signature:.......................</p>
                            </div>
                            <div class="col-md-6" v-if="result.student && result.student.remarks">
                                <p class=" font-weight-bold">Form Teacher's Remark:</p><p class="form_teacher htremarks" style="text-decoration: none">{{ result.student.remarks[0].CT_remarks }}</p>
                                <p>Form Teacher's Signature:.......................</p>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
        </div>
    </div>
</template>
<script>
import { Link } from '@inertiajs/inertia-vue'
export default {
    props:['students','section',],
    components:{Link},
    data(){
        return{
            result:[],
            student_id:"",
            prenurseryexam:[],
            student: [],
            picture: []
        }
    },
    methods:{
        getResult(){
            this.student_id = this.$refs.student_id.value;
            axios.get('/get-midterm-result',{params:{student_id:this.student_id, section:this.section}}).then((response)=>{
                this.result = response.data
                this.prenurseryexam = response.data.prenurseryexam
                this.student = response.data.student
                this.picture = response.data.student.picture
                //this.calculatePercentage();
                this.class_avg = ((this.result.class_avg/20) *100).toFixed(2)
            })
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