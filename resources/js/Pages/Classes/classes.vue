<style scoped>
    .fa-plus{
        cursor: pointer;
    }
    #accept-child {
        border: 1px solid #CED4DA;
        border-radius: 5px;
        padding-top: 10px;
        padding-left: 5px;
        padding-right: 5px;
    }
    #accept-child span{
        padding: 5px;
    }
    .no-border-input{
        border: none;
    }
    #accept-child span{
        padding: 5px;
    }
    .no-style{
        list-style: none;
        padding-left: 0;
        box-shadow: 0 0 1px rgba(0, 0, 0, 0.125), 0 1px 3px rgba(0, 0, 0, 0.2);
    }
    .no-style li{
        padding: 8px 0 8px 12px;
        border-bottom: 1px solid #CED4DA;
        cursor: pointer;
    }
    .no-style li:hover{
        background: #dde0e4;
        border-radius: 2px;
    }
    .tags{
        list-style: none;
        list-style: none;
        display: inline;
        padding-left: 5px;
        padding-right: 5px;
    }
    .tag-item {
        background: gray;
        margin-right: 12px;
        color: white;
        padding: 2px 10px 5px 10px;
        border-radius: 6px;
        line-height: 1.4;
        float: left;
        margin: 5px;
    }
    .tag-item span{
        cursor: pointer;
    }

    .inner{
        margin-left: 14px;
    }

    .dropdown-item-custom:hover .dropdown-menu-custom {
        display: block;
    }

