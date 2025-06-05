<style>
    body{
        font-size: 14px;
    }
</style>
<style scoped>
   .report_table .table th, .table td {
        padding: 0.15rem;
    }

    .img-thumbnail {
        padding: 0.25rem;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 0.25rem;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.075);
        max-width: 100%;
        height: 160px;
    }

    .centered {
    text-align: center; /* Centers horizontally */
    vertical-align: middle; /* Centers vertically */
    height: 100px;
  }
  .centered img {
    display: inline-block;
  }
</style>
<template>
    <div>
        <div class="col-md-12">
            <div class="pt-3 mb-4 no-print">
                 <button class="btn btn-primary no-print mt-2 pr-2" style="position:absolute; right: 0;" @click.prevent="rePrint">Re-print result</button>
                <div class="row">
                       
                       <div class="col no-print col-md-5 m-auto">
                           <select class="custom-select custom-select_modified" v-model="selectedStudent" name="pupil">
                                <option value="">Select Pupil</option>
                                <option :value="student.id" v-for="(student, index) in students" :key="index">{{student.fullname}}</option>
                           </select>
                       </div>
                   </div>
                   <div class="col-md-6 pt-4 mx-auto no-print" v-if="is_reprint">
                <div class="row">
                    <div class="col-md-6">
                        <select class="form-control" v-model="term" id="">
                            <option value="">Select term</option>
                            <option value="first_term">First term</option>
                            <option value="second_term">Second term</option>
                            <option value="third_term">Third term</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <input type="text" v-model="session" name="" class="form-control" placeholder="Session" id="">
                    </div>
                </div>
            </div>
            </div>
            <div class="col-md-12" style="text-align: center; padding-top: 15px; padding-bottom: 25px">
                <img src="/images/purplins-school2.png" alt="purplins high school">
            </div>
            <div>
                <h3 class="report_sheet text-center">REPORT SHEET</h3>
            </div>
            <div class="">
                <div class="col-md-8 col-sm-8 col-lg-8" style="margin: 0 auto">
                    <table class="table table-bordered table-sm report_table" v-if="student">
                        <thead>
                            <tr>
                                <!--	<th colspan="2" class="text-center" style="text-transform: uppercase;">First Term REPORT 2021/2022 SESSION</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>NAME:</td>
                                <td id="names">{{student.fullname}}</td>
                                <td rowspan="6" class="centered">
                                    <div class="" id="studentimage"><img v-if="student.picture" :src="student.picture.path"
                                            height="40px" class="img-thumbnail"></div>
                                </td>
                            </tr>
                            <tr>
                                <td>AGE:</td>
                                <td id="age" v-if="student.dob">{{getAge(student.dob)}} Year(s)</td>
                            </tr>
                            <tr>
                                <td>SEX:</td>
                                <td id="sex" v-if="student.sex">{{student.sex.toUpperCase()}}</td>
                            </tr>
                            <tr>
                                <td>ADMISSION NO.</td>
                                <td id="adm">{{student.student_id}}</td>
                            </tr>
                            <tr>
                                <td>CLASS:</td>
                                <!-- <td id="class">{{student.grade}} <span v-if="student.arm">{{ student.arm }}</span></td> -->
                                  <td id="class" v-if="student && student.primary_exam && student.primary_exam.length > 0">{{student?.primary_exam[0]?.grade}} {{student?.primary_exam[0]?.arm}} </td>
                            </tr>
                            <tr>
                                <td>NEXT TERM BEGINS:</td>
                                <td id="next_term" v-if="settings">{{ formatDate(settings.resumption)}}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="col-md-12 mt-4 stop-break">
                    <div class="row">
                        <div class="col-md-5 col-sm-5 col-lg-5 print-float-left">
                            <table class="table table-sm table-bordered report-table">
                                <thead>
                                    <tr>
                                        <th>Affective Disposition</th>
                                        <th>Very Good</th>
                                        <th>Good</th>
                                        <th>Satisfactory</th>
                                    </tr>
                                </thead>
                                <tbody class="affective_tbody" v-if="student && student.primary_affective && student.primary_affective.length > 0">
                                    <tr>
                                        <th>Mental Alertness</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].alertness == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].alertness == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].alertness == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr class="report-table-hide">
                                        <th>Assumes Responsibility</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].responsibility == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].responsibility == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].responsibility == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Takes Care of Personal appearance</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].appearance == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].appearance == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].appearance == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr class="report-table-hide">
                                        <th>Takes care of books and property</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].property == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].property == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].property == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Attentive and works independently</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].independently == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].independently == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].independently == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Does neat work</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].work == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].work == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].work == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Honesty</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].honesty == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].honesty == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].honesty == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th id="team">Team work and cooperation</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].cooperation == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].cooperation == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].cooperation == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Politeness</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].politeness == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].politeness == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].politeness == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Confidence</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].confidence == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].confidence == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].confidence == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th id="speaking">Speaking</th>
                                        <td class="text-center" v-html="student?.primary_affective[0].speaking == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].speaking == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.primary_affective[0].speaking == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                </tbody>
                            </table>
                           
                            <!-- physical development table for pre_nursery---------->
                            <table class="table table-sm table-bordered mt-2 report-table" v-if="student.physicaldevelopment">
                                <thead>
                                    <tr>
                                        <th>Physical Development</th>
                                        <th>At the begining of the term</th>
                                        <th>At the end of the term</th>
                                    </tr>
                                </thead>
                                <tbody class="physical_dev_tbody">
                                    <tr>
                                        <th>Current Height</th>
                                        <td class="height_beg">{{ student.physicaldevelopment.initial_height }}</td>
                                        <td class="height_end">{{ student.physicaldevelopment.current_height }}</td>
                                    </tr>
                                    <tr>
                                        <th>Current Weight</th>
                                        <td class="weight_beg">{{ student.physicaldevelopment.initial_weight }}</td>
                                        <td class="weight_end">{{ student.physicaldevelopment.current_weight }}</td>
                                    </tr>
                                </tbody>
                            </table>
                          
                            <!------- attendance table ------------->
                            <table class="table table-sm table-bordered report-table" v-if="student && student.attendance">
                                <thead>
                                    <tr style="font-size: 12px">
                                        <th>No. of Times School Opened</th>
                                        <th>No. of Times Present</th>
                                        <th>No. of Times Absent</th>
                                        <th>No. of Times Late</th>
                                        <th>No. of Times Absent for Morning Drills</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="attendance_table_tr">
                                        <td class="times_opened">{{ student.attendance.no_of_times_school_opened }}</td>
                                        <td class="times_present">{{ student.attendance.no_of_times_present }}</td>
                                        <td class="times_absent">{{ student.attendance.no_of_times_absent }}</td>
                                        <td class="times_late">{{ student.attendance.no_of_times_late }}</td>
                                        <td class="times_absent_drill">{{ student.attendance.no_of_times_absent_from_drills }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-7 col-sm-7 col-lg-7 print-float-right">
                            <!------------- this is the report card table------------>
                            <table class="table table-sm table-bordered report-table report-table-hide ">
                                <thead>
                                    <tr>
                                        <th>Subjects</th>
                                        <th>1ST C.A.T 20</th>
                                        <th>2ND C.A.T 20</th>
                                        <th>EXAM</th>
                                        <th>TOTAL 100%</th>
                                        <th>GRADE</th>
                                    </tr>
                                </thead>
                                <tbody id="report_tbody" v-if="student && student.primary_exam && student.primary_exam.length > 0">
                                    <tr v-for="exam in student.primary_exam">
                                        <td>{{ exam.subject }}</td>
                                        <td>{{ exam?.first_ca }}</td>
                                        <td>{{ exam?.second_ca }}</td>
                                        <td>{{ exam?.exam }}</td>
                                        <td>{{ parseFloat(exam.total).toFixed(2) }}</td>
                                        <td>{{ exam.remark }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-----------total marks table------------->
                            <table class="table table-bordered table-sm report-table report-table-hide" style="font-size: 12px">
                                <tbody>
                                    <tr>
                                        <th>TOTAL MARKS OBTAINABLE</th>
                                        <td id="marks_obtainable">{{total_marks_obtainable}}</td>
                                        <th>TOTAL MARKS OBTAINED</th>
                                        <td id="marks_obtained">{{total_marks_obtained}}</td>
                                    </tr>
                                    <tr>
                                        <th>OVERALL PERCENTAGE</th>
                                        <td id="overall_percent">{{overall_percentage}}</td>
                                        <th>CLASS AVERAGE PERCENTAGE</th>
                                        <td id="class_average">{{class_average}}</td>
                                    </tr>
                                    <tr>
                                        <th>OVERALL GRADE</th>
                                        <td colspan="3" id="overall_grade" class="text-center font-weight-bold">{{ getRemark(overall_percentage) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div style="margin-bottom: 10px; margin-top: 20px; text-align: center"
                    class="hide-pre_nursery-affective">
                    <span style=" font-weight: bold;">
                        KEY TO GRADES - A+: 90 - 100, A: 80 - 89, B+: 70 - 79, B: 60 - 69, C: 50 - 59, D: 40 - 49, E: 0
                        - 39
                    </span>
                </div>
            </div>
            <div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="remarks report-table-hide">
                            <div class="row">
                                <div class="col-md-6">
                                    <p style="font-weight:bold">Head Teacher's Remark:</p>
                                    <p class="htremarks pl-4" v-if="student && student.remarks">{{ student?.remarks[0]?.HT_remarks }}</p>
                                    <p style="font-weight:bold">Head Teacher's Signature:</p>
                                </div>
                                <div class="col-md-6">
                                    <p style="font-weight:bold">Class Teacher's Remark:</p>
                                    <p class="remarks_com pl-4" v-if="student && student.remarks">{{ student?.remarks[0]?.CT_remarks }}</p>
                                    <p style="font-weight:bold">Class Teacher's Signature:</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pre_nursery_table col-md-12 stop-break">
                    <div class="col-md-12 row  load-nursery-report stop-break" style="display: none;">
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
<script>
    export default{
        props: ['section','students'],

        data(){
            return{
                selectedStudent:"",
                student: [],
                class_average:0,
                total_marks_obtainable:0,
                total_marks_obtained:0,
                overall_percentage:0,
                settings: [],
                is_reprint: false,
                term:'',
                session:''
            }
        },

        methods:{
            rePrint(){
                this.is_reprint = !this.is_reprint
            },
            getResult(){
                axios.get('/get-result', {params:{
                    student_id: this.selectedStudent,
                    section: this.section,
                    term: this.term, 
                    session: this.session, 
                    is_reprint: this.is_reprint
                }}).then((response)=>{
                    this.student = response.data.student
                    this.class_average = response.data.class_average.toFixed(2)
                    this.total_marks_obtainable = response.data.total_marks_obtainable.toFixed(2)
                    this.total_marks_obtained = response.data.total_marks_obtained.toFixed(2)
                    this.overall_percentage = response.data.overall_percentage.toFixed(2)
                    this.settings = response.data.settings
                })
            },
            formatDate(dateString) {
                const date = new Date(dateString);

                if (isNaN(date.getTime())) {
                    // Invalid date
                    return 'Invalid Date';
                }

                const options = { year: 'numeric', month: 'long', day: 'numeric' };

                // Format the date
                let formattedDate = new Intl.DateTimeFormat('en-US', options).format(date);

                // Add the ordinal suffix (st, nd, rd, th)
                formattedDate = formattedDate.replace(/\d+/, (day) => {
                    const suffixes = ['th', 'st', 'nd', 'rd'];
                    const v = day % 100;
                    return day + (suffixes[(v - 20) % 10] || suffixes[v] || suffixes[0]);
                });

                return formattedDate;
            },

             getAge(birthDate) {
                const birth = new Date(birthDate);
                const today = new Date();
                
                let age = today.getFullYear() - birth.getFullYear();
                let monthDiff = today.getMonth() - birth.getMonth();
                let dayDiff = today.getDate() - birth.getDate();

                // Adjust age if the birthdate hasn't occurred yet this year
                if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                    age--;
                }

                return age;
            },
        
            getRemark(total){
                let remark = ""
                if(total >= 90){
                    remark = "A+"
                }else if(total <=89.98 && total >=79.99){
                    remark = "A"
                }else if(total <= 79.98 && total >= 69.99){
                    remark = "B+"
                }else if(total <=69.98 && total >=59.99){
                    remark = "B"
                }else if(total <=59.98 && total >= 49.99){
                remark = "C"
                }else if(total <=49.98 && total >= 39.99){
                    remark = "D"
                }else if(total <= 39.98){
                    remark = "E"
                }
                return remark
            }
        },
        watch:{
            selectedStudent(newVal, oldVal){
                this.getResult()
            }
        }
    }
</script>