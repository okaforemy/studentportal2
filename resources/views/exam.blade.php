<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CBT</title>

   
  <link rel="stylesheet" href="{{mix('css/app.css')}}">
  <script src="{{mix('js/app.js')}}" defer></script>
    
</head>

<body class="hold-transition login-page pt-4">
    <div id="ap">
        <div class="card pt-4 col-md-12">
            <div>
                <div v-if="total == 0">
                     <div class="col-md-8 pt-4">
                         <h4>Welcome to purplins' School CBT Exam/Text, <span class="font-weight-bold">{{ fullname }}</span>, Please click the button below to start the test.</h4>
                     </div>
                     <div class="text-center pt-4">
                         <button class="btn  btn-success" @click.prevent="startExam" ref="start_btn">Start Exam/Test</button>
                     </div>
                </div>
                 <div class="pt-3">
                     <div class="col-md-10 px-4">
                         <div class="mt-2">
                                     <label for="">Question for {{ fullname }}</label>
                                     <div class="card">
                                         <div class="card-body">
                                             <span>{{ index+1 }}.</span><h5 v-html="question.question"></h5>
                                         </div>
                                     </div>
                                 </div>
                                 <div class="mt-3 ml-2 form-group row">
                                     <label for="">A.</label>
                                     <div class="col-md-8">
                                         <input type="text" v-model="question.option_a" name="option_a" required id="" class="form-control">
                                     </div>
                                     <div class="col-md-2">
                                         <input type="radio" name="correct_option" @click="answerQuestion" v-model="question.your_answer" value="option_a" id="" class="form-check-input ml-4 mt-3">
                                     </div>
                                 </div>
                                 <div class="mt-3 ml-2 form-group row">
                                     <label for="">B.</label>
                                     <div class="col-md-8">
                                         <input type="text" v-model="question.option_b" id="" class="form-control">
                                     </div>
                                     <div class="col-md-2">
                                         <input type="radio" name="correct_option" @click="answerQuestion" value="option_b" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                                     </div>
                                 </div>
                                 <div class="mt-3 ml-2 form-group row">
                                     <label for="">C.</label>
                                     <div class="col-md-8">
                                         <input type="text" v-model="question.option_c"  id="" class="form-control">
                                     </div>
                                     <div class="col-md-2">
                                         <input type="radio" @click="answerQuestion" name="correct_option" value="option_c" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                                     </div>
                                 </div>
                                 <div class="mt-3 ml-2 form-group row">
                                     <label for="">D.</label>
                                     <div class="col-md-8">
                                         <input type="text" v-model="question.option_d" id="" class="form-control">
                                     </div>
                                     <div class="col-md-2">
                                         <input type="radio" @click="answerQuestion" name="correct_option" value="option_d" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                                     </div>
                                 </div>
                                 <div class="mt-3 ml-2 form-group row">
                                     <label for="">E.</label>
                                     <div class="col-md-8">
                                         <input type="text" v-model="question.option_e" id="" class="form-control">
                                     </div>
                                     <div class="col-md-2">
                                         <input type="radio" name="correct_option" value="option_e" v-model="question.your_answer" id="" class="form-check-input ml-4 mt-3">
                                     </div>
                                     <input type="hidden" name="" ref="question_id" :value="question.id">
                                 </div>
                                 <div class="mt-3 text-center pb-4">
                                     <button class="btn btn-secondary mr-3" @click.prevent="previous" v-show="index > 0">Previous</button>
                                     <button class="btn btn-success" @click.prevent="next" v-if="is_next">Next</button>
                                     <button class="btn btn-success" @click="submitQuestion" v-if="is_submit">Submit</button>
                                 </div>
                     </div>
                 </div>
             </div>
        </div>
    </div>
</body>

</html>
