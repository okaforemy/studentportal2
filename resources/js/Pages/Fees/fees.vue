<style scoped>
    ul{
        display: flex;
        text-decoration: none;
        list-style-type: none;
        padding: 2px;
        background-color: #fff;
        border-radius: 5px;
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.10);
    }

    .flex-item{
        flex: 1;
        padding: 8px;
        text-align: center;
    }

    ul li{
        cursor: pointer;
        margin: 4px 1px;
        border-radius: 5px;
    }

    li.active{
        box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.10);
    }
</style>
<template>
    <div class="pt-4 px-3">
        <div class="row">
            <div class="col-md-6 ">
                <h2 class=" font-weight-bold">School Fees Management</h2>
                <p>Manage student fees, payments, and generate reports</p>
            </div>
            <!-- <div class="col-md-6 text-right">
                <button class="btn btn-primary">+ New Payment</button>
            </div> -->
        </div>

        <div class="row mt-4">
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-info">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave-alt"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Total Fees for the term</span>
                        <span class="info-box-number">{{formatAmount(data.total_fee)}}</span>

                        <div class="progress">
                            <div class="progress-bar" style="width: 100%"></div>
                        </div>

                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-success">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave-alt"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Total fees collected for the term</span>
                        <span class="info-box-number">{{formatAmount(data.total_paid)}}</span>

                        <div class="progress">
                            <div class="progress-bar" :style="'width:'+ ((data.total_paid/data.total_fee)*100)+'%'"></div>
                            <!-- <div v-if="fee_sumary && fee_sumary.total_paid"
                                        class="progress-bar"
                                        :style="'width:'+((fee_sumary.total_paid/fee_sumary.total_fee) * 100)+ '%'"
                                    ></div> -->
                        </div>

                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-warning">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave-alt"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Total outstanding fees</span>
                        <span class="info-box-number">{{formatAmount(data.outstanding)}}</span>

                        <div class="progress">
                            <div class="progress-bar" :style="'width:'+ ((data.outstanding/data.total_fee)*100)+'%'"></div>
                            <!-- <div v-if="fee_sumary && fee_sumary.outstanding"
                                        class="progress-bar"
                                        :style="'width:'+(((fee_sumary.outstanding)/fee_sumary.total_fee) * 100)+ '%'"
                                    ></div>
                                    <div v-else
                                        class="progress-bar"
                                        :style="'width:0%'"
                                    ></div> -->
                        </div>

                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
            <div class="col-md-3 col-sm-6 col-12">
                <div class="info-box bg-gradient-danger">
                    <span class="info-box-icon"><i class="fas fa-money-bill-wave-alt"></i></span>

                    <div class="info-box-content">
                        <span class="info-box-text">Total credit for the term</span>
                        <span class="info-box-number">{{formatAmount(data.credit)}}</span>

                        <div class="progress">
                            <div class="progress-bar" :style="'width:'+ ((data.credit/data.total_fee)*100)+'%'"></div>
                                    <!-- <div
                                        class="progress-bar"
                                        :style="'width:'+(fee_sumary && fee_sumary.credit? ((fee_sumary.credit)/fee_sumary.total_fee) * 100 : 0)+ '%'"
                                    ></div> -->
                                </div>

                    </div>
                    <!-- /.info-box-content -->
                </div>
                <!-- /.info-box -->
            </div>
            <!-- /.col -->
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <ul>
                    <li class="flex-item" :class="active_btn == 'fee_structure'? 'active': ''" @click="setActiveBtn('fee_structure')">Fee Structure</li>
                    <li class="flex-item" :class="active_btn == 'student_fees'? 'active': ''" @click="setActiveBtn('student_fees')">Student Fees</li>
                    <!-- <li class="flex-item" :class="active_btn == 'record_payment'? 'active': ''" @click="setActiveBtn('record_payment')">Record Payment</li> -->
                    <li class="flex-item" :class="active_btn == 'analytics'? 'active': ''" @click="setActiveBtn('analytics')">Analytics</li>
                </ul>
            </div>
                <div class="col-md-6 text-right">
                <Link href="/configure-fees" class="btn btn-primary">+ Add Fee Structure</Link>
            </div>
        </div>

        <div>
            <Fee_structure :data="fee_structures" @isOptinal="isOptinal" @classChanges="classChanged" :classes="classes" @search="searchFeeStructure" @refresh="refershData" v-show="active_btn == 'fee_structure'"/>
            <student_fees  v-show="active_btn == 'student_fees'"/>
            <feeAnalytics v-show="active_btn == 'analytics'" @data="setData"/>
        </div>
    </div>
</template>
<script>
    import Fee_structure from '../../Components/fee_structure.vue';
    import student_fees from '../../Components/student_fees.vue';
    import feeAnalytics from '../../Components/feeAnalytics.vue';
    import { Link } from '@inertiajs/inertia-vue';

    export default{
        components: {Fee_structure, Link, student_fees, feeAnalytics},
        props:['feeStructures', 'classes'],
        data(){
            return{
                active_btn: 'fee_structure',
                data: {},
                fee_structures: this.feeStructures
            }
        },
        methods:{
            setActiveBtn(txt){
                this.active_btn = txt
            },
            setData(data){
                this.data = data;
            },
            refershData(){
                this.$inertia.reload({
                    only:['feeStructures']
                })
            },
            formatAmount(amount){
                return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
            },
            classChanged(grade){
                axios.get('/fees', {params: {grade:grade}}).then((response)=>{
                    this.fee_structures = response.data
                })
            },
            searchFeeStructure(search){
                axios.get('/fees', {params: {search:search}}).then((response)=>{
                    this.fee_structures = response.data
                })
                // this.$inertia.get('/fee-search', {search: search}, {onSuccess:page=>{
                //     console.log(page)
                // }})
            },
            isOptinal(option){
                axios.get('/fees', {params: {option:option}}).then((response)=>{
                    this.fee_structures = response.data
                })
            }
        },
        watch: {
            feeStructures: {
                handler(newVal) {
                this.fee_structures = newVal; // force update internal data
                },
                deep: true,
            },
        },
    }
</script>