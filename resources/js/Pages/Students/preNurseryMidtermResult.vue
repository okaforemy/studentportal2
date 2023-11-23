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
        <div class="text-center my-4">
            <img src="" height="70px" alt="student" class="img-thumbnail">
        </div>
        <div>
            <p class="text-center">Holiday Assessment/Midterm Progress Report for <span class="term"></span> <span class="session"></span> Session.</p>
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
                <span class="resume">Monday, 17th April, 2023</span>
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

        <p><bold></bold>Class Teacher's Comment: <span class="ct_remarks">Derick is an impressive learner. He is making steady progress in all his subjects. Howbeit, he needs a little push in his Creative Arts. Happy Easter!</span></p>

        <p>Class Teacher's Sign:...............................     Head Teacher's Sign: ....................................</p>
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
            student: []
        }
    },
    methods:{
        getResult(){
            this.student_id = this.$refs.student_id.value;
            axios.get('/get-midterm-result',{params:{student_id:this.student_id, section:this.section}}).then((response)=>{
                this.result = response.data
                this.prenurseryexam = response.data.prenurseryexam
                this.student = response.data.student
                this.calculatePercentage();
                this.class_avg = ((this.result.class_avg/20) *100).toFixed(2)
            })
        },
    }

}
</script>