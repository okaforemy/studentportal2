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
        <Head title="Subjects"/>
        <div class="card">
              <div class="card-header">
                <h3 class="card-title">Subjects</h3>

                <div class="card-tools">
                  <div class="input-group input-group-sm" style="width: 450px;">
                    <input type="text" v-model="query" name="table_search" class="form-control float-right" placeholder="Search">
                    <div class="input-group-append">
                      <button type="submit" class="btn btn-default">
                        <i class="fas fa-search"></i>
                      </button>
                    </div>
                    <Link href="/add-subjects" class="ml-4 btn btn-primary">Add subjects</Link>
                  </div>
                   
                </div>
              </div>
              <!-- /.card-header -->
              <div class="card-body  p-0" style="overflow-x: scroll;">
                <table class="table table-responsive table-hover text-nowrap" style="font-size: 14px;">
                  <thead>
                    <tr>
                      <th>ID</th>
                      <th>Subjects</th>
                      <th>Section</th>
                      <th>Category</th>
                     
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr v-for="(subject,index) in allSubjects" :key="index">
                      <td>{{index+1}}</td>
                      <td>{{subject.subject}}</td>
                      <td>{{subject.section}}</td>
                      <td>{{subject.category}}</td>
                
                        <td>
                            <div class="dropdown">
                            <button class="btn dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Options
                            </button>
                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                               
                                <Link class="dropdown-item" :href="'/edit-subject/'+subject.id">Edit</Link>
                                <a class="dropdown-item" @click.prevent="deletesubject(subject.subject, subject.id)" href="/delete-subject" > Delete </a>
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
    props:['subjects'],
    data(){
        return{
           query:'',
           allSubjects: this.subjects?.data,
           links: this.subjects.links
        }
    },
    methods:{
       deletesubject(name,id){
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
                       axios.get('/delete-subject',{params:{id:id}}).then((response)=>{
                        //  console.log(response.data)
                        //    if(response.data){
                        //        Inertia.reload({ only: ['subjects'] })
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
            axios.get('/filter-subjects', { params: { search: query } })
                .then((response) => {
                    this.allsubjects = response.data.data
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
        
    }
    
}
</script>