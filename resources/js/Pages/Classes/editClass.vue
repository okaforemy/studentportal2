<template>
    <div class="pt-3 row">
        <Head title="Edit classes" />
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Edit Class</h3>
                </div>
                <div class="card-body">
                   <form action="" @submit.prevent="submit">
                       <div class="row">
                           <div class="col-md-6">
                               <label for="classname">Class Name <required/></label>
                               <input type="text"  v-model="form.class_name" name="" id="classname" class="form-control">
                               <div v-if="errors.classname" class="text-danger">{{ errors.classname }}</div>
                           </div>

                           <div class="col-md-6">
                               <label for="section">Section <required/></label>
                               <select  name="" v-model="form.section" id="section" class="custom-select">
                                   <option value="">select option</option>
                                   <option value="pre nursery">Pre nursery</option>
                                   <option value="nursery">Nursery</option>
                                   <option value="primary">Primary</option>
                                   <option value="junior secondary">Junior secondary</option>
                                   <option value="senior secondary">Senior secondary</option>
                               </select>
                               <div v-if="errors.section" class="text-danger">{{ errors.section }}</div>
                           </div>
                           <!-- <div class="col-md-6">
                               <label for="classteacher">Class Teacher</label>
                               <select name="" id="classteacher" class="custom-select" v-model="form.teacher_incharge">
                                   <option value="">Select teacher</option>
                                   <option value="mr. emeka">Mr. Emeka</option>
                                   <option value="Mr. Jonah">Mr. Jonah</option>
                                   <option value="Precious">Precious</option>
                               </select>
                               <div v-if="errors.class_teacher" class="text-danger">{{ errors.class_teacher }}</div>
                           </div> -->
                       </div>
                       <div class="row mt-3">
                           <!-- <div class="col-md-6">
                               <label for="capacity">Capacity</label>
                               <input type="text" v-model="form.capacity" name="" id="capacity" class="form-control">
                               <div v-if="errors.capacity" class="text-danger">{{ errors.capacity }}</div>
                           </div> -->
                           
                       </div>
                       <div class="mt-3 text-center">
                           <button class="btn btn-primary">Edit</button>
                       </div>
                   </form>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <classes-side-bar :classes="classes"/>
        </div>
    </div>
</template>

<script>
import { Head } from "@inertiajs/inertia-vue";
import Required from "../../Shared/Required.vue";
import ClassesSideBar from '../../Shared/classesSideBar.vue';

export default {
    components: { Head, Required, ClassesSideBar },
     props:{
           errors: Object,
           classes: Array,
           singleclass: Object,
       },
    data(){
        return{
            form: this.$inertia.form({
                class_name:null,
                teacher_incharge:"",
                capacity:null,
                section:"",
                class_id:this.singleclass.id
            })
        }
    },
    methods:{
        submit(){
            this.$inertia.post('/edit-class', this.form,{onSuccess:()=>{
                this.form ={
                class_name:null,
                teacher_incharge:"",
                capacity:null,
                section:"",
                class_id:null
            }
                   toastr.success('Class edited successfully!', 'Success')
            }})
        }
    },
    mounted(){
        this.form = this.singleclass
    }
   
};
</script>
