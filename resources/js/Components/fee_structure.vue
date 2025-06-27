<template>
    <div class="card px-2 py-2">
       <table class="table table-sm table-reponsive-sm mt-4 table-stripe">
        <thead>
            <tr>
                <th>#</th>
                <th>Description</th>
                <th>Section</th>
                <th>Class</th>
                <th>Optional?</th>
                <th>Amount</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(fee, index) in data.data">
                <td>{{ index + 1 }}</td>
                <td>{{ fee.description }}</td>
                <td>{{ fee.section }}</td>
                <td>{{ fee.class }} {{ fee.arm? fee.arm: '' }}</td>
                <td>{{ fee.is_optional? 'Yes':'No' }}</td>
                <td>{{ formatAmount(fee.amount) }}</td>
                <td>
                    <i style="cursor: pointer;" class="fas fa-edit text-success" @click="setSelectedFee(fee)" data-toggle="modal" data-target="#feeModal"></i>
                    <i class="fas fa-trash text-danger" @click.prevent="deleteFee(fee)" style="cursor: pointer"></i>
                </td>
            </tr>
        </tbody>
       </table>
        <Paginator :links="data.links"/>

        <!-- Modal -->
        <div class="modal fade" id="feeModal" tabindex="-1" aria-labelledby="feeModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="feeModalLabel">Modal title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <label for="">Class</label>
                    <input type="text" :value="selected_class" readonly name="" class="form-control" id="">
                </div>
                <div class="mt-2">
                    <label for="">Description</label>
                    <input type="text" v-model="form.description" name="" class="form-control" id="">
                </div>
                <div class="mt-2">
                    <label for="">Is Optional?</label>
                    <select name="" class="form-control" v-model="form.is_optional" id="">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
                <div class="mt-2">
                    <label for="">Amount</label>
                    <input type="number" v-model="form.amount" name="" class="form-control" id="">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" @click="saveEdit" class="btn btn-primary">Save changes</button>
            </div>
            </div>
        </div>
        </div>
    </div>
</template>

<script>
    import Paginator from '../Shared/paginator.vue';
    
    export default{
        components: {Paginator},
        props:['data'],
        data(){
            return {
                fee_structures: {},
                links:null,
                selected_class: '',
                 form: this.$inertia.form({
                    description: '',
                    amount: '',
                    section: '',
                    class: '',
                    class_name: '',
                    class_id: '',
                    is_optional: 1,
                    arm: '',
                    id: null
                }),
            }
        },
        methods:{
            formatAmount(amount){
                return new Intl.NumberFormat('en-NG', { style: 'currency', currency: 'NGN' }).format(amount);
            },
            setSelectedFee(fee){console.log(fee.class)
                this.form.id = fee.id
                this.form.description = fee.description
                this.form.amount = fee.amount
                this.selected_class = fee.class + ' ' + (fee.arm ? fee.arm : '')
                this.form.section = fee.section;
                this.form.class = fee.class;
                this.form.class_name = fee.class
                this.form.class_id = fee.class_id
                this.form.arm = fee.arm
                this.form.is_optional = fee.is_optional
            },
            saveEdit(){
                this.$inertia.post('/configure-fees', this.form, {
                onSuccess: (response) => {
                    this.form.reset();
                    $('#feeModal').modal('hide')
                    this.$emit('refresh')
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
                           that.$emit('refresh')
                        })
                    }
                },
                cancel: function(){
                      
                }
            }
        });
            
        }
            
        },
        created(){
            // axios.get('/fee-structures').then((response)=>{
            //     this.fee_structures = response.data.data
            //     this.links = response.data.links
            // })
        }
    }
</script>