<template>
    <div>
        <div class="card px-3 py-4">
            <h4>Filter Students</h4>
            <div class="mt-2 row">
                <div class="col-md-3">
                    <input type="text" v-model="search" name="" placeholder="Search student" class="form-control" id="">
                </div>

                <div class="col-md-3">
                    <select name="" @change="getSelectedClass" class="form-control" id="" v-model="current_grade">
                        <option value="">Select class</option>
                        <option :value="grade.id" v-for="grade in classes">{{ grade.class_name }}</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="" v-model="status" class="form-control" id="">
                        <option value="">Status</option>
                        <option value="Paid">Paid</option>
                        <option value="Partial">Partial</option>
                        <option value="Unpaid">Unpaid</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <button class="btn py-2" @click="exportToExcel" style="border: 1px solid #e4e4e4;">Export to Excel</button>
                </div>
            </div>
        </div>

        <div class="mt-4 card py-4 px-3">
            <h4>Student Fee Records</h4>
            <table class="py-4 table table-responsive-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Student</th>
                        <th>Class</th>
                        <th>Total Fees</th>
                        <th>Paid Amount</th>
                        <th>Pending</th>
                        <th>Progress</th>
                        <th style="width: 20%;">Status</th>
                        <th>Last Payment</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(student, index) in filteredStudent">
                        <td>{{ index + 1 }}</td>
                        <td>{{ student.fullname }}</td>
                        <td>{{ student.grade+" "+ ((student.arm? student.arm: '')) }}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? formatAmount(student.student_fee[0].total_fee): 0 }}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? formatAmount(student.student_fee[0].total_paid): 0 }}</td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? (formatAmount(student.student_fee[0].outstanding)): 0 }}</td>
                        <td></td>
                        <td v-html="getStatus(student)"></td>
                        <td>{{ (student.student_fee && student.student_fee.length > 0)? formatDate(student.student_fee[0].updated_at):'' }}</td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script>
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';
    export default{
        data(){
            return{
                classes: {},
                class: [],
                students:{},
                current_grade: '',
                status: '',
                search:''
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
                    if(student.student_fee[0].status == 'Paid'){
                        return `<span class="bg-success py-1 px-2 rounded"><i class="far fa-check-circle"></i> Paid</span>`
                    }
                    if(student.student_fee[0].status == 'Partial'){
                        return `<span class="bg-warning py-1 px-2 rounded"><i class="fas fa-exclamation-triangle"></i> Partial</span>`
                    }
                    if(student.student_fee[0].status == 'Unpaid'){
                        return `<span class="bg-danger py-1 px-2 rounded"><i class="fas fa-exclamation-triangle"></i> Unpaid</span>`
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
            },
            getSelectedClass(){
                axios.get('/student-fees?class_id='+this.current_grade).then((response)=>{
                    this.classes = response.data.classes
                    this.class = response.data.class
                    this.students = response.data.students
                })
            },
             async exportToExcel() {
                const workbook = new ExcelJS.Workbook();
                const worksheet = workbook.addWorksheet("Students");

                // Define headers
                worksheet.columns = [
                    { header: "Students", key: "students", width: 50 },
                    { header: "Class", key: "class", width: 30 },
                    { header: "Total Fees", key: "total_fee", width: 10 },
                    { header: "Total Paid", key: "total_paid", width: 10 },
                    { header: "Pending", key: "pending", width: 10 },
                    { header: "status", key: "status", width: 10 },
                    { header: "Last Payment Date", key: "last_payment", width: 20 },
                ];

                // Add data rows
                this.filteredStudent.forEach(student => {
                    worksheet.addRow({
                    students: student.fullname,
                    class: student.grade+" "+(student.arm? student.arm: ''),
                    total_fee: (student.student_fee && student.student_fee.length > 0)? student.student_fee[0].total_fee: 0,
                    total_paid: (student.student_fee && student.student_fee.length > 0)? student.student_fee[0].total_paid: 0,
                    pending: (student.student_fee && student.student_fee.length > 0)? student.student_fee[0].outstanding: 0,
                    status: (student.student_fee && student.student_fee.length > 0)? student.student_fee[0].status: '',
                    last_payment: (student.student_fee && student.student_fee.length > 0)? this.formatDate(student.student_fee[0].updated_at): '',
                    });
                });

                // Optionally style header row
                worksheet.getRow(1).font = { bold: true };

                // Create Excel buffer
                const buffer = await workbook.xlsx.writeBuffer();

                // Save as file
                const blob = new Blob([buffer], {
                    type:
                    "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                });
                saveAs(blob, "students.xlsx");
                },

        },
        computed:{
            filteredStudent(){
                if(this.status =='' && this.search ==''){
                    return this.students
                }else{
                   // if(this.search !==''){
                       return this.students.filter(item => {
                            const matchesSearch = this.search === '' || (
                            item.fullname?.toLowerCase().includes(this.search.toLowerCase()) ||
                            item.student_id?.toLowerCase().includes(this.search.toLowerCase())
                            );

                            const matchesStatus = this.status === '' || (
                            item.student_fee.some(fee => fee.status === this.status)
                            );

                            return matchesSearch && matchesStatus;
                        });
                   // }
                    //return this.students.filter((item)=> item.student_fee.some(fee=>fee.status == this.status))
                }
            }
        },
        watch:{
            class(newVal){
                this.current_grade = newVal.id
            },
            
        },
        created(){
            this.getStudents()
        }
    }
</script>