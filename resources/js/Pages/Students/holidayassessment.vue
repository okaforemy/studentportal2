<style scoped>
    .fixed{
        position: fixed;
    }
</style>
<template>
    <div class="pt-3 px-4 py-4 col-md-10">
        <form action="" id="holidayForm">
            
            <table class="table table-strip">
                <thead>
                    <tr>
                        <th>S/N</th>
                        <th>Names</th>
                        <!-- <th>
                            <select class="">
                                <option value="">Select subject</option>
                                <option :value="subj.subject" v-for="(subj, ind) in students[1].subjects" :key="ind">{{ subj.subject }}</option>
                            </select> 
                            <input type="number" class="" placeholder="max">
                        </th> -->
                        <!-- <th ref="subject1" :data-value="students[0].subjects[0].subject">{{ students[0].subjects[0].subject }} (40)</th>
                        <th ref="subject2" :data-value="students[0].subjects[1].subject">{{ students[0].subjects[1].subject }} (30)</th>
                        <th ref="subject3" :data-value="students[0].subjects[2].subject">{{ students[0].subjects[2].subject }} (30)</th> -->
                       <template v-if="students[0].holiday_assessment && students[0].holiday_assessment.length ===0">
                            <th v-for="(subj, inde) in hollyday_subj" :data-value="subj.subject" :ref="'subject'+(inde+1)" :key="inde">{{ subj.subject }} ({{subj.max_score}})</th>
                       </template>
                       <template v-else>
                            <!-- <th v-for="(subj, inde) in students[0].holiday_assessment" :data-value="subj.subject" :ref="'subject'+(inde+1)" :key="inde">{{ subj.subject+ " ("+ maxScoreValue(subj.subject) +")"}}</th> -->
                            <th v-for="(subj, inde) in hollyday_subj" :data-value="subj.subject" :ref="'subject'+(inde+1)" :key="inde">{{ subj.subject }} ({{subj.max_score}})</th>
                       </template>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(student, index) in students" :key="index">
                        <template v-if="student.holiday_assessment && student.holiday_assessment.length ===0">
                            <td>{{ index+1 }}</td>
                            <td>{{ student.surname+' '+student.othernames }}</td>
                            <td><input type="text" :data-val="student.id" :data-id="''" ref="score1" @keyup="validate($event,hollyday_subj[0],'')"  @keypress="isNumber"   name="score1" class="form-control"></td>
                            <td><input type="text" :data-val="student.id" :data-id="''" ref="score2" @keyup="validate($event,hollyday_subj[1],'')"  @keypress="isNumber"    name="score2" class="form-control"></td>
                            <td><input type="text" :data-val="student.id" :data-id="''" ref="score3" @keyup="validate($event,hollyday_subj[2],'')"  @keypress="isNumber"  name="score3" class="form-control"></td>
                            <input type="hidden" ref="student_id" name="student_id" :value="student.id">
                        </template>
                        <template v-else>
                            <td>{{ index+1 }}</td>
                            <td>{{ student.surname+' '+student.othernames }}</td>
                            <td v-for="(ha, ind) in student.holiday_assessment" :key="ind"><input type="text" :data-val="student.id" :data-id="ha.id" :name="'score'+(score_index ==3? score_index =1 :++score_index)"  :ref="'score'+(score_index ==3? score_index =1 :++score_index)"  @keyup="validate($event,hollyday_subj[ind].max_score, ha.score)" @keypress="isNumber" :value="ha.score" class="form-control"></td>
                            <!-- <input type="hidden" ref="student_id" name="student_id" :value="student.id"> -->
                        </template>
                    </tr>
                </tbody>
            </table>
            <div class="text-right">
                <button class="btn btn-primary my-2" @click.prevent="saveHolidayAssessment">Save</button>
            </div>
        </form>
    </div>
</template>

<script>
//import { router } from '@inertiajs/vue2'

export default {
    props:['students','grade'],
    data(){
        return{
            allstudents: this.students,
            // hollyday_subj:[]
            hollyday_subj: JSON.parse(this.grade.subjects).filter(function(val){
                return val.holiday ==1;
            }),
            score_index: 0,
        }
    },
    methods:{
        maxScoreValue(subject){
            let found = this.hollyday_subj.find(val=> val.subject = subject)
            return found? found.max_score:''
        },
        saveHolidayAssessment(){
           
            let score1 = this.$refs['score1'];
            let score2 = this.$refs['score2'];
            let score3 = this.$refs['score3'];
            
            //let student_id = this.$refs['student_id'];
            let subject1 = this.$refs['subject1'][0].getAttribute('data-value');
            let subject2 = this.$refs['subject2'][0].getAttribute('data-value');
            let subject3 = this.$refs['subject3'][0].getAttribute('data-value');
        
           // let ids = [];
            // ids.push(this.$refs['score1'][0].getAttribute('data-id'))
            // ids.push(this.$refs['score2'][0].getAttribute('data-id'))
            // ids.push(this.$refs['score3'][0].getAttribute('data-id'))

            let id1 = this.$refs['score1']
            let id2 = this.$refs['score2']
            let id3 = this.$refs['score3']

            //let student_ids = [];
            // student_ids.push(this.$refs['score1'][0].getAttribute('data-val'))
            // student_ids.push(this.$refs['score2'][0].getAttribute('data-val'))
            // student_ids.push(this.$refs['score3'][0].getAttribute('data-val'))

            let student_id1 = this.$refs['score1']
            let student_id2 = this.$refs['score2']
            let student_id3 = this.$refs['score3']

            let data = []
            let j =1;
            for(let i=0; i < score1.length; i++){
               for(let j =1; j <=3; j++){
                let score = j==1?score1: j==2? score2: score3
                //let score = score[i];
                let subject =j==1? subject1: j==2? subject2: subject3;
                
              // console.log(student_id_single)
                let id = j==1? id1[i].getAttribute('data-id'): j==2? id2[i].getAttribute('data-id'): id3[i].getAttribute('data-id');
                let student_id = score1[i].getAttribute('data-val')
                //let std_id = j==1? student_id1[i].getAttribute('data-val'): j==2? student_id2[i].getAttribute('data-val'): student_id3[i].getAttribute('data-val')

                let val ={
                    score: score[i].value? parseFloat(score[i].value): 0,
                    subject: subject,
                    student_id: student_id,
                    term: 'second',
                    id: id,
                    session: '2021/2022',
                }
                data.push(val)
               }
              
            }
          
           // data.push(ids)
            // axios.post('/holiday-assessment',data).then((response)=>{
            //     toastr.success('Saved successfully!', 'Success')
            // })
            this.$inertia.post('/holiday-assessment',data)
        },
        validate(event,val,oldval){
            let old_val = oldval
                if(event.target.value > parseInt(val.max_score)){
                    toastr.error('Maximum number entered!', 'Error')
                    event.target.value=old_val
                }
            
        },
        isNumber(evt){
            const charcode = evt.which? evt.which: evt.keyCode;
            if(charcode > 31 && (charcode < 48 || charcode > 57) && charcode !=46){
                evt.preventDefault();
            }
        },
    },

}
</script>