<style scoped>
.toggle-container {
            display: inline-block;
            position: relative;
            width: 40px;
            height: 20px;
        }

        /* Toggle slider */
        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            border-radius: 20px;
            transition: .4s;
        }

        /* Slider handle */
        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 16px;
            width: 16px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            border-radius: 50%;
            transition: .4s;
        }

        /* Toggle switch checked state */
        .toggle-container input:checked + .toggle-slider {
            background-color: #2196F3;
        }

        .toggle-container input:focus + .toggle-slider {
            box-shadow: 0 0 1px #2196F3;
        }

        /* Toggle slider handle position for checked state */
        .toggle-container input:checked + .toggle-slider:before {
            transform: translateX(16px);
        }

        /* Hide the default checkbox */
        .toggle-container input {
            display: none;
        }
</style>
<template>
    <div class="pt-3 row">
        <Head title="Add Question" />
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Add Question</h3>
                    <div class="col-md-10">
                       <div class="float-right">
                         <!-- <a href="" class="float-right"><i class="fas fa-plus"></i></i></a> -->
                         <label class="toggle-container ">
                            <!-- The hidden checkbox -->
                            <input type="checkbox" v-model="form.isRichText">
                            <!-- The toggle slider -->
                            <span class="toggle-slider"></span>
                        </label>
                       </div>
                    </div>
                </div>
                <div class="card-body">
                  <div class="add-form">
                    <form action="" @submit.prevent="saveQuestion">
                        <div>
                            <label for="">Class</label>
                            <select name="grade" id="" required v-model="form.grade" class="form-control">
                                <option value="">select class</option>
                                <option v-for="(cls, index) in classes" :key="index" :value="cls.class_name">{{ cls.class_name }}</option>
                            </select>
                            <div v-if="errors.grade" class="text-danger">{{ errors.grade }}</div>
                        </div>
                        <div class="mt-3">
                            <label for="">Subjects</label>
                            <select name="subject" required id="" v-model="form.subject" class="form-control">
                                <option value="">select subject</option>
                                <option v-for="(subject, index) in subjects" :key="index" :value="subject.id">{{ subject.subject }}</option>
                            </select>
                            <div v-if="errors.subject" class="text-danger">{{ errors.subject }}</div>
                        </div>
                        <div class="mt-2">
                            <label for="">Question</label>
                            <ckeditor :editor="editor" v-model="form.question" :config="editorConfig"></ckeditor>
                            <div v-if="errors.question" class="text-danger">{{ errors.question }}</div>
                        </div>
                       <div v-if="form.isRichText ==false">
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">A.</label>
                            <div class="col-md-8">
                                <input type="text" v-model="form.option_a" name="option_a" required id="" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" name="correct_option" v-model="form.answer" value="option_a" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">B.</label>
                            <div class="col-md-8">
                                <input type="text" v-model="form.option_b" id="" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" name="correct_option" value="option_b" v-model="form.answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">C.</label>
                            <div class="col-md-8">
                                <input type="text" v-model="form.option_c"  id="" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" name="correct_option" value="option_c" v-model="form.answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">D.</label>
                            <div class="col-md-8">
                                <input type="text" v-model="form.option_d" id="" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" name="correct_option" value="option_d" v-model="form.answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">E.</label>
                            <div class="col-md-8">
                                <input type="text" v-model="form.option_e" id="" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" name="correct_option" value="option_e" v-model="form.answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <input type="hidden" name="is_richtext" :value="form.isRichText">
                       </div>
                        <div class="mt-3 text-center">
                            <button class="btn btn-primary">Add question</button>
                        </div>
                    </form>
                  </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header"></div>
                <div class="card-body">
                    <ol>
                        <li v-for="(question, index) in questions" :key="index" v-html="question.question"></li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
import { Head } from "@inertiajs/inertia-vue";

export default {
    props:['subjects','classes','errors','questions'],
    components: { Head},
    data(){
        return {
                editor: ClassicEditor,
                editorData: '',
                editorConfig: {
                    // The configuration of the editor.
                },
                form: this.$inertia.form({
                   grade: '',
                   subject:'',
                   question: '',
                   option_a:'',
                   option_b:'',
                   option_c:'',
                   option_d:'',
                   option_e:'',
                    answer: '',
                    isRichText: false
                })
        };
    },
    
    methods:{
        saveQuestion(){
            this.$inertia.post('/add-question', this.form,{onSuccess:()=>{  
                this.form.question = '';
                this.form.option_a = '';
                this.form.option_b = '';
                this.form.option_c = '';
                this.form.option_d = ''; 
                this.form.option_e = '';
                this.form.answer = '';
                this.form.isRichText = false;
                   toastr.success('questions added successfully!', 'Success')
            }}).then((response)=>{
               
            })
        },
        changeRichText(){
            alert(this.isRichText)
            return !this.isRichText;
        }
    }

}
</script>