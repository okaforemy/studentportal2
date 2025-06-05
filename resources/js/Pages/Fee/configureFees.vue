<template>
    <div class="pt-4 pb-4 px-3">

        <Head title="Configure Fees" />
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configure Fees</h3>
            </div>

            <div class="card-body  p-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="" v-model="sect" class="form-control" id="">
                            <option value="">Select Section</option>
                            <option :value="sec.name" v-for="sec in section">{{ sec.name }}</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="" v-model="grade" @change="setSelectedClass" class="form-control" id="">
                            <option value="">Select Class</option>
                            <option :value="clas.id" v-for="clas in selectedClass">{{ clas.class_name }}</option>
                        </select>
                    </div>
                    <div class="col-md-4"
                        v-if="selected_class && selected_class.arms && selected_class.arms.length > 0">
                        <select name="" v-model="arm" class="form-control" @change="getConfiguredFees" id="">
                            <option value="">Select Arm</option>
                            <option :value="arm.arm_name" v-for="arm in selected_class.arms">{{ arm.arm_name }}</option>
                        </select>
                        <span v-if="errors" class="text-danger" style="font-size: 14px;">{{ errors.arm }}</span>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="row">
                        <div class="col-md-6">
                            <label for="">Description</label>
                            <input type="text" v-model="form.description" name="" class="form-control"
                                placeholder="Description" id="">
                            <span v-if="errors" class="text-danger" style="font-size: 14px;">{{ errors.description
                                }}</span>
                        </div>
                        <div class="col-md-4">
                            <label for="">Amount</label>
                            <input type="number" name="" v-model="form.amount" class="form-control" placeholder="Amount"
                                id="">
                            <span v-if="errors" class="text-danger" style="font-size: 14px;">{{ errors.amount }}</span>
                        </div>
                        <div class="col-md-2">
                            <label for="" style="display: block;">Optional?</label>
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-secondary" :class="form.is_optional == 1 ? 'active' : ''"
                                    style="color: white !important;">
                                    <input type="radio" name="options" value="1" v-model="form.is_optional" checked> Yes
                                </label>
                                <label class="btn btn-secondary" :class="form.is_optional == 0 ? 'active' : ''"
                                    style="color: white !important;">
                                    <input type="radio" name="options" value="0" v-model="form.is_optional"> No
                                </label>
                            </div>

                        </div>
                    </div>
                    <div class="mt-3"
                        v-if="sect && grade && selected_class && (!selected_class.arms || selected_class.arms.length === 0 || arm !== '')">
                        <button class="btn btn-primary" @click.prevent="saveConfiguration">
                            Save configuration
                        </button>
                    </div>
                </div>

                <!-- table -->
                 <div class="mt-4" v-if="fees && fees.length > 0">
                    <table class="table table-striped mt-4 table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Description</th>
                                <th>Optional?</th>
                                <th>Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(fee, index) in fees">
                                <td>{{ index + 1 }}</td>
                                <td>{{ fee.description }}</td>
                                <td>{{ fee.is_optional?'Yes':'No' }}</td>
                                <td>{{ formatAmount(fee.amount) }}</td>
                                <td>
                                    <i class="fas fa-edit px-2 text-success" @click="editFee(fee)" style="cursor: pointer"></i>
                                    <i class="fas fa-trash text-danger" @click.prevent="deleteFee(fee)" style="cursor: pointer"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                 </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Head } from '@inertiajs/inertia-vue'

export default {
    components: { Head },
    props: ['section', 'classes', 'errors'],
    data() {
        return {
            grade: '',
            sect: '',
            selected_class: '',
            arm: '',
            form: this.$inertia.form({
                description: '',
                amount: '',
                section: '',
                class: '',
                class_id: '',
                is_optional: false,
                arm: '',
                id: null
            }),
            fees:[]
        }
    },
    methods: {
        setSelectedClass() {
            let val = this.classes.filter((item) => item.id == this.grade)
            if (val && val.length > 0) {
                this.selected_class = val[0]
            }
            this.resetArms()
            this.getConfiguredFees()
        },

        formatAmount(amount){
           return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
        },

        editFee(fee){
            this.form.description = fee.description
            this.form.amount = fee.amount
            this.form.id = fee.id
            this.form.is_optional = fee.is_optional
        },  

        resetArms() {
            if (this.selected_class && this.selected_class.arms && this.selected_class.arms.length == 0) {
                this.arm = ''
            }

        },

        fetchConfiguredFees() {
            axios.get('/get-configured-fees', { params: { section: this.sect, class_id: this.selected_class.id, arm: this.form.arm } })
                .then((response) => {
                    this.fees = response.data
                })
        },

        getConfiguredFees() {
            if (this.selected_class && this.selected_class.arms && this.selected_class.arms.length == 0) {
                this.fetchConfiguredFees()
            } else if (this.arm) {
                this.form.arm = this.arm
                this.fetchConfiguredFees()
            }
        },

        saveConfiguration() {
            this.form.class = this.grade
            this.form.section = this.sect
            this.form.class_id = this.selected_class.id

            this.$inertia.post('/configure-fees', this.form, {
                onSuccess: (response) => {
                    this.form.reset()
                    this.fetchConfiguredFees()
                }
            })
        },

        deleteFee(fee){
            let that = this
            $.confirm({
            title: 'Delete!',
            content: 'Do you want to delete '+fee.description+'?',
            type: 'red',
            buttons: {   
                ok: {
                    text: "ok!",
                    btnClass: 'btn-primary',
                    keys: ['enter'],
                    action: function(){
                      axios.get('/delete-fee/'+fee.id).then((response)=>{
                            if(response.data.success){
                                that.fetchConfiguredFees()
                            }
                        })
                    }
                },
                cancel: function(){
                      
                }
            }
        });
            
        }
    },
    computed: {
        selectedClass() {
            return this.classes.filter((item) => item.section == this.sect);
        }
    }
}
</script>