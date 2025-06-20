<style scoped>
 .list-group{
   min-height: 40px;
   background: #fff;
 }

 .move{
   cursor: move;
 }

 .show{
  display: inline-block;
 }

 .hide{
  display: none;
 }

 .same-line{
  display: inline-flex;
  width: 100%;
 }

 .form-control, .custom-select {
    height: calc(2.15rem + 1px) !important;
    font-size: 1.1rem !important;
}

.hidden{
  display: none;
}

.show{
  display: block;
}
 
</style>
<template>
    <div>
        <div class="row pb-4">
            <div class="col-md-10 col-sm-12 col-lg-8 offset-md-1">
              <h4 class="text-center pt-2 pb-4">Assign subjects to {{grade}} {{sectionStudentsTitle}}</h4>
               <ul class="list-group">
                <li class="list-group-item  d-flex justify-content-between align-items-center" v-for="(subj, index) in selectedsubjects" :key="index"
                draggable="true"
                @dragstart="onDragStart($event, index)"
                @dragover.prevent
                @drop="onDrop($event, index)"
                > 
                  {{subj.subject}} <span class="pl-2" v-if="subj.category && section == 'pre nursery'">{{ subj.category }}</span>
                  <div v-if="section=='primary'">
                    <div class="same-line">
                        <label for="">Holiday Assessment</label>
                        <input type="checkbox" name="holidayassessment" :ref="'holiday_'+index" :checked="isChecked(subj)" @click="selectSubject(subj, $event, true,index)">
                        <input :value="maxScoreValue(subj)" :ref="'max_score'+index" type="text" @keyup="isNumber($event,subj)" name="max_score" :id="'max_score'+index" class="form-control ml-1 col-md-4 max-score" placeholder="max score">
                    </div>
                        
                  </div>
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" @click="selectSubject(subj, $event, false,index)" :checked="isSelected(subj.id, index)" :id="'customSwitch'+index">
                    <label class="custom-control-label" :for="'customSwitch'+index"></label>
                  </div>
                  <span class="badge badge-primary badge-pill"></span>
                </li>
              </ul>

              <ul class="list-group mt-2">
                <li class="list-group-item  d-flex justify-content-between align-items-center" v-for="(subj, index) in filteredSubject" :key="index"
                > 
                  {{subj.subject}}  <span class="pl-2" v-if="subj.category && section == 'pre nursery'">{{ subj.category }}</span>
                  <span class="text-danger" @click="deleteSubject(subj)" style="cursor: pointer; display: inline; position: absolute; right: 70px;"> <i class="fas fa-trash"></i></span>
                  
                 
                  <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" @click="selectSubject2(subj, $event,index)" :id="'customSwitchs'+index">
                    <label class="custom-control-label" :for="'customSwitchs'+index"></label>
                  </div>
                  <span class="badge badge-primary badge-pill"></span>
                </li>
              </ul>
              <div class="text-center mt-4">
                    <span v-if="allsubjects.length <= 0" class="d-block mt-2 mb-2 font-italic">
                      <span class="font-weight-bold">Subjects not found!</span> please click the Add subjects button to add the subjects for the intended class
                    </span>
                    <Link href="/assign-subjects" v-if="allsubjects.length > 0" method="post" class="btn btn-primary" :data="{subjects: selectedsubjects, grade: grade, arm:arm}" :class="{'disabled':selectedsubjects.length==0}" :disabled="selectedsubjects.length == 0">Save Subjects</Link>
                    <Link href="/add-subjects" v-else class="btn btn-primary">Add Subjects</Link>
              </div>
            </div>

            <div class="col-md-6 col-sm-6">
             
            </div>
        </div>
    </div>
</template>

