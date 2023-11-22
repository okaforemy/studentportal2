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
                    <select name="" class="form-control" id="">
                        <option value="">Select subjects</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
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
            $('#resultModal').modal('show')
            this.$refs['student_name'].innerHTML = student.firstname+" "+student.lastname
        }
    }
}
</script>