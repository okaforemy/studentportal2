<style scoped>
    .card{
        margin-bottom: 0;
    }
    .table-responsive{
      overflow: visible;
    }
</style>
<template>
    <div class="pt-4 pb-4 px-3">
        <Head title="Students"/>
        <div class="card">
              <div class="card-header">
                <h3 class="card-title">Students</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 450px;">
                    <input type="text" v-model="query" name="table_search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                    <Link href="/add-students" class="ml-4 btn btn-primary">Add student</Link>
                  </div>
                   
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body  p-0" style="overflow-x: scroll;">
                <table class="table table-responsive table-hover text-nowrap" style="font-size: 14px;">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Name</th>
                      <th>Date of birth</th>
                      <th>Sex</th>
                      <th>Grade</th>
                      <th>Student ID</th>
                      <th>Progress</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(student,index) in allStudents" :key="index">
                      <td>{{index+1}}</td>
                      <td>{{student.fullname}}</td>
                      <!-- <td>{{student.othernames}}</td> -->
                      <td>{{student.dob | date}}</td>
                      <td>{{student.sex}}</td>
                      <td>{{student.grade}} {{ student.arm }}</td>
                        <td>{{student.student_id}}</td>
                        <td>
                           <radial-progress-bar :diameter=45
                       :completed-steps="student.reg_progress"
                       :total-steps=100
                       :strokeWidth=8
                       :innerStrokeWidth=8
                       :startColor="'#2ECC71'"
                       :stopColor="'#2ECC71'"
                       :innerStrokeColor="'#F1F1F1'"
                       >
                           </radial-progress-bar>
                        </td>
                        <td>
                            <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Options
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <Link class="dropdown-item" href="/add-parents" :data="{id:student.id,surname:student.surname, othernames:student.othernames}">Add parents' info</Link>
                                <!-- <a class="dropdown-item" href="#">Add medical info</a>
                                <a class="dropdown-item" href="#">Assign hostel</a>
                                <a class="dropdown-item" href="#">Assign house</a>
                                <a class="dropdown-item" href="#">Assign club</a> -->
                                <div class="dropdown-divider"></div>
                                <Link class="dropdown-item" :href="`/students-exam-scores?studentid=${student.id}&arm=${student.arm}&grade=${student.grade}&page=1&section=${student.student_grade.section}&singleStudent=true`">Add scores</Link>
                                <a class="dropdown-item" :href="`get-result-page?singleResult=true&studentid=${student.id}&section=${student.student_grade.section}&grade=${student.grade}`">Check result</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" :href="'/edit-student/'+student.id">Edit</a>
                                <a class="dropdown-item" @click.prevent="deleteStudent(student.fullname, student.id)" href="/delete-student" > Delete </a>
                            </div>
                            </div>
                        </td>
                    </tr>
                  
                  </tbody>
                </table>
                <Paginator :links="links"/>
              </div>
              <!-- /.card-body -->
            </div>
    </div>
</template>

<script>
import moment from 'moment'
import { Head } from '@inertiajs/inertia-vue'
import { Link } from '@inertiajs/inertia-vue'
import Paginator from '../../Shared/paginator.vue'
import RadialProgressBar from 'vue-radial-progress'
import { Inertia } from '@inertiajs/inertia'
import debounce from 'lodash/debounce';

export default {
    components:{
        Head,Link, Paginator, RadialProgressBar, Inertia
    },
    props:['students'],
    data(){
        return{
           query:'',
           allStudents: this.students?.data,
           links: this.students.links
        }
    },
    methods:{
       deleteStudent(name,id){
           $.confirm({
            title: 'Delete!',
            content: 'Do you want to delete '+name,
            type: 'red',
            buttons: {   
                ok: {
                    text: "ok!",
                    btnClass: 'btn-primary',
                    keys: ['enter'],
                    action: function(){
                       axios.get('/delete-student',{params:{id:id}}).then((response)=>{
                        //  console.log(response.data)
                        //    if(response.data){
                        //        Inertia.reload({ only: ['students'] })
                        //    }
                         window.location.href = window.location.href
                       })
                    }
                },
                cancel: function(){
                      
                }
            }
        });
       },
       
       fetchData(query) {
        //if (query) { // Ensure query is checked correctly
            axios.get('/filter-students', { params: { search: query } })
                .then((response) => {
                    this.allStudents = response.data.data
                    this.links = response.data.links
                })
                .catch((error) => {
                    console.error(error); // Handle errors
                });
        //}
    }

      },
       
    filters:{
        date: function(value){
            if(value){
               return moment(String(value)).format('ll')
               //return moment(value).format("MMM Do YY")
           }
        }
    },
    watch: {
    query: debounce(function (newQuery) {
      this.fetchData(newQuery);
    }, 500), // 500ms delay
  },
    mounted(){
        //console.log(this.students)
    }
    
}
</script>