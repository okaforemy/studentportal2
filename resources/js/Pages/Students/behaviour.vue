<style scoped>
    .form-control, .custom-select {
        height: calc(4.5rem + 2px) !important;
        font-size: 1rem !important;
    }
</style>

<template>
    <div>
        <h4 class="py-3 text-center">Student's behaviour and homework for {{ grade }}</h4>
        <table class="table">
            <thead>
                <tr>
                    <th>S/N</th>
                    <th>Students</th>
                    <th>BEHAVIOUR AND ATTITUDE TO LEARNING</th>
                    <th>HOMEWORK</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(student, index) in students" :key="index">
                    <td tabindex="-1">{{ index+1 }}</td>
                    <td tabindex="-1">{{ student.surname+" "+student.othernames }}</td>
                    <td><textarea class="form-control" :ref="'behaviour'+index" :class="'behaviour'+index" rows="3">{{ (student.behaviour)?student.behaviour.behaviour: '' }}</textarea></td>
                    <td><textarea class="form-control" :ref="'homework'+index" :class="'homework'+index" rows="3">{{ (student.behaviour)?student.behaviour.homework: '' }}</textarea></td>
                    <td tabindex="-1">
                        <input type="hidden" :ref="'student_id'+index" :value="student.id">
                        <input type="hidden" :ref="'id'+index" :value="(student.behaviour)?student.behaviour.id:''">
                        <button tabindex="-1" class="btn btn-primary btn-sm" @click="saveRemarks(index)">save</button>
                    </td>
                </tr>   
            </tbody>
        </table>
    </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia'
export default {
    props: ['students', 'grade', 'settings'],

    data(){
        return{
            HTremark: '',
            CTremark: '',
            remark_by_HT:false,
            remark_by_CT: false,
            form:{

            }
        }
    },

    methods: {
        saveRemarks(index){
          let behaviour = this.$refs['behaviour'+index][0].value;
          let homework = this.$refs['homework'+index][0].value;
          let student_id = this.$refs['student_id'+index][0].value;
          let id = this.$refs['id'+index][0].value;
          let data = {
            behaviour: behaviour,
            homework: homework,
            session: this.settings.session,
            term: this.settings.term,
            student_id: student_id,
            id: id
          }
          axios.post('/behaviour',data).then((response)=>{
            if(response.data.success){
                toastr.success("Remarks added successfully!", "Success");
            }
          })
        }
    },

    created(){

    }
}
</script>