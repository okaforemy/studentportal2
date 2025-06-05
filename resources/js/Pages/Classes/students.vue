<template>
    <div class="pt-4">
        <div class="card">
              <div class="card-header">
                <h3 class="card-title">{{grade+", "+arm}} Class List</h3>
                
                <div class="card-tools">
                  
                  <div class="input-group input-group-sm" style="width: 350px;">
                    <input type="text" name="table_search" v-model="query" class="form-control float-right" placeholder="Search">

                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                  </div>
                </div>
                <div class="text-center">
                  <button class="btn px-2 mx-2" @click="is_promotion_started = !is_promotion_started">Start Student's promotion/demotion</button>
                  
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body p-0">
                <div class="row mx-2">
                  <div class="my-2 col-md-4">
                     <label for="">Promote to: </label>
                    <select name="" @change="selectClass" v-model="selected_class_id" v-if="is_promotion_started" id="" class="form-control">
                      <option value="">Select class</option>
                      <option :value="clas.id" v-for="clas in classes">{{ clas.class_name }}</option>
                    </select>
                  </div>

                  <div class="my-2 col-md-4" v-if="is_promotion_started && selected_class.arms && selected_class.arms.length > 0">
                    <label for="">Arms</label>
                    <select name="" v-model="selected_arms"  id="" class="form-control">
                      <option value="">Select arm</option>
                      <option :value="arm.arm_name" v-for="arm in selected_class.arms">{{ arm.arm_name  }}</option>
                    </select>
                  </div>
                </div>
                <table class="table table-hover table-responsive text-nowrap">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th><input v-model="select_all"  v-if="is_promotion_started" type="checkbox" class="mr-2 custom-check" name="" id="">Surname</th>
                      <th>Other names</th>
                      <th>Date of birth</th>
                      <th>Sex</th>
                      <th>Grade</th>
                      <th>Student ID</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(student,index) in allStudents.data" :key="index">
                      <td>{{index+1+parseInt(page)}}</td>
                      <td><input type="checkbox" v-model="selected_student" :value="student.id" v-if="is_promotion_started" class="mr-2" name="" id="">{{student.surname}}</td>
                      <td>{{student.othernames}}</td>
                      <td>{{student.dob}}</td>
                      <td>{{student.sex}}</td>
                      <td>{{student.grade}}</td>
                        <td>{{student.student_id}}</td>
                        
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
                                  <a class="dropdown-item" @click.prevent="deleteStudent(student.name, student.id)" href="/delete-student" > Delete </a>
                              </div>
                            </div>
                        </td>
                    </tr>
                  
                  </tbody>
                </table>
                <div class="my-2 text-right" v-if="selected_student && selected_student.length >0">
                  <button class="btn btn-primary px-2 mx-4 py-2" @click.prevent="promoteStudent" :disabled="is_promoting">Promote</button>
                </div>
                <Paginator :links="students.links"/>
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
import { Inertia } from '@inertiajs/inertia'
import debounce from 'lodash/debounce';

export default {
    components:{
        Head,Link, Paginator, Inertia
    },
    props:{
        students: Object,
        grade: String,
        page: String,
        arm: String,
        is_promotion_started: false,
        
    },

    data(){
      return{
        query:'',
        allStudents: this.students,
        selected_student: [],
        select_all: false,
        classes: [],
        selected_class: [],
        selected_class_id:'',
        selected_arms: '',
        is_promoting: false,
      }
    },
    methods:{
      selectClass(){
          let selected = this.classes.filter((item)=>item.id == this.selected_class_id);
          if(selected){
            this.selected_class = selected[0]
          }
      },
      promoteStudent(){
            if(this.selected_class_id){
              this.is_promoting = true;
                let data = {
                    class_id: this.selected_class_id,
                    arm_name: this.selected_arms,
                    class_name: this.selected_class?.class_name,
                    students: this.selected_student
                }

                axios.post('/promote-students', data).then((response)=>{
                  if(response.data.success){
                     toastr.success('Students promoted successfully!', 'Success')
                     this.is_promoting = false
                     Inertia.reload({ only: ['students'] })
                  }
                })
            }
        },
      searchClassesStudents(query) {
            axios.get('/search-classes-students', { params: { search: query } })
                .then((response) => {
                    this.allStudents.data = response.data.data
                    this.links = response.data.links
                })
                .catch((error) => {
                    console.error(error); // Handle errors
                });
        },
      getClassesWithArms(){
        axios.get('/classes-with-arms').then((response)=>{
          this.classes = response.data
        })
      }
    },

     watch:{
        query: debounce(function(newVal){
            this.searchClassesStudents(newVal)
        }, 500),

        select_all(){
          if(this.select_all){
            this.selected_student = []
            for(let data of this.students.data){
              this.selected_student.push(data.id)
            }
          }else{
            this.selected_student = []
          }
        },

        is_promotion_started(newVal){
          if(newVal){
            this.getClassesWithArms()
          }
        },

        students(newVal){
          this.allStudents = newVal
        }
    },
    
    
}
</script>