</style>
<template>
    <div class="pt-3">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Classes</h3>

                <div class="card-tools">
                    <div class="input-group input-group-sm" style="width: 450px;">
                        <input type="text" v-model="query" name="table_search" class="form-control float-right"
                            placeholder="Search">
                        <div class="input-group-append">
                            <button type="submit" class="btn btn-default">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                        <Link href="/add-class" class="ml-4 btn btn-primary">Add classes</Link>
                    </div>

                </div>
            </div>
            <!-- /.card-header -->
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Class name</th>
                            <th>Arm(s)</th>
                            <!-- <th>Class teacher</th>
                            <th>Capacity</th> -->
                            <th>Section</th>
                            <th>Subjects</th>
                            <th>Options</th>
                        </tr>
                    </thead>
                    <tbody>
                       <tr v-for="(clas, index) in allClasses.data" :key="index" >
                           <td>{{index + 1}}</td>
                           <td v-if="clas.arms.length == 0"><Link href="/class-list" :data="{class:clas.class_name}" class="inner">{{clas.class_name}}</Link></td>
                           <td v-else>
                              <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{clas.class_name}}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <Link class="dropdown-item" v-for="(arm, ind) in clas.arms" :key="ind" href="/class-list" :data="{class:clas.class_name, arm: arm.arm_name}">{{arm.arm_name}}</Link>
                                    </div>
                                </div>
                           </td>
                           <td>
                               <i class="fas fa-plus text-success pr-2" @click="arms(clas.id, clas.class_name,clas.arms)"></i>
                               <span v-for="(arm,ind) in clas.arms" :key="ind">
                                   {{" "+arm.arm_name+", "}}
                               </span>
                           </td>
                           <!-- <td>{{clas.teacher_incharge}}</td>
                           <td>{{clas.capacity}}</td> -->
                           <td>{{clas.section.replace('_',' ')}}</td>
                           <td v-if="clas.arms.length == 0">
                               <Link href="/assign-subjects" :data="{section: clas.section, grade:clas.class_name}" class="inner">{{(clas.subjects_count !==0)? "edit subjects": "add subjects"}}</Link></td>
                           <td v-else>
                               <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Select arm
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <Link class="dropdown-item"  v-for="(arm, ind) in clas.arms" :key="ind" href="/assign-subjects" :data="{section: clas.section, grade:clas.class_name, arm: arm.arm_name}">{{(arm.subjects_count !==0)? arm.arm_name+" [edit subjects]": arm.arm_name+" [add subjects]"}}</Link>
                                    </div>
                                </div>
                           </td>
                           <td v-if="clas.arms.length ==0">
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        Options
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <Link href="/students-exam-scores" class="dropdown-item" :data="{grade:clas.class_name,section:clas.section}">Student's score</Link>
                                        <Link href="/upload-scores" v-if="clas.section=='primary' || clas.section=='junior secondary' || clas.section == 'senior secondary'" class="dropdown-item" :data="{grade:clas.class_name,section:clas.section, class_id: clas.id}">Upload scores</Link>
                                        <a href="#" v-if="clas.section=='primary' || clas.section=='junior secondary' || clas.section == 'senior secondary'"  class="dropdown-item" :data="{id: clas.id}" @click="dowloadScoreSheet(clas.id,'',clas.section)">Download Score sheet</a>
                                        <Link href="/holiday-assessment" v-if="clas.section=='primary'"  class="dropdown-item" :data="{grade: clas.class_name}">Holiday Assessment</Link>
                                        <Link href="/affective-disposition" class="dropdown-item" :data="{grade:clas.class_name,currentStudent:0,section:clas.section}">Affective disposition</Link>
                                        <Link href="/attendance" class="dropdown-item" :data="{grade:clas.class_name, section: clas.section}">Attendance</Link>
                                        <Link href="/physical-development" :data="{grade:clas.class_name}" class="dropdown-item">Physical development</Link>
                                        <Link href="/remarks" class="dropdown-item" :data="{grade: clas.class_name}">Remarks</Link>
                                        <Link href="/behaviour" class="dropdown-item" :data="{grade: clas.class_name}" v-if="clas.section == 'pre nursery'">Behaviour</Link>
                                        <div class="dropdown-divider"></div>
                                        <Link href="/add-fee-to-class" class="dropdown-item" :data="{grade: clas.id}" >Add fee</Link>
                                        <div class="dropdown-divider"></div>
                                        <Link href="/get-result-page" :data="{section: clas.section, grade:clas.class_name}" class="dropdown-item">Results</Link>
                                        <Link href="/mid-term-result" :data="{grade: clas.class_name, section:clas.section}" class="dropdown-item">Mid-term Results</Link>
                                        <div class="dropdown-divider"></div>
                                        <Link href="/edit-class" :data="{id:clas.id}" class="mr-2 dropdown-item">edit</Link>
                                        <a @click="deleteClass(clas.class_name, clas.id)" href="#" class="dropdown-item">delete</a>
                                    </div>
                                </div>
                           </td>
                           <td v-else>
                                <div class="dropdown">
                                    <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {{clas.class_name}}
                                    </button>
                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                        <Link class="dropdown-item dropdown-item-custom" v-for="(arm, ind) in clas.arms" :key="ind" href="/class-list" :data="{class:clas.class_name, arm: arm.arm_name}">
                                            {{arm.arm_name}}
                                            <!-- remove btn-group for more spacing in the dropdown -->
                                            <div class="dropdown dropleft btn-group">
                                                <div class="dropdown-menu dropdown-menu-custom" aria-labelledby="dropdownMenuButton">
                                                    <Link href="/students-exam-scores" class="dropdown-item" :data="{grade:clas.class_name,section:clas.section, arm:arm.arm_name}">Student's score [ {{arm.arm_name}}]</Link>
                                                    <Link href="/upload-scores" v-if="clas.section=='primary' || clas.section=='junior secondary' || clas.section == 'senior secondary'" class="dropdown-item" :data="{grade:clas.class_name,section:clas.section, class_id: clas.id, arm:arm.arm_name, arm_id:arm.id}">Upload scores</Link>
                                                    <a href="#" v-if="clas.section=='primary' || clas.section=='junior secondary' || clas.section == 'senior secondary'"  class="dropdown-item" :data="{id: clas.id, arm_id:arm.id}" @click="dowloadScoreSheet(clas.id,arm.id, clas.section)">Download Score sheet  [ {{arm.arm_name}}]</a>
                                                    <Link href="/holiday-assessment" v-if="clas.section=='primary'"  class="dropdown-item" :data="{grade: clas.class_name, arm:arm.arm_name}">Holiday Assessment [ {{arm.arm_name}}]</Link>
                                                    <Link href="/affective-disposition" class="dropdown-item" :data="{grade:clas.class_name,currentStudent:0,section:clas.section, arm:arm.arm_name}">Affective disposition [ {{arm.arm_name}}]</Link>
                                                    <Link href="/attendance" class="dropdown-item" :data="{grade:clas.class_name, section: clas.section, arm:arm.arm_name}">Attendance [ {{arm.arm_name}}]</Link>
                                                    <Link href="/physical-development" :data="{grade:clas.class_name, arm:arm.arm_name}" class="dropdown-item">Physical development [ {{arm.arm_name}}]</Link>
                                                    <Link href="/remarks" class="dropdown-item" :data="{grade: clas.class_name, arm:arm.arm_name}">Remarks [ {{arm.arm_name}}]</Link>
                                                    <Link href="/behaviour" class="dropdown-item" :data="{grade: clas.class_name, arm:arm.arm_name}" v-if="clas.section == 'pre nursery'">Behaviour</Link>
                                                    <div class="dropdown-divider"></div>
                                                    <Link href="/add-fee-to-class" class="dropdown-item" :data="{grade: clas.id, arm:arm.id}" >Add fee</Link>
                                                    <div class="dropdown-divider"></div>
                                                    <Link href="/get-result-page" :data="{section: clas.section, grade: clas.class_name, arm: arm.arm_name}" class="dropdown-item">Results</Link>
                                                    <Link href="/mid-term-result" :data="{grade: clas.class_name, arm:arm.arm_name, section:clas.section}" class="dropdown-item">Mid-term Results</Link>
                                                    <div class="dropdown-divider"></div>
                                                    <Link href="/edit-class" :data="{id:clas.id}" class="mr-2 dropdown-item">edit</Link>
                                                    <a @click="deleteClass(clas.class_name, clas.id)" href="#" class="dropdown-item">delete</a>
                                                </div>
                                            </div>
                                        </Link>
                                    </div>
                                </div>
                           </td>
                       </tr>
                    </tbody>
                </table>
                <paginator :links="classes.links"/>
            </div>
            <!-- /.card-body -->
        </div>

        <!--Arms  Modal -->
        <div class="modal fade" id="arms" tabindex="-1" role="dialog" aria-labelledby="armsModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="armsModalLabel">Add Arms Classes</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
               <div>
                   <label for="class">Class</label>
                   <input type="text" v-model="class_name" id="class" class="form-control" readonly>
               </div>
               <div class="mt-3">
                   <label for="arm">Arm</label>
                   <div id="accept-child">
                            <ul class="tags" v-if="all_arms.length > 0" >
                                <li class="tag-item" v-for="(arm, inde) in all_arms" :key="inde"> 
                                    {{arm}}
                                </li>
                            </ul>
                        <input type="text"  v-model="class_arms" autofocus  placeholder="Enter coma seperated list e.g Gold, Silver," class="form-control no-border-input">
                    </div>
               </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" @click.prevent="saveArms">Save</button>
            </div>
            </div>
        </div>
        </div>
    </div>
