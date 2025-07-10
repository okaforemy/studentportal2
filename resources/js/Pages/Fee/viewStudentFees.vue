<style scoped>
    /* Styles for printing only specific content */
@media print {
  .no-print {
    display: none !important;
  }

  .print-only {
    display: block !important;
  }


  .print-only, .print-only * {
    visibility: visible;
  }

  .print-only {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
  }

   .col-left {
    float: left;
    width: 50% !important;
  }

  .col-right {
    float: right;
    width: 50% !important;
  }

  .print-row {
    width: 100%;
    overflow: hidden;
    display: block !important;
  }
}

</style>
<template>
    <div class="pt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    VIEW FEES FOR <span style="font-weight: bold;">{{ student.fullname }}</span>, Class:
                    {{ student.grade }}
                    <span v-if="student.arm">{{ student.arm }}</span>
                </h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-gradient-info">
                            <span class="info-box-icon"
                                ><i class="fas fa-money-bill-wave-alt"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Total Fees</span>
                                <span class="info-box-number">{{fee_sumary && fee_sumary.total_fee? formatAmount(fee_sumary?.total_fee): totalStudentFees()}}</span>

                                <div class="progress">
                                    <div
                                        class="progress-bar"
                                        style="width: 100%"
                                    ></div>
                                </div>
                               
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-gradient-success">
                            <span class="info-box-icon"
                                ><i class="fas fa-money-bill-wave-alt"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Total Paid</span>
                                <span class="info-box-number">{{ totalPaid() }}</span>

                                <div class="progress">
                                    <div v-if="fee_sumary && fee_sumary.total_paid"
                                        class="progress-bar"
                                        :style="'width:'+((fee_sumary.total_paid/fee_sumary.total_fee) * 100)+ '%'"
                                    ></div>
                                </div>
                               
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-gradient-warning">
                            <span class="info-box-icon"
                                ><i class="fas fa-money-bill-wave-alt"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Outstanding</span>
                                <span class="info-box-number">{{fee_sumary && fee_sumary.outstanding? formatAmount(fee_sumary?.outstanding): formatAmount(0)}}</span>

                                <div class="progress">
                                    <div v-if="fee_sumary && fee_sumary.outstanding"
                                        class="progress-bar"
                                        :style="'width:'+(((fee_sumary.outstanding)/fee_sumary.total_fee) * 100)+ '%'"
                                    ></div>
                                    <div v-else
                                        class="progress-bar"
                                        :style="'width:0%'"
                                    ></div>
                                </div>
                                
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-3 col-sm-6 col-12">
                        <div class="info-box bg-gradient-danger">
                            <span class="info-box-icon"
                                ><i class="fas fa-money-bill-wave-alt"></i></span>

                            <div class="info-box-content">
                                <span class="info-box-text">Credit</span>
                                <span class="info-box-number">{{fee_sumary && fee_sumary.credit? formatAmount(fee_sumary?.credit): formatAmount(0)}}</span>

                                <div class="progress">
                                    <div
                                        class="progress-bar"
                                        :style="'width:'+(fee_sumary && fee_sumary.credit? ((fee_sumary.credit)/fee_sumary.total_fee) * 100 : 0)+ '%'"
                                    ></div>
                                </div>
                              
                            </div>
                            <!-- /.info-box-content -->
                        </div>
                        <!-- /.info-box -->
                    </div>
                    <!-- /.col -->
                </div>

                <!-- table for fees -->
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
                    <tbody v-if="allTransactions">
                        <tr v-for="(trans, index) in allTransactions">
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
        </div>

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
                                                            <span class="fw-semibold text-dark">Credit:</span>
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
import Paginator from '../../Shared/paginator.vue'

export default {
    components: { Paginator },
    props: ["student", "fees", 'transactions', 'fee_sumary'],
    data() {
        return {
            allTransactions: [],
            links: '',
            currentTransaction: []
        };
    },
    methods: {
        totalStudentFees() {
            let total = 0;
            for (let fee of this.fees) {
                total += fee.amount
            }
            return this.formatAmount(total)
        },
        setCurrentTransaction(trans){
            this.currentTransaction = []
            this.currentTransaction.push(trans)
        },
        setCurrentreceiptTransaction(){
            this.currentTransaction = this.allTransactions
        },
        formatAmount(amount) {
            return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
        },

        totalPaid() {
            let total_paid = 0;
            if (this.allTransactions) {
                for (let bal of this.allTransactions) {
                    total_paid += parseFloat(bal.amount);
                }
            }
            this.total_paid = total_paid;
            return this.formatAmount(total_paid);
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
        }

    },

    watch: {
        transactins: {
            handler(val) {
                this.allTransactions = this.transactions?.data;
                this.links = this.transactions?.links
            },
            immediate: true,
        }
    }
};
</script>
