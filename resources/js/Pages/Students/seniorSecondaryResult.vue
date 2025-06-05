<style scoped>
    .report_table{
        font-size: 14px;
    }
    .table-sm th, .table-sm td {
        padding: 0.2rem;
    }
    .centered {
    text-align: center; /* Centers horizontally */
    vertical-align: middle; /* Centers vertically */
    height: 100px;
  }
  .centered img {
    display: inline-block;
  }
  #highreport.table th, #highreport.table td {
	padding: 0.45rem;
	vertical-align: top;
	border-top: 1px solid #dee2e6;
   font-size: 14px;
}
.report_table .table th, .report_table .table td {
	padding: 2px;
	vertical-align: top;
	border-top: 1px solid #dee2e6;
}
.delete-subject{
   display: none;
}
.delete-subject-td:hover .delete-subject{
   display: inline-block;
}
</style>
<template>
   <div class="load_content">
   
   <div class="col-md-12">
      <button class="btn btn-primary no-print mt-2 pr-2" style="position:absolute; right: 0;" @click.prevent="rePrint">Re-print result</button>
      <div class="col-md-4 m-auto pt-2 mb-4 no-print">
         <form>
            <div class="text center">
              <select name="" id="" class="form-control" v-model="selectedStudent">
                <option value="">Select student</option>
                <option :value="student.id" v-for="student in students">{{ student.fullname }}</option>
              </select>
            </div>
         </form>
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
      <div class="text-center py-4">
            <img src="/images/PHS.png"  alt="purplins">
      </div>
      <div>
         <h3 class="report_sheet" style="text-align:center">REPORT SHEET</h3>
      </div>
      <div class="">
         <div class="col-md-8" style="margin: 0 auto">
            <table class="table table-bordered table-sm report_table" v-if="student">
               <thead>
                  <tr>
                     <!--	<td colspan="3" style="font-weight: bold"><span class="term"></span> REPORT <span class="session"></span> SESSION</td>-->
                     <td colspan="3" style="font-weight: bold; text-align:center;" v-if="settings">{{settings.session}}, {{settings.term.split("_").join(" ").toUpperCase()}} ASSESSMENT</td>
                  </tr>
               </thead>
               <tbody >
                  <tr>
                     <td>NAME:</td>
                     <td id="h_name">{{student.fullname}}</td>
                     <td rowspan="6" class="centered">
                        <img v-if="student.picture" :src="student.picture.path" alt="student's picture" id="student_img" height="120px">
                     </td>
                  </tr>
                  <tr>
                     <td>AGE:</td>
                     <td id="h_age">{{getAge(student.dob)}}  Years</td>
                  </tr>
                  <tr>
                     <td>SEX:</td>
                     <td id="h_sex" v-if="student.sex">{{student.sex.toUpperCase()}}</td>
                  </tr>
                  <tr>
                     <td>ADMISSION NO.</td>
                     <td id="h_admission_no">{{student.student_id}}</td>
                  </tr>
                  <tr>
                     <td>CLASS:</td>
                     <!-- <td id="h_class">{{student.grade+' '}} {{ student.arm?student.arm:'' }}</td> -->
                      <td v-if="exams && exams.length > 0">{{ exams[0].grade }}</td>
                  </tr>
                  <tr>
                     <td>NEXT TERM BEGINS:</td>
                     <td id="h_next_term" v-if="settings">{{formatDate(settings.resumption).toUpperCase()}}</td>
                  </tr>
               </tbody>
            </table>
         </div>
      </div>
      <div>
         <table class="table table-striped" id="highreport">
            <thead>
               <tr>
                  <th>S/N</th>
                  <th>Subject</th>
                  <th>CA1 (15)</th>
                  <th>CA2 (15)</th>
                  <th>Exam (70)</th>
                  <th>Total</th>
                  <th>Subject Pos.</th>
                  <th>Class AVG</th>
                  <th>Grade</th>
               </tr>
            </thead>
            <tbody id="high_tbody" v-if="student && exams">
               <tr v-for="(exam, index) in exams" :key="index">
                  <td v-html="index+1"></td>
                  <td class="delete-subject-td">{{exam.subject}} 
                     <button @click="deleteSubject(exam.id)" class="btn btn-warning btn-sm delete-subject" style="font-size: 10px; padding: 3px; color: white;">delete</button></td>
                  <td>{{ exam.first_ca }}</td>
                  <td>{{ exam.second_ca }}</td>
                  <td>{{ exam.exam }}</td>
                  <td>{{ exam.total }}</td>
                  <td>{{addOrdinalSuffix(exam.rank)}}</td>
                  <td v-if="class_average">{{ exam.subject_average.toFixed(2) }}</td>
                  <td>{{ exam.remark }}</td>
               </tr>
            </tbody>
         </table>
         <div class="text-center mt-4">
            <span style=" font-weight: bold;">KEY TO GRADES  -  A1: 75 & Above, B2: 70  -  74, B3: 65  -  69, C4: 60  -  64, C5: 55  -  59, C6: 50  -  54, D7: 45  -  49, E8: 40  -  44, F9: 39 & below  </span>
         </div>
         <div class="text-center mt-2 mb-4">
            <h4 class="h_average"><span>Average: </span><span v-if="student_average" class="avg">{{ student_average.toFixed(2) }}</span></h4>
         </div>
         <div class="high_affective_disposition">
            <div class="row">
               <div class="col-md-6 col-lg-6 col-sm-6">
                  <table class="table table-sm table-bordered report-table">
                     <thead>
                        <tr>
                           <th>Affective Disposition</th>
                           <th>Very Good</th>
                           <th>Good</th>
                           <th>Satisfactory</th>
                        </tr>
                     </thead>
                     <tbody class="affective_tbody" v-if="student && student.secondary_affective && student.secondary_affective.length > 0">
                           <tr>
                                        <th>Mental Alertness</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].alertness == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].alertness == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].alertness == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr class="">
                                        <th>Assumes Responsibility</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].responibility == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].responibility == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].responibility == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Takes Care of Personal appearance</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].appearance == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].appearance == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].appearance == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr class="">
                                        <th>Takes care of books and property</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].property == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].property == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].property == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Attentive and works independently</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].independently == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].independently == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].independently == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Does neat work</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].work == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].work == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].work == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                   
                      
                     </tbody>
                  </table>
               </div>
               <div class="col-md-6 col-lg-6 col-sm-6">
                  <table class="table table-sm table-bordered report-table">
                     <thead>
                        <tr>
                           <th>Affective Disposition</th>
                           <th>Very Good</th>
                           <th>Good</th>
                           <th>Satisfactory</th>
                        </tr>
                     </thead>
                     <tbody class="affective_tbody" v-if="student && student.secondary_affective && student.secondary_affective.length > 0">
                        <tr>
                                        <th>Honesty</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].honesty == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].honesty == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].honesty == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th id="team">Team work and cooperation</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].cooperation == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].cooperation == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].cooperation == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Politeness</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].politeness == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].politeness == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].politeness == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Confidence</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].confidence == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].confidence == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].confidence == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th id="speaking">Speaking</th>
                                        <td class="text-center" v-html="student?.secondary_affective[0].speaking == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].speaking == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="student?.secondary_affective[0].speaking == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                        <!-- <tr>
                           <th>Does neat work</th>
                           <td id="h_work1" class="text-center"></td>
                           <td id="h_work2" class="text-center"></td>
                           <td id="h_work3" class="text-center"></td>
                        </tr>
                        <tr>
                           <th>Honesty</th>
                           <td id="h_honesty1" class="text-center"></td>
                           <td id="h_honesty2" class="text-center"><i class="fas fa-check"></i></td>
                           <td id="h_honesty3" class="text-center"></td>
                        </tr>
                        <tr>
                           <th>Team work and cooperation</th>
                           <td id="h_team1" class="text-center"></td>
                           <td id="h_team2" class="text-center"><i class="fas fa-check"></i></td>
                           <td id="h_team3" class="text-center"></td>
                        </tr>
                        <tr>
                           <th>Politeness</th>
                           <td id="h_politeness1" class="text-center"></td>
                           <td id="h_politeness2" class="text-center"><i class="fas fa-check"></i></td>
                           <td id="h_politeness3" class="text-center"></td>
                        </tr>
                        <tr>
                           <th>Confidence</th>
                           <td id="h_confidence1" class="text-center"><i class="fas fa-check"></i></td>
                           <td id="h_confidence2" class="text-center"></td>
                           <td id="h_confidence3" class="text-center"></td>
                        </tr>
                        <tr>
                           <th>Speaking</th>
                           <td id="h_speaking1" class="text-center"></td>
                           <td id="h_speaking2" class="text-center"><i class="fas fa-check"></i></td>
                           <td id="h_speaking3" class="text-center"></td>
                        </tr> -->
                     </tbody>
                  </table>
               </div>
            </div>
         </div>
      </div>
      <div>
         <div class="row">
            <div class="col-md-12">
               <div class="remarks report-table-hide">
                  <div class="row">
                     <div class="col-md-6 col-lg-6 col-sm-6">
                        <p style="font-weight: bold;">Principal's Remark:</p>
                        <p class="principal htremarks pl-4" style="text-decoration: none; font-style: italic;" v-if="student && student.remarks && student.remarks.length > 0">{{ student.remarks[0].HT_remarks }}</p>
                        <p style="font-weight: bold;">Principal's Signature:</p>
                     </div>
                     <div class="col-md-6 col-lg-6 col-sm-6">
                        <p style="font-weight: bold;">Form Teacher's Remark:</p>
                        <p class="form_teacher htremarks pl-4" style="text-decoration: none; font-style: italic;" v-if="student && student.remarks && student.remarks.length > 0">{{ student.remarks[0].CT_remarks }}</p>
                        <p style="font-weight: bold;">Form Teacher's Signature:</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</template>

