<style scoped>
    .overflow{
        text-overflow: ellipsis;
        overflow: hidden;
        white-space: normal;
    }

    .delete_subject {
    display: none;
    }

    .delete_parent:hover .delete_subject {
        display: inline;
        cursor: pointer;
    }

</style>
<template>
    <div>
        <Head title="add student scores" />
        <div class="pt-3">
            <div class="row">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Add scores for <span class="text-bold">{{student.fullname}}</span> in {{student.grade}}</h3>
                        </div>
                        <div class="card-body">
                            <form action="" id="scores" @submit.prevent="saveStudentScores">
                               <table class="table table-sm mt-4" v-for="(subject, index) in subjects" :key="index">
                                <thead>
                                    <tr>
                                        <th>{{ index }}</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(subj, ind) in subject" :key="ind">
                                        <td class="delete_parent">{{ subj.subject}} <span class="delete_subject"><Link href="/delete-pre-nursery-exam-subject" :data="{id: subj.id}"><i class="fas fa-trash text-danger"></i></Link></span></td>
                                        <td>
                                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                                <label class="btn btn-primary btn-sm text-white" :class="{active:subj.value==2}">
                                                    <input type="radio" v-model="subject_data['data_'+subj.id].value" @click="getsubject($event,subj,2)" :checked="subj.value==2" ref="mastered" :name="'scores'+(subj.id)" value="2"> Mastered Concepts
                                                </label>
                                                <label class="btn btn-primary btn-sm text-white" :class="{active: subj.value==1}">
                                                    <input type="radio" v-model="subject_data['data_'+subj.id].value" @click="getsubject($event, subj,1)" ref="needs" :checked="subj.value==1" :name="'scores'+(subj.id)" value="1"> NEEDs Work
                                                </label>
                                            </div>
                                            <input type="hidden" ref="subject" name="subject" :value="subj.subject">
                                            <input type="hidden" ref="category" name="category" :value="index">
                                            <input type="hidden" ref="student_id" name="student_id" :value="student.id">
                                            <input type="hidden" ref="id" name="id" :value="subj.id? subj.id:''">
                                        </td>
                                    </tr>
                                </tbody>
                               </table>
                               <button class="btn btn-primary float-right" type="submit">Save</button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Students</h3>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body table-sm-responsive p-0">
                            <table class="table table-hover text-nowrap">
                                <thead>
                                    <tr>
                                        <th>S/N</th>
                                        <th>Students</th>
                                    </tr>
                                </thead>
                                <tbody>
                                   <tr v-for="(student,index) in students.data" :key="index" :class="{'table-success': index==selectedstudent}">
                                       <td>{{(page != null)? index+1+page: index+1}}</td>
                                       <td class="overflow">
                                           <Link href="/students-exam-scores" :data='{studentid:student.id, arm:student.arm, grade: grade, page:pagination_page, currentStudent: index, section:section}'>{{student.fullname}}</Link>
                                        </td>
                                   </tr>
                                </tbody>
                            </table>
                            <nav aria-label="..." class="ml-4 mt-4" >
                                <ul class="pagination">
                                    <li class="page-item" :class="{active : link.active, disabled : link.url ==null }" v-for="(link,index) in students.links" :key="index">
                                    <Link  :href="link.url"  preserve-scroll :data="{newpage:true}"  v-html="link.label" class="page-link" />
                                    </li>
                                </ul>
                            </nav>
                        </div>
                        <!-- /.card-body -->
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia'
import { Link } from '@inertiajs/inertia-vue'
export default {
    components:{
        Inertia, Link
    },
    props:['students', 'student','selectedstudent','page','currentpage','grade','section','subjects', 'term', 'session'],
    
    data(){
        return{
            pagination_page: (this.currentpage)? this.currentpage:1,
            allMastered: [],
            allNeeds: [],
            pre_subjects: [],
            subject_data: {}
        }
    },
    methods: {
        getsubject(e, data, val){
            if(e.target.checked){
                let found = this.pre_subjects.filter(item => item.id === data.id );
               
                if(found.length ==0){
                    this.pre_subjects.push({
                        value: val,
                        subject: data.subject,
                        category: data.category,
                        student_id: this.student.id,
                        session: this.session,
                        term:this.term,
                        //id: data.id
                    })
                }else{
                    this.pre_subjects.splice(found,1);
                    this.pre_subjects.push({
                        value: val,
                        subject: data.subject,
                        category: data.category,
                        student_id: this.student.id,
                        session: this.session,
                        term:this.term,
                        //id: data.id
                    })
                }
               
            }
        },
        
        saveStudentScores(){
         
          axios.post('/students-exam-scores',{data:this.subject_data, section:this.section}).then((response)=>{
            toastr.success('Saved successfully!', 'Success')
            Inertia.reload({ only: ['subjects'] })
          })
        }
    },
    created(){
        
        for(let subject in this.subjects){
            let order = 1;
            for(let val of this.subjects[subject]){
               
                this.subject_data['data_' + val.id] = {
                    subject: val.subject,
                    category: val.category,
                    value: (val && val.value)? val.value: '',
                    session:this.session,
                    term: this.term,
                    student_id: this.student.id,
                    order: order
                };
                order += order + 1;
            }
        }
    }
    
}
</script>