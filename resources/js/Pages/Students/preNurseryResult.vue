<style>
@media print {
    .no-print {
        display: none;
    }
}

.report_table tr {
    line-height: 1;
}
</style>
<template>
    <div class="col-md-12" style="font-size: 0.9rem;">
        <div class="mx-auto no-print">
            <div class="col-md-4 pt-4 mx-auto">
                <label for="">Students:</label>
                <select name="" id="" v-model="student_id" @change="getResult" class="form-control"
                    v-if="students && students.length > 0">
                    <option v-for="student in students" :value="student.id">{{ student.fullname }}</option>
                </select>
            </div>
        </div>


        <div class="col-md-12">

            <div class="col-md-12" style="text-align: center; padding-top: 15px; padding-bottom: 25px">
                <img src="/images/purplins-school.png" alt="purplins high school">
            </div>
            <div class="text-center">
                <h3>REPORT SHEET</h3>
            </div>
            <div class="">
                <div class="col-md-8 col-lg-8 col-sm-8" style="margin: 0 auto">
                    <table class="table table-bordered table-sm report_table reducedTable" v-if="result && result.student">
                        <thead>
                            <tr>
                                <!--	<th colspan="2" class="text-center" style="text-transform: uppercase;">First Term REPORT 2024/2025 SESSION</th> -->
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>NAME:</td>
                                <td id="names">{{ result.student.fullname }}</td>
                                <td rowspan="6">
                                    <div class="text-center" id="studentimage" v-if="student.picture">
                                        <img :src="student.picture.path" style="height: 135px;" class="img-thumbnail">
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>AGE:</td>
                                <td id="age">{{ calculateAge(result.student.dob) }} Year(s)</td>
                            </tr>
                            <tr>
                                <td>SEX:</td>
                                <td id="sex">{{result.student.sex}}</td>
                            </tr>
                            <tr>
                                <td>ADMISSION NO.</td>
                                <td id="adm">{{result.student.student_id}}</td>
                            </tr>
                            <tr>
                                <td>CLASS:</td>
                                <td id="class">{{result.student.grade}}</td>
                            </tr>
                            <tr>
                                <td>NEXT TERM BEGINS:</td>
                                <td id="next_term" v-if="settings">{{ formatDate(settings.resumption).toUpperCase() }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div>
                <div class="col-md-12 mt-4 stop-break">
                    <div class="row">
                        <div class="col-md-5 col-sm-5 col-lg-5 print-float-left">
                            
                            <!-- Pre nursery comment tables --->
                            <div class="pre_nursery_remarks report-table-hide-p " style="">
                                <table class="table table-bordered table-sm report-table-hide-p reducedTable"
                                    style="font-size: 12px;">
                                    <tbody v-if="student && student.behaviour">
                                        <tr>
                                            <th>BEHAVIOUR AND ATTITUDE TO LEARNING</th>
                                            <td id="behaviour">{{student.behaviour.behaviour}}</td>
                                        </tr>
                                        <tr>
                                            <th>HOMEWORK</th>
                                            <td id="homework">{{ student.behaviour.homework }}</td>
                                        </tr>
                                        <tr v-if="student && student.remarks && student.remarks.length > 0">
                                            <th>CLASS TEARCHER'S REMARK</th>
                                            <td class="remarks_com">{{ student.remarks[0].CT_remarks }}</td>
                                        </tr>
                                        <tr>
                                            <th>CLASS TEACHER'S SIGNATURE</th>
                                            <td></td>
                                        </tr>
                                        <tr v-if="student && student.remarks && student.remarks.length > 0">
                                            <th>HEAD TEACHER'S REMARK</th>
                                            <td class="htremarks"> {{ student.remarks[0].HT_remarks }}</td>
                                        </tr>
                                        <tr>
                                            <th>HEAD TEACHER'S SIGNATURE</th>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <!-- physical development table for pre_nursery---------->
                            <table
                                class="table table-sm table-bordered mt-2 report-table report-table-hide-p reducedTable"
                                style="">
                                <thead>
                                    <tr>
                                        <th>Physical Development</th>
                                        <th>At the begining of the term</th>
                                        <th>At the end of the term</th>
                                    </tr>
                                </thead>
                                <tbody class="physical_dev_tbody" v-if="student && student.physicaldevelopment">
                                    <tr>
                                        <th>Current Height</th>
                                        <td class="height_beg">{{ student.physicaldevelopment.initial_height }} cm</td>
                                        <td class="height_end">{{student.physicaldevelopment.current_height}} cm</td>
                                    </tr>
                                    <tr>
                                        <th>Current Weight</th>
                                        <td class="weight_beg">{{ student.physicaldevelopment.initial_weight }} kg</td>
                                        <td class="weight_end">{{student.physicaldevelopment.current_weight}} kg</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>
                        <div class="col-md-7 col-sm-7 col-lg-7 print-float-right">
                         
                            <!------- attendance table for pre_nursery ------------->

                            <table class="table table-sm table-bordered report-table report-table-hide-p reducedTable"
                                style="">
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
                                    <tr class="attendance_table_tr" v-if="student && student.attendance">
                                        <td class="times_opened">{{student.attendance.no_of_times_school_opened}}</td>
                                        <td class="times_present">{{student.attendance.no_of_times_present}}</td>
                                        <td class="times_absent">{{ student.attendance.no_of_times_absent }}</td>
                                        <td class="times_late">{{ student.attendance.no_of_times_late }}</td>
                                        <td class="times_absent_drill">{{student.attendance.no_of_times_absent_from_drills}}</td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- pre nursery afffective disposition -->
                            <table
                                class="table table-sm table-bordered report-table mt-2 hide-primary-affective reducedTable"
                                style="" v-if="result && result.student && result.student.pre_nursery_affective">
                                <thead>
                                    <tr>
                                        <th>Affective Disposition</th>
                                        <th>Very Good</th>
                                        <th>Good</th>
                                        <th>Satisfactory</th>
                                    </tr>
                                </thead>
                                <tbody class="affective_tbody">
                                    <tr>
                                        <th>Mental Alertness</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.alertness == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.alertness == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.alertness == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                   
                                    <tr>
                                        <th>Takes Care of Personal appearance</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.appearance == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.appearance == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.appearance == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                   
                                    <tr>
                                        <th>Attentive and works independently</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.independently == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.independently == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.independently == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Does neat work</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.work == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.work == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.work == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Honesty</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.honesty == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.honesty == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.honesty == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th id="team">Relationship with peers</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.peers == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.peers == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.peers == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr id="speaking-tr">
                                        <th id="speaking">Leadership</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.leadership == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.leadership == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.leadership == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Politeness</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.politeness == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.politeness == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.politeness == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    <tr>
                                        <th>Confidence</th>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.confidence == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.confidence == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td class="text-center" v-html="result.student.pre_nursery_affective.confidence == 3 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                    
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>

            </div>
            <div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="remarks report-table-hide" style="display: none;">
                            <div class="row">
                                <div class="col-md-6 col-sm-6 col-lg-6">
                                    <p>Head Teacher's Remark:</p>
                                    <p class="htremarks" style="text-decoration: none"></p>
                                    <p>Head Teacher's Signature:</p>
                                </div>
                                <div class="col-md-6 col-sm-6 col-lg-6">
                                    <p>Class Teacher's Remark:</p>
                                    <p class="remarks_com" style="text-decoration: none">Shindara is a delightful pupil
                                        who participates actively in classroom activities. She can count numbers 0-20
                                        orally, identify numbers 0-5 and sound s, a, t, i, p, n perfectly. More so, she
                                        needs to be encouraged in her tracing skills, pencil grip and improvement on her
                                        identification of numbers. Have a wonderful Christmas celebration!</p>
                                    <p>Class Teacher's Signature:</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="pre_nursery_table col-md-12 stop-break">

                    <div class="col-md-12 row  load-nursery-report stop-break" style="" v-if="prenurseryexam">
                        <div class="col-md-6 col-sm-6 col-lg-6" style="font-size:14px;" v-for="(nursery, id) in prenurseryexam" :key="id">
                            <table class="table table-bordered table-sm reducedTable">
                                <thead>
                                    <tr>
                                        <th class="pre_nursery_header">{{id}}</th>
                                        <th width="60px">Mastered Concept</th>
                                        <th width="50px">NEEDs Work</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    <tr v-for="nur in nursery">
                                        <td>{{nur.subject}}</td>
                                        <td v-html="nur.value == 2 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                        <td v-html="nur.value == 1 ? '<i class=\'fas fa-check\'></i>' : ''"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>
<script>
export default {
    props: ['section', 'students', 'studentid'],
    data() {
        return {
            result: [],
            student_id: "",
            student: [],
            prenurseryexam: [],
            class_avg: 0,
            picture: [],
            settings:[]

        }
    },
    methods: {
        getResult() {
            axios.get('/get-result', { params: { student_id: this.student_id, section: this.section } }).then((response) => {
                this.result = response.data
                this.prenurseryexam = response.data.prenurseryexam
                this.student = response.data.student
                this.picture = response.data.picture
                //this.calculatePercentage();
                this.settings = response.data.settings
                this.class_avg = ((this.result.class_avg / 20) * 100).toFixed(2)
            })
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
        calculateAge(dob) {
            const birthDate = new Date(dob);
            const today = new Date();

            let age = today.getFullYear() - birthDate.getFullYear();
            const monthDiff = today.getMonth() - birthDate.getMonth();
            const dayDiff = today.getDate() - birthDate.getDate();

            // Adjust if birth date hasn't occurred yet in the current year
            if (monthDiff < 0 || (monthDiff === 0 && dayDiff < 0)) {
                age--;
            }

            return age;
        }
    },
    watch:{
            studentid: {
                handler(newVal) {
                    this.student_id = newVal
                    this.getResult()
                },
                //deep: true,
                immediate: true
            }
        }
}
</script>