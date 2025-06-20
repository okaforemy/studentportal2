<template>
    <div class="pt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    MAKE PAYMENT FOR <span style="font-weight: bold;">{{ student.fullname }}</span>, Class:
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

                <div class="row mt-4">
                    <div class="col-md-2">
                        <label for="">Payment Method</label>
                        <select name="payment_method" v-model="form.payment_method" class="form-control" id="">
                            <option value="bank">Bank</option>
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="cheque">Cheque</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="">Amount</label>
                        <input type="number" v-model="form.amount" name="" class="form-control" id="">
                    </div>
                    <div class="col-md-6">
                        <label for="">Description</label>
                        <input type="text" name="" v-model="form.description" class="form-control" id="">
                    </div>
                    <div class="col-md-2 mt-2">
                        <button @click.prevent="makeTransaction" class="btn btn-primary mt-4">Make payment</button>
                    </div>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody v-if="student.transactions">
                        <tr v-for="(trans, index) in student.transactions">
                            <td>{{ index + 1 }}</td>
                            <td>{{ formatAmount(trans.amount) }}</td>
                            <td>{{ trans?.description }}</td>
                            <td style="text-transform: capitalize;">{{ trans.payment_method }}</td>
                            <td>{{ trans?.receipt_no }}</td>
                            <td>{{ formatDate(trans.updated_at) }}</td>
                            <td>{{ trans.term.split('_').join(' ') }}</td>
                            <td>{{ trans.session }}</td>
                            <td><span class="text-danger" style="cursor: pointer;" @click="removeTransactionEntry(trans.id)">Remove entry</span></td>
                        </tr>
                    </tbody>
                 </table>
            </div>
        </div>
    </div>
</template>

<script>
    export default{
        props: ['student', 'fees', 'fee_sumary'],
        data(){
            return {
                form: this.$inertia.form({
                    amount: '',
                    description: '',
                    student_id: '',
                    payment_method: 'bank'
                }),
                total_fee:0,
                total_paid:0,
            }
        },

        methods:{
            makeTransaction(){
                this.form.student_id = this.student.id;
                this.$inertia.post('/make-payment/'+this.student.id, this.form, {onSuccess:()=>{
                    this.$inertia.reload({only: ['student', 'fees']})
                    this.form.reset();
                }})
            },

             totalStudentFees(){
                    let total = 0;
                    for(let fee of this.fees){
                        total +=fee.amount
                    }
                    this.total_fee = total;
                    return this.formatAmount(total)
                },

            formatAmount(amount){
                 return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
            },
            
            totalPaid(){
                let total_paid = 0;
                if(this.student.transactions){
                    for(let bal of this.student.transactions){
                        total_paid += parseFloat(bal.amount);
                    }
                }
                this.total_paid = total_paid;
                return this.formatAmount(total_paid);
            },

            formatDate(val){
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
            removeTransactionEntry(id){
                axios.post('/remove-transaction-entry/'+id).then((response)=>{
                    this.$inertia.reload({only: ['student', 'fee_sumary']})
                })
            }
        }
    }
</script>