<script>
    export default{
        props: ['section','students', 'student_id'],

        data(){
            return{
                selectedStudent:"",
                student: [],
                class_average:0,
                total_marks_obtainable:0,
                total_marks_obtained:0,
                overall_percentage:0,
                settings:null,
                exams:[],
                class_average: 0,
                student_average: 0,
                result_id:1,
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
                    this.settings = response.data.settings
                    this.exams = response.data.exams
                    this.student_average = response.data.student_average
                    this.class_average = response.data.class_average
                })
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
            deleteSubject(id){
               axios.get('/delete-senior-secondary-exam-subject',{params:{id:id}}).then((response)=>{
                  if(response.data.message){
                     this.getResult()
                  }
               })
            },
            getRemark(total){
                let remark = ""
                if (total >= 75) {
                remark = "A1";
                } else if (total >= 70) {
                    remark = "B2";
                } else if (total >= 65) {
                    remark = "B3";
                } else if (total >= 60) {
                    remark = "C4";
                } else if (total >= 55) {
                    remark = "C5";
                } else if (total >= 50) {
                    remark = "C6";
                } else if (total >= 45) {
                    remark = "D7";
                } else if (total >= 40) {
                    remark = "E8";
                } else {
                    remark = "F9";
                }
                return remark
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
             formatDate(dateString) {
                const date = new Date(dateString);
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
             addOrdinalSuffix(number) {
                if (typeof number !== 'number') return number; // Ensure it's a number

                let suffix = "th"; // Default suffix

                if (number % 100 < 11 || number % 100 > 13) { // Handle exceptions like 11th, 12th, 13th
                    switch (number % 10) {
                        case 1: suffix = "st"; break;
                        case 2: suffix = "nd"; break;
                        case 3: suffix = "rd"; break;
                    }
                }

                return number + suffix;
            }

        },
        watch:{
            selectedStudent(newVal, oldVal){
                this.getResult()
            },
            student_id: {
                handler(newVal) {
                    this.selectedStudent = newVal
                    this.getResult()
                },
                //deep: true,
                immediate: true
            }
        }
    }
</script>