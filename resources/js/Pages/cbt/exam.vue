
<style scoped>
    .badge{
        font-size: 18px;
        cursor: pointer;
    }

    .form-control:disabled, .form-control[readonly] {
        background-color: #fff;
        cursor: default;
    }
</style>
<template>
    <div>
       <div v-if="total == 0">
            <div class="col-md-8 pt-4 pl-2">
                <h4>Welcome to purplins' School CBT Exam/Text, <span class="font-weight-bold">{{ fullname }}</span>, Please click the button below to start the test.</h4>
            </div>
            <!-- <div class="text-center pt-4">
                <button class="btn  btn-success" @click.prevent="startExam" ref="start_btn">Start Exam/Test</button>
            </div> -->
       </div>
       <div class="float-right">
        <button class="mb-2 btn btn-success" @click="logOut">Log out</button>
        <h2 class="font-weight-bold"><span id="timer"></span></h2>
       </div>
       <div class="my-4">
        <h4 class="text-center my-3 font-weight-bold" v-if="total !=0">Welcome to Purplins School CBT</h4>
        <h5 class="ml-4"><strong>Fullname:</strong> {{ fullname }}</h5>
       </div>
       <div class="my-2 ml-4">
        <span class="badge mx-1" :class="{'badge-danger': ques.your_answer==null, 'badge-success': ques.your_answer != null}" 
            v-for="(ques, index) in questions" :key="index" @click="selectQuestion(index)">
            {{ index +1 }}</span>
       </div>
        <div class="pt-3">
            <div class="col-md-10 px-4">
                <div class="mt-2">
                            
                            <div class="card">
                                <div class="card-body">
                                    <span>{{ index+1 }}.</span><h5 v-html="question.question"></h5>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">A.</label>
                            <div class="col-md-8">
                                <input type="text" readonly v-model="question.option_a" @click="answerQuestion" name="option_a" required id="" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" name="correct_option" disabled v-model="question.your_answer" value="option_a" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">B.</label>
                            <div class="col-md-8">
                                <input type="text" readonly v-model="question.option_b" @click="answerQuestion" name="option_b" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" disabled name="correct_option"  value="option_b" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">C.</label>
                            <div class="col-md-8">
                                <input type="text" readonly v-model="question.option_c" @click="answerQuestion"  name="option_c" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" disabled name="correct_option" value="option_c" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row">
                            <label for="">D.</label>
                            <div class="col-md-8">
                                <input type="text" readonly v-model="question.option_d" @click="answerQuestion" name="option_d" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" disabled  name="correct_option" value="option_d" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 ml-2 form-group row" v-if="question.option_e">
                            <label for="">E.</label>
                            <div class="col-md-8">
                                <input type="text" readonly v-model="question.option_e" name="option_e" @click="answerQuestion" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <input type="radio" disabled name="correct_option" value="option_e" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                            </div>
                        </div>
                        <div class="mt-3 text-center pb-4">
                            <button class="btn btn-secondary mr-3" @click.prevent="previous" v-show="index > 0">Previous</button>
                            <button class="btn btn-success" @click.prevent="next" v-if="is_next">Next</button>
                            <button class="btn btn-success" @click="submitQuestion" v-if="isAnsweredAll">Submit</button>
                        </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: ['fullname','student_id','questions','duration'],
    data(){
        return{
          //  questions: [],
            question:[],
            index:0,
            total:0,
            isAnsweredAll: false,
            current_duration: null,
        }
    },
    methods:{
        startExam(){
            this.$refs['start_btn'].textContent = "Please wait..."
            this.getQuestions();
        },

        getQuestions(){
            axios.get('/prepare-exam',{params:{student_id: this.student_id}}).then((response)=>{
                if(response.data.length == 0){
                    toastr.error('Question not found for this class!', 'Error')
                }
                this.questions = response.data;
                this.total = this.questions.length;
                this.question = this.questions[0];
                this.$refs['start_btn'].textContent = "Start Exam/Test"
            })
        },

        next(){
            if(this.index <= this.total){
                this.index++
                this.question = this.questions[this.index];
                this.unanswered()
            }
        },

        previous(){
            this.index--
            this.question = this.questions[this.index];
            this.unanswered()
        },

        saveSelectedAnswer(answer){
            let question_id = this.question.id
            axios.post('/answer-question',{question_id: question_id, your_answer: answer}).then((response)=>{
               let found = this.questions.findIndex(val=>{
                return val.id == this.question.id
               });
               this.questions[found].your_answer = answer
               this.unanswered()
            })
        },

        answerQuestion(event){
            let clicked_option = event.target.name;
            let answer = clicked_option?clicked_option: $('input[name="correct_option"]:checked').val()
            this.saveSelectedAnswer(answer)
           
        },
        unanswered(){
            let unanswered = this.questions.filter(val=>{
                return val.your_answer==null;
            })
    
            if(unanswered.length > 0 && (this.index +1) != this.total){
               this.isAnsweredAll = false;
            }else if(unanswered.length == 0 && (this.index +1) === this.total){
               this.isAnsweredAll = true;
            }else{
                this.isAnsweredAll = false;
            }
        },
        submitQuestion(){
            localStorage.setItem('timerData', 0.0);
            window.location.href = '/cbt-result';
        },
        selectQuestion(index){
            this.question = this.questions[index];
            this.index = index;
            this.unanswered()
        },

         formatTime(minutes, seconds) {
             return `${String(minutes).padStart(2, '0')}:${parseInt(String(seconds).padStart(2, '0'))}`;
        },

         updateTimer(minutes, seconds) {
            document.getElementById('timer').innerText = this.formatTime(minutes, seconds);
        },

         saveToLocalStorage(minutes, seconds) {
            if (seconds % 10 === 0) {
                const timeObject = { minutes, seconds };
                localStorage.setItem('timerData', JSON.stringify(timeObject));
            }
        },

         startCountdown(durationInMinutes) {
            let totalSeconds = durationInMinutes * 60;
            let that = this;
            let first_warning = Math.floor(totalSeconds * 0.3);
            let second_warning = Math.floor(totalSeconds * 0.2);
            let last_warning = Math.floor(totalSeconds * 0.1);
           
            const countdownInterval = setInterval(function() {
                const minutes = Math.floor(totalSeconds / 60);
                const seconds = totalSeconds % 60;

                let rounded_seconds = Math.floor(totalSeconds);

                that.updateTimer(minutes, seconds);
                that.saveToLocalStorage(minutes, seconds);

                if(rounded_seconds == first_warning){
                    let minute = first_warning / 60;
                    let seconds = first_warning % 60;
                    toastr.error('You have '+Math.floor(minute)+' minutes and '+Math.floor(seconds)+' seconds remaining', 'Error')
                }

                if(rounded_seconds == second_warning){
                    let minute = second_warning / 60;
                    let seconds = second_warning % 60;
                    toastr.error('You have '+Math.floor(minute)+' minutes and '+Math.floor(seconds)+' seconds remaining', 'Error')
                }

                if(rounded_seconds == last_warning){
                    let minute = last_warning / 60;
                    let seconds = last_warning % 60;
                    toastr.error('You have '+Math.floor(minute)+' minutes and '+Math.floor(seconds)+' seconds remaining', 'Error')
                }

                if (totalSeconds <= 0) {
                    that.submitQuestion()
                clearInterval(countdownInterval);
                } else {
                totalSeconds--;
                }
            }, 1000);
        },

        checkUserInput(event){
            let pressed = event.key
            if(pressed == 'a'){
                this.saveSelectedAnswer('option_a')
            }
            if(pressed == 'b'){
                this.saveSelectedAnswer('option_b')
            }
            if(pressed == 'c'){
                this.saveSelectedAnswer('option_c')
            }
            if(pressed == 'd'){
                this.saveSelectedAnswer('option_d')
            }
            if(this.question.option_e && pressed == 'e'){
                this.saveSelectedAnswer('option_e')
            }
            if(pressed == 'n'){
                this.next()
            }
            if(pressed == 'p'){
                this.previous()
            }
           
        },

        logOut(){
            window.location.href = '/log-out'
        }

    },
    computed: {
        is_next() {
            return (this.index+1) < this.total;
        },
        // is_submit() {
        //     let unanswered = this.unanswered()
        //     if(!unanswered){
        //         return (this.index +1) === this.total;
        //     }
            
        // }
    },
    created(){
        //this.getQuestions()
        this.question = this.questions[0];
        this.total = this.questions.length;
    },
    mounted(){
        let current_time = localStorage.getItem('timerData');

        let time = 0;
       if(current_time != null && current_time != 0){
         current_time = JSON.parse(current_time);
            let mins = current_time.minutes;
            let secs = current_time.seconds;
            time = parseFloat(mins+"."+secs).toFixed(2);
       }
        this.current_duration = (time != null && time !=0.0)? time: this.duration ;
    
        this.startCountdown(this.current_duration)

        window.addEventListener('keypress', this.checkUserInput)
    }
}
</script>