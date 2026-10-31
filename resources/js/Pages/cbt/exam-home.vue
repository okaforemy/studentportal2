<style>
    .subject-box {
        min-height: 200px;
        width: 100%;
        border: 0;
        border-radius: 8px;
        background-color: #563d7c;
        padding: 1rem;
        margin: 0.5rem;
        cursor: pointer;
        opacity: 0.9;
        color: #fff;
        font-size: 1.25rem;
        font-weight: 600;
        text-align: center;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .subject-box:hover {
        opacity: 0.8;
    }
    img {
        max-width: 60%;
    }
</style>
<template>
    <div>
        <div class="float-right">
            <button class="my-2 btn btn-success" @click="logOut">Log out</button>
        </div>
        <div class="text-center">
            <img src="/images/midterm.png" alt="" class="w-50 pt-4">
        </div>
        <div>
            <h4 class="text-center font-weight-bold py-3">Welcome {{ fullname }} to Purplins High School CBT center; click to select a subject to start your exam.<br> Good luck</h4>
        </div>
        <div v-if="error" class="alert alert-warning" role="alert">{{ error }}</div>
        <div class="my-4">
            <div v-if="subjects.length" class="row">
                <div v-for="(subj, index) in subjects" :key="index" class="col-md-4 col-lg-3">
                    <button class="subject-box" type="button" :disabled="starting" @click="startExam(subj.subject, subj.section)">
                        {{ subj.subject }}
                    </button>
                </div>
            </div>
            <div v-else class="alert alert-info text-center" role="status">
                No exams are available for your class at this time.
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props:['subjects','fullname','error'],

    data(){
        return{
            starting: false,
        }
    },
    methods:{
        startExam(subject, section){
            if (this.starting) {
                return;
            }
            this.starting = true;
            window.location.href = `/prepare-exam?subject=${encodeURIComponent(subject)}&section=${encodeURIComponent(section)}`;
        },
        logOut(){
            window.location.href = '/log-out'
        }
    },
}
</script>