<script>
import { Inertia } from '@inertiajs/inertia'
import { Link } from '@inertiajs/inertia-vue'
//import draggable from "vuedraggable/src/vuedraggable";
export default {
    props:['subjects','grade','arm','selected_subj'],
    components:{
         Link
    },
    data(){
        return{
            allsubjects: this.subjects,
            selectedsubjects: [],
           // grade:"",
            section:"",
            sectionStudentsTitle:"",
            selectedCheckbox:[],
            draggedIndex: null
        }
    },
    methods: {
      onDragStart(event, index){
        this.draggedIndex = index
      },
      onDrop(event, index){
        const item = this.selectedsubjects.splice(this.draggedIndex, 1)[0]
        this.selectedsubjects.splice(index, 0, item)
      },

      deleteSubject(subject){
        const that = this
        $.confirm({
            title: 'Delete!',
            content: 'Do you want to delete '+subject.subject+"?",
            type: 'red',
            buttons: {   
                ok: {
                    text: "ok!",
                    btnClass: 'btn-primary',
                    keys: ['enter'],
                    action: function(){
                       axios.get('/delete-subject', {params:{id: subject.id}}).then((response)=>{
                        that.$inertia.reload({only:['subjects', 'selected_subj']})
                       })
                    }
                },
                cancel: function(){
                      
                }
            }
        });
      },
    // isSelected(subject){
    //   let subjects = Object.values(this.selected_subj);
    //  if(this.selected_subj && this.selected_subj.length > 0){
    //     let index = this.selected_subj.findIndex(val => val.subject === subject)
    //     if(index >= 0){
    //       return true;
    //     }else{
    //       return false
    //     }
    //  }
    // },
    isSelected(id) {
      if(this.selectedsubjects){
          let found = this.selectedsubjects.findIndex((item)=>item.id == id);
        if(found >= 0){
          return true
        }else{
          return false
        }
      }
    },

    maxScoreValue(subject){
      let found = this.selectedsubjects.find(val => val.id ==subject.id)

      if(found){
        if(found.pivot && found.pivot.max_score){
          let index = this.selectedsubjects.findIndex(element => element.id == subject.id)
          this.selectedsubjects[index].max_score = found.pivot.max_score
          return found.pivot.max_score;
        }else{
          return ''
        }
      }else{
        return ''
      }
    },

  
    isChecked(subject) {
    // Convert to an array if selected_subj is an object
    // let subjects = Array.isArray(this.selected_subj) 
    //     ? this.selected_subj 
    //     : Object.values(this.selected_subj);

    // if (subjects.length > 0) {
    //     let index = subjects.findIndex(val => val.id === subject.id && val.holiday === true);
    //     return index >= 0;
    // }

    // return false;

    //return true;
    let val = this.selectedsubjects.find((item)=>item.id === subject.id)
    
    if(val){
      if(val.pivot && val.pivot.is_holiday){
          let index = this.selectedsubjects.findIndex(element => element.id == subject.id)
          this.selectedsubjects[index].holiday = val.pivot.is_holiday
        return true
      }else{
        return false
      }
    }else{
      return false
    }
},

    maxScore(index){
      this.selectedsubjects[index].max_score = this.$refs['max_score'+index][0].value
    },

    isNumber(evt, subj){
            const charcode = evt.which? evt.which: evt.keyCode;
            this.setValues(evt, subj)
            if(charcode > 31 && (charcode < 48 || charcode > 57) && charcode !=46){
                evt.preventDefault();
            }
        },
    
    setValues(event, subj){
      let index = this.selectedsubjects.findIndex(element => element.id == subj.id)
      this.selectedsubjects[index].max_score = event.target.value
    },

    selectSubject(subj,event,holiday,ind){
      let index = this.selectedsubjects.findIndex(element => element.id == subj.id)
      
      if(holiday===true){
        if(index >=0){
          
         if(this.$refs['holiday_'+ind][0].checked){
            this.selectedsubjects[index].holiday = true;
            //this.$refs['max_score'+index][0].style.display = "inline-block"
          }else{
            delete this.selectedsubjects[index].holiday
           // this.$refs['max_score'+index][0].style.display = "none"
            this.selectedsubjects[index].max_score = 0
          }
        }
      }else{
        if(event.target.checked){
          if(index == -1){
            this.selectedsubjects.push(subj);
          }
        }else{
        if(index != -1){
          this.selectedsubjects.splice(index,1);
        }
        }
      }
      
    },

    selectSubject2(subj, event, ind) {
      if (event.target.checked) {
        const alreadySelected = this.selectedsubjects.some(s => s.id === subj.id);
        if (!alreadySelected) {
          const val = this.filteredSubject.find(el => el.id === subj.id);
          if (val) this.selectedsubjects.push(val);
        }
        event.target.checked = false
      } else {
        // Unchecked: remove it
        this.selectedsubjects = this.selectedsubjects.filter(s => s.id !== subj.id);
      }
    }

  
  },
  computed:{
    filteredSubject() {
      return this.subjects.filter(item => {
        return !this.selectedsubjects.some(sel => sel.id === item.id)
      });
    }
  },
  created(){
    let params = new URL(location.href).searchParams;
    let grade = params.get('grade');
    let section = params.get('section');
    this.grade = grade;
    this.section = section;
    if(section =="pre_nursery" || section =="nursery" || section =="primary"){
      this.sectionStudentsTitle = "Pupils"
    }else{
      this.sectionStudentsTitle = "Students"
    }

    if(this.selected_subj===null){
      this.selectedsubjects = [];
    }else{
      //console.log(this.selected_subj)
      //this.selectedsubjects = this.selected_subj;
      for(let subj in this.selected_subj){
        this.selectedsubjects.push(this.selected_subj[subj])
      }
    }
  }
}
</script>