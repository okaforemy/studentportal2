<template>
    <div class="col-md-4 py-4">
        <form action="" @submit.prevent="saveSetting">
            <div>
                <label for="">Session <span class="text-danger">*</span></label>
                <input type="text" placeholder="2022/2023" class="form-control" v-model="form.session">
            </div>
            <div class="mt-3">
                <label for="">Term <span class="text-danger">*</span></label>
                <select class="form-control" v-model="form.term" id="">
                    <option value="">Select term</option>
                    <option value="first_term">First term</option>
                    <option value="second_term">Second term</option>
                    <option value="third_term">Third term</option>
                </select>
            </div>
            <div class="mt-3">
                <label for="">Term begin <span class="text-danger">*</span></label>
                <input type="date" class="form-control" v-model="form.term_begin" id="">
            </div>
            <div class="mt-3">
                <label for="">Term end <span class="text-danger">*</span></label>
                <input type="date" class="form-control" v-model="form.term_end">
            </div>
            <div class="mt-3">
                <label for="">Mid-term <span class="text-danger">*</span></label>
                <input type="date" v-model="form.mid_term_end" class="form-control">
            </div>
            <div class="mt-3">
                <label for="">Mid-term resumption <span class="text-danger">*</span></label>
                <input type="date" v-model="form.mid_term_resumption" class="form-control">
            </div>
            <div class="mt-3">
                <label for="">Resumption <span class="text-danger">*</span></label>
                <input type="date" v-model="form.resumption" class="form-control">
            </div>
            <div class="mt-3">
                <button class="btn btn-primary" type="submit">Save</button>
            </div>
        </form>
    </div>
</template>

<script>
export default {
    props:['settings'],
    data(){
        return{
            form:{
                session:"",
                term: "",
                term_begin:"",
                term_end: "",
                mid_term_end:"",
                mid_term_resumption: "",
                resumption: "",
            }
        }
    },
    methods:{
        saveSetting(){
            this.$inertia.post('/settings',this.form, {onSuccess: ()=>{
                toastr.success('settings saved successfully!', 'Success')
            }})
        }
    },
    created(){
        if(this.settings){
            this.form = this.settings;
        }
    }
}
</script>