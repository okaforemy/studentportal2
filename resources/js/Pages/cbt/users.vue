<template>
    <div>
        <div class="card">
            <div class="card-header">
                <span>CBT Users</span>
            </div>
            <div class="card-body">
                <div v-for="(student, index) in students" :key="index"><h4 class="text-center font-weight-bold">{{ index }}</h4>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Names</th>
                                <th>ID</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr v-for="(stu, ind) in student" :key="ind">
                            <td>{{ stu.lastname+" "+stu.firstname }}</td>
                            <td>{{ stu.student_id }}</td>
                            <th>
                                <button class="btn btn-sm" @click.prevent="deleteStudent(stu.id)"><i class="fas fa-trash text-danger"></i></button>
                                <a href="" @click.prevent="openModal(stu)"><i class="fas fa-file"></i></a>
                            </th>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal -->
        <div class="modal fade" id="resultModal" tabindex="-1" aria-labelledby="resultModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalLabel">Result for <span ref="student_name" class="font-weight-bold"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="col-md-4 mx-auto">
                    <select name="" class="form-control" id="" v-model="selected_subject" @change="setResult">
                        <option value="">Select subjects</option>
                        <option v-for="(key, ind) in keys" :key="ind" :value="key">{{ key }}</option>
                    </select>
                </div>

                <div class="mt-4">
                    <table class="table table-hover table-sm table-responsive" v-if="result && result.length > 0">
                        <thead>
                            <tr>
                                <th>S/N</th>
                                <th>question</th>
                                <th>Option A</th>
                                <th>Option B</th>
                                <th>Option C</th>
                                <th>Option D</th>
                                <th v-if="result.option_e">Option E</th>
                                <th>Correct answer</th>
                                <th>Your answer</th>
                                <th>Score</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(re, ind) in result" :key="ind" :class="{ 'text-success' :re.your_answer==re.correct_answer, 'text-danger': re.your_answer!=re.correct_answer}">
                                <td>{{ ind+1 }}</td>
                                <td>{{ re.question }}</td>
                                <td>{{ re.option_a }}</td>
                                <td>{{ re.option_b }}</td>
                                <td>{{ re.option_c }}</td>
                                <td>{{ re.option_d }}</td>
                                <td v-if="result.option_e">{{ re.option_e }}</td>
                                <td>{{ re.correct_answer }}</td>
                                <td>{{ re.your_answer }}</td>
                                <td><h5>{{ re.your_answer==re.correct_answer? "2": "0" }}</h5></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="8"><h4>Total score</h4></td>
                                <td><h4>{{ total_score }}</h4></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
            </div>
        </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['students'],
    data(){
        return{
            results: [],
            keys: [],
            selected_subject:'',
            result:[],
            total_score: 0,
        }
    },
    methods:{
        deleteStudent(id){
            let userResponse = confirm('Do you want to proceed?');
            if(userResponse){
                this.$inertia.get('/delete-cbt-student',{id:id})
            }
            
        },
        openModal(student){
            this.results = [];
            this.keys = [],
            this.selected_subject ="";
            this.result= [];
            this.total_score = 0

            axios.get('/cbt-results',{params:{student_id: student.student_id}}).then((response)=>{
                this.results = response.data.results;
                this.keys = response.data.keys
            })
            $('#resultModal').modal('show')
            this.$refs['student_name'].innerHTML = student.firstname+" "+student.lastname
        },
        setResult(){
            this.result = this.results[this.selected_subject];
            let score = 0;
            for(let i=0; i < this.result.length; i++){
                if(this.result[i].your_answer == this.result[i].correct_answer){
                    score += 2;
                }
            }
            this.total_score = score
        },

        disposeModal(){
            $('#resultModal').modal('disponse')
        }
    }
}
</script>