<template>
    <div>
        <div class="card px-3 py-4">
            <h4>Filter Students</h4>
            <div class="mt-2 row">
                <div class="col-md-3">
                    <input type="text" name="" placeholder="Search student" class="form-control" id="">
                </div>

                <div class="col-md-3">
                    <select name="" class="form-control" id="">
                        <option value="">Select class</option>
                        <option value=""></option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="" class="form-control" id="">
                        <option value="">Status</option>
                        <option value="paid">Paid</option>
                        <option value="partial">Partial</option>
                        <option value="unpaid">Unpaid</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn py-2" style="border: 1px solid #e4e4e4;">Export to Excel</button>
                </div>
            </div>
        </div>

        <div class="mt-4 card py-4 px-3">
            <h4>Student Fee Records</h4>
            <table class="py-4 table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Total Fees</th>
                        <th>Paid Amount</th>
                        <th>Pending</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Last Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(student, index) in students">
                        <td>{{ index + 1 }}</td>
                        <td>{{ student.fullname }}</td>
                        <td>{{ student.grade+" "+ ((student.arm? student.arm: '')) }}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? formatAmount(student.student_fee[0].total_fee): 0 }}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? formatAmount(student.student_fee[0].total_paid): 0 }}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? (formatAmount(student.student_fee[0].outstanding)): 0 }}</td>
                        <td></td>
                        <td>{{ getStatus(student)}}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? formatDate(student.student_fee[0].updated_at):'' }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
    export default{
        data(){
            return{
                classes: {},
                class: [],
                students:{}
            }
        },
        methods:{
            getStudents(){
                axios.get('/student-fees').then((response)=>{
                    this.classes = response.data.classes
                    this.class = response.data.class
                    this.students = response.data.students
                })
            },
            formatAmount(amount){
                return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
            },
            getStatus(student){
                if(student && student.student_fee && student.student_fee.length > 0){
                    if(student.student_fee[0].outstanding == 0){
                        return "Paid"
                    }
                    if(student.student_fee[0].outstanding !== 0 && student.student_fee[0].total_paid !==0){
                        return "Partial"
                    }
                    if(student.student_fee[0].total_paid == 0){
                        return "Unpaid"
                    }
                }else{
                    return 'Fees not found'
                }
            },
            formatDate(dateString) {
                const date = new Date(dateString);

                const day = date.getDate();
                const monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
                                    "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
                const month = monthNames[date.getMonth()];
                const year = date.getFullYear();

                // Add ordinal suffix
                const getOrdinal = (n) => {
                    if (n > 3 && n < 21) return `${n}th`;
                    switch (n % 10) {
                    case 1: return `${n}st`;
                    case 2: return `${n}nd`;
                    case 3: return `${n}rd`;
                    default: return `${n}th`;
                    }
                };

                return `${getOrdinal(day)} ${month}, ${year}`;
            }

        },
        created(){
            this.getStudents()
        }
    }
</script>