</template>

<script>
import paginator from '../../Shared/paginator.vue'
import { Inertia } from '@inertiajs/inertia'
import { Link } from '@inertiajs/inertia-vue'
import debounce from 'lodash/debounce';
export default {
  components: { paginator, Link },
    props:{
        classes:Object,
    },
    data(){
        return{
           all_arms:[],
           class_id:null,
           class_arms:null,
           class_name:null,
           old_arms:[],
           isOpen: false,
           query:'',
           allClasses: this.classes
        }
    },
    methods:{
        arms(id, name,arms){
            this.class_id = id
            this.class_name = name
            this.all_arms=[]
            this.class_arms= ""
            this.old_arms=[]
           if(arms.length > 0){
               for(var i=0; i < arms.length; i++){
                   this.all_arms.push(arms[i].arm_name)
                   this.class_arms = this.all_arms.join(', ')
                   this.old_arms.push(arms[i].id)
               }
           }
            $('#arms').modal('show');
        },
        
        saveArms(){
            axios.post('/save-arms',{class_id:this.class_id,arms:this.all_arms, old_arms:this.old_arms}).then((response)=>{
                if(response.data.success){
                    $('#arms').modal('hide');
                    toastr.success('Arms added successfully!', 'Success')
                    Inertia.reload({ only: ['classes'] })
                }
            })
        },
        dowloadScoreSheet(class_id,arm_id, section){
            //this.$inertia.get('/score-sheet',{id:class_id, arm_id:arm_id})
             const url = `/score-sheet?id=${class_id}&arm_id=${arm_id}&section=${section}`;
                window.location.href = url;
        },
        deleteClass(name, id){
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
                            axios.get('/delete-class',{params:{id:id}}).then((response)=>{
                                if(response.data.success){
                                    Inertia.reload({ only: ['classes'] })
                                }
                            })
                        }
                    },
                    cancel: function(){
                        
                    }
                }
            });
        },
        searchClasses(query) {
            axios.get('/search-class', { params: { search: query } })
                .then((response) => {
                    this.allClasses.data = response.data.data
                    this.links = response.data.links
                })
                .catch((error) => {
                    console.error(error); // Handle errors
                });
        }
    
    },
    watch:{
        class_arms:function(){
                this.all_arms=[];
                let myarr = this.class_arms.split(',');
                this.all_arms = myarr.filter(entry => entry.trim() != '');
        },
        query: debounce(function(newVal){
            this.searchClasses(newVal)
        }, 500)
    },
    
}
</script>