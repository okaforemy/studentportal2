<template>
    <div class="pt-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">ADD FEES FOR <span style="font-weight: bold;">{{ student.fullname }}</span>, Class: {{ student.grade }} <span v-if="student.arm">{{ student.arm }}</span></h3>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <select name="" v-model="form.fee_config_id" class="form-control" id="">
                            <option value="">Select fee configuration</option>
                            <option :value="fee.id" v-for="fee in fees">{{ fee.description }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button @click.prevent="addFee" class="btn btn-primary">Add Fee</button>
                    </div>
                </div>

                <div class="py-3">
                    <table class="table">
                        <thead>
                            <th>#</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Discount</th>
                            <th></th>
                        </thead>
                        <tbody>
                            <tr v-for="(fee, index) in studentFeeConfig">
                                <td>{{ index+1 }}</td>
                                <td>{{ fee.description }}</td>
                                <th>{{ fee.is_optional ==1? 'Optional':'Mandatory' }}</th>
                                <td>{{ formatAmount(fee.amount, fee.pivot?.discount) }}</td>
                                <td>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <input type="text" v-model="discount[index+1]" class="form-control" name="" placeholder="add discount" id="">
                                        </div>
                                        <div class="col-md-6">
                                            <button class="btn btn-success" @click.prevent="addDiscount($event, fee.id, index)">save</button>
                                        </div>
                                    </div>
                                </td>
                                <td > <span v-if="fee.is_optional == 1" style="cursor: pointer;" @click="removeSingleFee(fee.id)">Remove</span></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td style="font-size: 16px; font-weight: bold;" colspan="2">TOTAL</td>
                                <td style="font-size: 16px; font-weight: bold;">{{ totalStudentFees() }} </td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</template>

<script>

export default {
    props: ['student', 'studentFeeConfig', 'feeConfig'],
    data(){
        return {
            form: this.$inertia.form({
                fee_config_id: '',
                student_id:''
            }),
            discount:{}
        }
    },
    methods: {
        formatAmount(amount,discount){
           return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount-discount);
        },

        totalStudentFees(){
            let total = 0;
            let discount = 0;
            for(let fee of this.studentFeeConfig){
                total +=fee.amount
                discount += fee.pivot?.discount
            }
            console.log(discount)
            return this.formatAmount(total, discount)
        },
        addFee(){
            this.form.student_id = this.student.id;
            this.$inertia.post('/add-single-fee', this.form, {
                only: ['studentFeeConfig', 'feeConfig'],
                onSuccess: ()=>{
                    this.form.reset();
                }
            })
        },
        removeSingleFee(id){
            let data = {
                student_id: this.student.id,
                fee_config_id: id
            }
            
            this.$inertia.get('/remove-single-fee',data, {
                only: ['studentFeeConfig', 'feeConfig']
            })
        },
        addDiscount(event, id,index){
            this.$inertia.post('/add-discount',{student_id: this.student.id, fee_configuration_id: id, discount: this.discount[index+1]},{
                only: ['studentFeeConfig', 'feeConfig']
            })
        }
    },
    computed:{
        fees(){
            const studentFeeIds = new Set(this.studentFeeConfig.map(item => item.id));
            return this.feeConfig.filter(item => !studentFeeIds.has(item.id));
        }
    },
    created(){
        let fee_length = this.studentFeeConfig.length;
        let data = {}
        for(let i=0; i < fee_length; i++){
            //data[this.studentFeeConfig[i].id] = this.studentFeeConfig[i].pivot.discount?this.studentFeeConfig[i].pivot.discount: 0.0
            data[i+1] = this.studentFeeConfig[i].pivot.discount?this.studentFeeConfig[i].pivot.discount: 0.0
        }
        this.discount = data
    }
}
</script>
