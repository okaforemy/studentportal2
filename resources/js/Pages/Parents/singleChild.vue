<template>
    <div>
        <div class="row px-2 py-3">
          <div class="col-md-3">

            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img v-if="student.picture" class="profile-user-img img-fluid img-circle" :src="student.picture.path" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{student.fullname}}</h3>

                <p class="text-muted text-center">{{student.grade}} {{ student.arm? student.arm: '' }}</p>

                <ul class="list-group list-group-unbordered mb-3">
                  <li class="list-group-item">
                    <b>ID</b> <a class="float-right">{{ student.student_id }}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Sex</b> <a class="float-right" style="text-transform: capitalize;">{{student.sex}}</a>
                  </li>
                  <li class="list-group-item">
                    <b>Date of birth</b> <a class="float-right">{{ student.dob }}</a>
                  </li>
                </ul>

              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->

           
          </div>
          <!-- /.col -->
          <div class="col-md-9">
            <div class="card">
              <div class="card-header p-2">
                <ul class="nav nav-pills">
                  <li class="nav-item"><a class="nav-link active" href="#result" data-toggle="tab">Check Result</a></li>
                  <li class="nav-item"><a class="nav-link" href="#fees" data-toggle="tab">Check Fees</a></li>
                  <li class="nav-item"><a class="nav-link " href="#payments" data-toggle="tab">Payments</a></li>
                </ul>
              </div><!-- /.card-header -->
              <div class="card-body">
                <div class="tab-content">
                  <div class="tab-pane active" id="result">
                    <div class="row mb-4">
                        <div class="col-md-4 mt-2">
                            <select v-model="session" name="" id="" class="custom-select">
                                <option value="">Select session</option>
                                <option v-for="session in sessions" :value="session">{{session}}</option>
                            </select>
                        </div>
                        <div class="col-md-4 mt-2">
                            <select name="" v-model="term" class="custom-select" id="">
                                <option value="">Select terms</option>
                                <option value="first_term">First term</option>
                                <option value="second_term">Second term</option>
                                <option value="third_term">Third term</option>
                            </select>
                        </div>
                        <div class="mt-2">
                            <button class="btn btn-primary px-2" @click.prevent="checkResult">Check</button>
                        </div>
                    </div>

                    <table class="table table-sm mt-4" v-if="result_check">
                      <thead class="mt-4">
                        <tr>
                          <th>#</th>
                          <th>Term</th>
                          <th>Session</th>
                          <th></th>
                        </tr>
                      </thead>
                      <tbody>
                        <tr v-if="result_check.result && result_check.result > 0">
                          <td>1</td>
                          <td>{{ result_check.term.split('_').join(' ') }}</td>
                          <td>{{ result_check.session }}</td>
                          <td>
                            <button class="btn btn-primary" @click="getResult">Download result</button>
                          </td>
                        </tr>
                        <tr v-if="result_check.result == 0">
                          <td colspan="4">No Result found</td>
                        </tr>
                      </tbody>
                    </table>
                    
                  </div>
                  <!-- /.tab-pane -->
                  <div class="tab-pane" id="fees">
                   <table class="table">
                        <thead>
                            <th>#</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Discount</th>
                        </thead>
                        <tbody v-if="fees">
                            <tr v-for="(fee, index) in fees">
                                <td>{{ index+1 }}</td>
                                <td>{{ fee.description }}</td>
                                <th>{{ fee.is_optional ==1? 'Optional':'Mandatory' }}</th>
                                <td>{{ formatAmount(fee.amount, fee.pivot?.discount) }}</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-6">
                                            {{ fee.pivot?.discount }}
                                        </div>
                                       
                                    </div>
                                </td>
                               
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td style="font-size: 16px; font-weight: bold;" colspan="2">TOTAL</td>
                                <td style="font-size: 16px; font-weight: bold;">{{ totalStudentFees() }} </td>
                                <td></td>
                                <td></td>
                             
                            </tr>
                        </tfoot>
                    </table>
                  </div>
                  <!-- /.tab-pane -->

                  <div class="tab-pane " id="payments">
                      <table class="table table-striped mt-4">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Amount</th>
                                <th>Description</th>
                                <th>Payment method</th>
                                <th>Receipt No.</th>
                                <th>Date</th>
                                <th>Term</th>
                                <th>Session</th>
                                <th><span data-toggle="modal" data-target="#viewReciept" class="text-success"
                                        style="cursor: pointer;" @click="setCurrentreceiptTransaction">VIEW RECEIPT</span></th>
                            </tr>
                        </thead>
                        <tbody v-if="transactions">
                            <tr v-for="(trans, index) in transactions">
                                <td>{{ index + 1 }}</td>
                                <td>{{ formatAmount(trans.amount) }}</td>
                                <td>{{ trans?.description }}</td>
                                <td style="text-transform: capitalize;">{{ trans.payment_method }}</td>
                                <td>{{ trans?.receipt_no }}</td>
                                <td>{{ formatDate(trans.updated_at) }}</td>
                                <td>{{ trans.term.split('_').join(' ') }}</td>
                                <td>{{ trans.session }}</td>
                                <td><span data-toggle="modal" data-target="#viewReciept" class="text-success"
                                        style="cursor: pointer;" @click="setCurrentTransaction(trans)">View receipt</span></td>
                            </tr>
                        </tbody>
                    </table>
                    <Paginator v-if="links && links.length > 0" :links="links" />
                  </div>
                  <!-- /.tab-pane -->
                </div>
                <!-- /.tab-content -->
              </div><!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
        </div>
         <div style="z-index: -99; position: relative;">
          <!-- <div> -->
           <div ref="preNurserysection" >
            <pre-nursery-result
              :section="result_check.section"
              :result="result"
              :student="student"
              :prenurseryexam="prenurseryexam"
              v-if="result && result_check.section === 'pre nursery'" 
            />
          </div>
          <div ref="primarySection">
            <primary-result
              v-if="result && (result_check.section === 'nursery' || result_check.section ==='primary')" 
              :student="currentStudent"
              :settings="settings"
              :total_marks_obtainable="total_marks_obtainable.toFixed(2)"
              :total_marks_obtained="total_marks_obtained.toFixed(2)"
              :overall_percentage="overall_percentage.toFixed(2)"
              :class_average="class_average.toFixed(2)"
              :section="result_check.section"
            />
          </div>
          <div ref="juniorSecondarySection">
            <junior-secondary-result  v-if="result && result_check.section == 'junior secondary'"
            :student="currentStudent"
            :class_average="class_average"
            :total_marks_obtainable="total_marks_obtainable"
            :total_marks_obtained="total_marks_obtained"
            :overall_percentage="overall_percentage"
            :settings="settings"
            :exams="exams"
            :student_average="student_average"
            />
          </div>
          <div ref="seniorSecondarySection">
            <senior-secondary-result  v-if="result && result_check.section == 'senior secondary'"
            :student="currentStudent"
            :class_average="class_average"
            :total_marks_obtainable="total_marks_obtainable"
            :total_marks_obtained="total_marks_obtained"
            :overall_percentage="overall_percentage"
            :settings="settings"
            :exams="exams"
            :student_average="student_average"
            />
          </div>
        </div>
      
        <!-- reciept modal -->
         <div class="modal fade" id="viewReciept" tabindex="-1" aria-labelledby="viewRecieptLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="viewRecieptLabel">New message</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row justify-content-center">
                            <div class="col-12 col-lg-12" >
                                <div class="card shadow-lg" ref="receiptContent">
                                    <div class="card-body p-5">
                                        <!-- Header -->
                                        <div class="text-center border-bottom border-primary border-3 pb-4 mb-4">
                                            <div
                                                class=" mx-auto mb-3 d-flex align-items-center justify-content-center">
                                                    <img src="/images/lion_final.png" alt="" width="120">
                                            </div>
                                            <h1 class="fs-2 fw-bold text-dark mb-2">PURPLINS HIGH SCHOOL</h1>
                                            <p class="text-muted mb-2">No. 2, Purplins School Road, Shebwo-kpma 2,</p>
                                            <div class="small text-secondary">
                                                <p class="mb-1">Behind City College of Education Off Old karu road, Mararaba Nasarawa State
                                                </p>
                                                <p class="mb-1">Phone: 08160000010, 08026184476 | Email:
                                                    finance@springfieldacademy.edu</p>
                                                <!-- <p class="mb-0">Website: purplinsschool.com | Registration No:
                                                    EDU-2024-001</p> -->
                                            </div>
                                        </div>

                                        <!-- Receipt Title -->
                                        <div class="text-center my-4">
                                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                            
                                                <h2 class=" text-dark mb-0" style="font-size: 22px; font-weight: bold;">SCHOOL FEES RECEIPT</h2>
                                            </div>
                                            <div class="receipt-underline bg-primary mx-auto"></div>
                                        </div>

                                        <!-- Receipt and Student Information -->
                                        <div class="row mb-4 print-row">
                                            <div class="col-md-6 col-lg-6 col-sm-12 col-left">
                                                <div class="bg-light p-4 rounded">
                                                    <h5 class="fw-semibold text-dark mb-3">Receipt Information</h5>
                                                    <div class="small">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Receipt No:</span>
                                                            <span class="fw-medium">{{currentTransaction[0]?.receipt_no}}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Date:</span>
                                                            <span class="fw-medium">{{formatDate(currentTransaction[0]?.updated_at)}}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Academic Year:</span>
                                                            <span class="fw-medium">{{currentTransaction[0]?.session}}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Term:</span>
                                                            <span style="text-transform: capitalize;" class="fw-medium" v-if="currentTransaction && currentTransaction.length > 0">{{currentTransaction[0].term.split('_').join(" ")}}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Payment Method:</span>
                                                            <span style="text-transform: capitalize;" class="fw-medium">{{currentTransaction[0]?.payment_method}}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6 col-lg-6 col-sm-12 col-right">
                                                <div class="bg-light bg-opacity-10 p-4 rounded">
                                                    <h5 class="fw-semibold text-dark mb-3">Student Information</h5>
                                                    <div class="small">
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Student Name:</span>
                                                            <span class="fw-medium">{{student?.fullname}}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between mb-2">
                                                            <span class="text-muted">Student ID:</span>
                                                            <span class="fw-medium">{{student?.student_id}}</span>
                                                        </div>
                                                        <div class="d-flex justify-content-between">
                                                            <span class="text-muted">Class:</span>
                                                            <span class="fw-medium">{{student?.grade}} {{ student?.arm }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Fee Details -->
                                        <div class="mb-4">
                                            <h5 class=" mb-3 text-center" style="font-weight: bold;">Fee Details</h5>
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-striped">
                                                    <thead class="table-light">
                                                        <tr>
                                                            <th class="text-start fw-semibold">S.No</th>
                                                            <th class="text-start fw-semibold">Fee Description</th>
                                                            <th class="text-end fw-semibold">Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody v-if="currentTransaction">
                                                        <tr v-for="(trans, index) in currentTransaction">
                                                            <td class="text-center">{{ index + 1 }}</td>
                                                            <td>{{trans.description}}</td>
                                                            <td class="text-end fw-medium">{{ formatAmount(trans.amount) }}</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- Summary -->
                                            <div class="bg-light rounded p-4 mt-3">
                                                <div class="row">
                                                    <div class="col-12">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center fs-5 mb-2">
                                                            <span class="fw-semibold text-dark">Total Fees:</span>
                                                            <span class="fw-bold text-dark">{{ fees && fees.total_fee ? formatAmount(fees?.total_fee) :
                                                                totalStudentFees()}}
                                                            </span>
                                                        </div>
                                                        <div
                                                            class="d-flex justify-content-between align-items-center fs-5 border-top pt-2 mb-2">
                                                            <span class="fw-semibold text-dark">Amount Paid:</span>
                                                            <span class="fw-bold text-success">{{totalReceiptPaymemt()}}</span>
                                                        </div>
                                                        <div v-if="fee_sumary && fee_sumary.credit > 0"
                                                            class="d-flex justify-content-between align-items-center fs-5 border-top pt-2 mb-2">
                                                            <span class="fw-semibold text-dark" >Credit:</span>
                                                            <span class="fw-bold text-success">{{(fee_sumary && fee_sumary.credit)?formatAmount(fee_sumary.credit): 0}}</span>
                                                        </div>
                                                        <div
                                                            class="d-flex justify-content-between align-items-center fs-4 border-top border-primary border-2 pt-3">
                                                            <span class="fw-bold text-dark">Balance:</span>
                                                            <span class="fw-bold text-success fs-4">{{ (fee_sumary && fee_sumary.outstanding)? formatAmount(fee_sumary.outstanding): 0 }}</span>
                                                        </div>
                                                        <div class="text-center mt-3" v-if="fee_sumary && fee_sumary.outstanding == 0">
                                                            <span class="badge bg-success fs-6 px-3 py-2">✓ FULLY
                                                                PAID</span>
                                                        </div>
                                                        <div class="text-center mt-3" v-else>
                                                            <span class="badge bg-info fs-6 px-3 py-2">✓
                                                                PART PAYMENT</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Footer -->
                                        <div class="mt-4 pt-4 border-top">
                                            <div class="row">
                                               
                                                <div class="col-md-6 text-end">
                                                    <div class="mb-4">
                                                        <p class="small text-muted mb-1">The Proprietor:</p>
                                                        
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Print Button -->
                                        <div class="text-center mt-4 no-print">
                                            <button @click="printReceipt"
                                                class="btn btn-primary px-4 py-2 print-hidden">
                                                Print Receipt
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
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
import { TimeScale } from 'chart.js'
import preNurseryResult from '../../Components/preNurseryResult.vue';
import primaryResult from '../../Components/primaryResult.vue';
import juniorSecondaryResult from '../../Components/juniorSecondaryResult.vue';
import seniorSecondaryResult from '../../Components/seniorSecondaryResult.vue';
import Paginator from '../../Shared/paginator.vue';
//import html2pdf from 'html2pdf.js'
import jsPDF from 'jspdf'
import html2canvas from 'html2canvas'


    export default{
        props: ['student'],
        components: {preNurseryResult, primaryResult, Paginator, juniorSecondaryResult, seniorSecondaryResult},
        data(){
            return{
                sessions: [],
                result_check: [],
                term: '',
                session:'',
                result: null,
                prenurseryexam: null,
                settings: null,
                currentStudent: null,
                class_average: 0,
                overall_percentage: 0,
                total_marks_obtainable: 0,
                total_marks_obtained: 0,
                exams:null,
                student_average: 0,
                fees:[],
                discount :{},
                transactions: [],
                currentTransaction:[],
                links: '',
                fee_sumary: []
            }
        },
        methods:{
          checkResult(){
            if(!this.term && !this.session){
              return false;
            }
            axios.get('/checkresult', {
              params:{
                student_id: this.student.id, 
                class_id: this.student.class_id,
                term: this.term,
                session: this.session
              }
              })
              .then((response)=>{
                console.log(response)
                this.result_check = response.data
              })
          },
          formatAmount(amount,discount=0){
           return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount-discount);
        },

         setCurrentTransaction(trans){
            this.currentTransaction = []
            this.currentTransaction.push(trans)
        },
        setCurrentreceiptTransaction(){
            this.currentTransaction = this.transactions
        },

        totalReceiptPaymemt() {
            let total_paid = 0;
            if (this.currentTransaction) {
                for (let bal of this.currentTransaction) {
                    total_paid += parseFloat(bal.amount);
                }
            }
            this.total_paid = total_paid;
            return this.formatAmount(total_paid);
        },

        formatDate(val) {
            const date = new Date(val);

            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formatted = date.toLocaleDateString('en-GB', options);

            // Add ordinal suffix to the day
            function addOrdinal(n) {
                const s = ["th", "st", "nd", "rd"],
                    v = n % 100;
                return n + (s[(v - 20) % 10] || s[v] || s[0]);
            }

            const day = addOrdinal(date.getDate());
            const finalOutput = `${day} ${date.toLocaleString('default', { month: 'long' })}, ${date.getFullYear()}`;
            return finalOutput;
        },
        printReceipt() {
            const printContents = this.$refs.receiptContent.innerHTML;
            const originalContents = document.body.innerHTML;

            document.body.innerHTML = printContents;
            window.print();
            document.body.innerHTML = originalContents;
            window.location.reload(); // Refresh the page to restore Vue reactivity
        },

        totalStudentFees(){
            let total = 0;
            let discount = 0;
            for(let fee of this.fees){
                total +=fee.amount
                discount += fee.pivot?.discount
            }
            return this.formatAmount(total, discount)
        },
          getResult() {
            axios.get('/get-result', { params: { student_id: this.student.id, 
                section: this.result_check.section, term: this.result_check.term, session: this.result_check.session, is_reprint: true } }).then((response) => {
                this.result = response?.data
                this.prenurseryexam = response.data?.prenurseryexam
                this.currentStudent = response.data?.student
                this.overall_percentage = response.data?.overall_percentage
                this.class_average = response.data?.class_average
                this.total_marks_obtainable = response.data?.total_marks_obtainable
                this.total_marks_obtained = response.data?.total_marks_obtained
                this.settings = response.data?.settings
                this.exams = response.data?.exams
                this.student_average = response.data?.student_average
                // this.student = response.data.student
                // this.picture = response.data.picture
               
                // this.settings = response.data.settings
                // this.class_avg = ((this.result.class_avg / 20) * 100).toFixed(2)
                 this.$nextTick(() => {
                  this.downloadPDF()
                })
            })
        },

        getFees(){
          
          axios.get('/parent/fees', {params:{student_id: this.student.id}}).then((response)=>{
            this.fees = response.data
          })
        },

        getTransactions(){
           axios.get('/parent/transactions', {params:{student_id: this.student.id}}).then((response)=>{
            this.transactions = response.data.transactions
            this.fee_sumary = response.data.fee_sumary
          })
        },

        async downloadPDF() {
          let element = null;

          if (this.result_check.section === 'pre nursery') {
            element = this.$refs.preNurserysection;
          } else if (
            this.result_check.section === 'nursery' ||
            this.result_check.section === 'primary'
          ) {
            element = this.$refs.primarySection;
          }else if(this.result_check.section == 'junior secondary'){
            element = this.$refs.juniorSecondarySection
          }else if(this.result_check.section == 'senior secondary'){
            element = this.$refs.seniorSecondarySection
          }

          if (!element) {
            console.warn("No matching section found.");
            return;
          }

          await this.$nextTick(); // ensure DOM is painted

          try {
            const canvas = await html2canvas(element, {
              useCORS: true,
              scale: 2,
              allowTaint: true,
              backgroundColor: '#fff',
            });

            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');

            const marginX = 5;
            const marginY = 10;
            const pageWidth = pdf.internal.pageSize.getWidth();
            const pageHeight = pdf.internal.pageSize.getHeight();

            const imgWidth = pageWidth - marginX * 2;
            const imgHeight = (canvas.height * imgWidth) / canvas.width;

            // If height overflows, you can paginate (optional)
            if (imgHeight > pageHeight - marginY * 2) {
              // TODO: paginate if needed
            }

            pdf.addImage(imgData, 'PNG', marginX, marginY, imgWidth, imgHeight);
            pdf.save(`${this.student.fullname}-result.pdf`);
          } catch (e) {
            console.error("PDF generation error:", e);
          }
        }

        },
        created(){
            const currentYear = new Date().getFullYear() -1
            for (let i = 0; i < 20; i++) {
              const start = currentYear + i
              const end = start + 1
              this.sessions.push(`${start}/${end}`)
            }

            this.getFees()
            this.getTransactions()
        }
    }
</script>