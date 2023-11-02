<template>
    <div>
        <div class="col-md-10 pt-3">
            <div class="card">
                <div class="card-header">
                    <span>Settings</span>
                </div>
                <div class="card-body">
                   <form action="" id="settings_form" @submit.prevent="saveSettings">
                    <table class="table" v-if="settings.length ==0">
                        <thead>
                            <tr>
                            <th>S/N</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Date</th>
                            <th>Duration</th>
                            
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(subject, index) in subjects" :key="index">
                                <td>{{ index+1 }}</td>
                                <td>{{ subject.subject }}
                                    <input type="hidden" name="subject[]" :value="subject.subject">
                                    <input type="hidden" name="section[]" :value="subject.section">
                                </td>
                                <td>{{ subject.section.split("_").join(" ") }}</td>
                                <td><input type="date" name="date[]" class="form-control" id=""></td>
                                <td><input type="text" name="duration[]" class="form-control" placeholder="duration" id=""></td>
                                
                            </tr>
                        </tbody>
                    </table>
                    <table class="table" v-if="settings && settings.length > 0">
                        <thead>
                            <tr>
                            <th>S/N</th>
                            <th>Subject</th>
                            <th>Section</th>
                            <th>Date</th>
                            <th>Duration</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(setting, index) in settings" :key="index">
                                <td>{{ index+1 }}</td>
                                <td>{{ setting.subject }}
                                    <input type="hidden" name="subject[]" :value="setting.subject">
                                    <input type="hidden" name="id[]" :value="setting.id">
                                    <input type="hidden" name="section[]" :value="setting.section">
                                </td>
                                <td>{{ setting.section.split('_').join(" ") }}</td>
                                <td><input type="date" :value="setting.date" name="date[]" class="form-control" id=""></td>
                                <td><input type="text" :value="setting.duration" name="duration[]" class="form-control" placeholder="duration" id=""></td>
                                <td>
                                    <a href="" v-if="setting.is_started ==1" class="btn btn-success btn-sm">Started</a>
                                    <a href="" v-else class="btn btn-danger btn-sm" @click.prevent="startExam(setting.id)">Start</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div class="text-center mt-2">
                        <button class="btn btn-primary" type="submit">Save</button>
                    </div>
                   </form>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: [ 'subjects','settings'],
    data(){
        return{

        }
    },
    methods:{
        saveSettings(){
            let form = $('#settings_form').serialize();
            this.$inertia.post('/cbt-settings', form, {
                onSuccess: (response)=> {
                    toastr.success('Settings saved successfully!', 'Success')
                }
            });
        },
        startExam(index){
            this.$inertia.get('/start-exam-setting',{id: index}, {
                onSuccess: (response)=>{
                    toastr.success('Exam started successfully!', 'Success')
                }
            })
        }
    }
}
</script>