<template>
    <div>
        <div class="col-md-12">
            <div class="mx-auto py-4 col-md-8">
                <div class="row">
                    <div class="col-md-6">
                        <select name="" v-model="grade" id="" class="form-control" @change="getStudents">
                            <option value="">Select grade</option>
                            <option v-for="(gr, index) in grades" :key="index" :value="gr.class_name">{{ gr.class_name }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <!-- <select name="" v-model="student_id" id="" class="form-control" @change="getResult">
                            <option value="">Select student</option>
                            <option v-for="(student, index) in students" :key="index" :value="student.student_id">{{ student.lastname+" "+student.firstname }}</option>
                        </select> -->
                        <select name="" v-model="subject" id="" class="form-control" @change="getSubjectsResult">
                            <option value="">Select subject</option>
                            <option v-for="(subject, index) in subjects" :key="index" :value="subject.id">{{ subject.subject }}</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="col-md-12">
                <!-- <table class="table">
                    <thead>
                        <tr>
                            <th>S/N</th>
                            <th>Subject</th>
                            <th>Mark obtainable</th>
                            <th>Mark obtained</th>
                            <th>{{ selected_section=='junior_secondary'? '(15)':'(50)' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(result, index) in results" :key="index">
                            <td>{{ index + 1 }}</td>
                            <td>{{ result && result.subject? result.subject.subject: '' }}</td>
                            <td>{{result.marks_obtainable}}</td>
                            <td>{{result.marks_obtained}}</td>
                            <td>{{ selected_section == 'junior_secondary'? parseInt(result.marks_obtained)/4 : (parseInt(result.marks_obtained)/2) }}</td>
                        </tr>
                    </tbody>
                </table> -->

                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Full name</th>
                            <th>Mark obtainable</th>
                            <th>Mark obtained</th>
                            <th>{{ selected_section=='junior_secondary'? '(15)':'(50)' }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(student, index) in studentSubjects" :key="index">
                            <td>{{ student && student.name? student.name.lastname +" "+ student.name.firstname:''}}</td>
                            <td>{{ student.mark_obtainable }}</td>
                            <td>{{ student.score }}</td>
                            <td>{{ selected_section == 'junior_secondary'? parseInt(student.score)/4 : (parseInt(student.score)/2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['grades'],
    data(){
        return{
            grade:'',
            students:[],
            student_id:'',
            results: [],
            selected_section: '',
            selected_grade:[],
            subjects: [],
            subject:'',
            studentSubjects:[]
        }
    },
    methods:{
        getStudents(){
            axios.get('/students-in-a-class',{params:{grade:this.grade}}).then((response)=>{
                this.students = response.data
                this.selectedGrade()
            })
        },

        getResult(){
            let data = {student_id:this.student_id}
            axios.post('/results',data).then((response)=>{
                this.results = response.data;
            })
        },

        getSubjectsResult(){
            axios.get('/student-results-by-subjects',{params:{subject:this.subject, grade: this.grade}}).then((response)=>{
                this.studentSubjects = response.data
            })
        },

        selectedGrade(){
          let grade=  this.grades.find((val)=>{
                return val.class_name == this.grade;
            })
            if(grade){
                this.selected_section = grade.section
                this.selected_grade = grade
                this.subjects = JSON.parse(this.selected_grade.subjects)
            }else{
                this.selected_section = ''
            }
            
        }
    },
    watch:{
        grade(){
            this.selectedGrade()
        }
    }
}
</script>