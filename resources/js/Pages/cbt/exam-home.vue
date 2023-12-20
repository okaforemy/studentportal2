<style>
    .subject-box {
        min-height: 200px;
        background-color: purple;
        padding: 5px;
        /* margin: 5px; */
        margin: ;
        /* border-radius: 5px; */
        cursor: pointer;
        opacity: 0.9;
        color: #fff;
        text-align: center;
        display: flex;
        /* align-content: center; */
        justify-content: center;
        align-items: center;
    }
    .subject-box:hover {
        opacity: 0.8;
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
        <div class="my-4">
            <div class="row">
                <div class="col-md-3 subject-box" @click="startExam( subj.subject, subj.section)"  v-for="(subj, index) in subjects" :key="index">
                    {{ subj.subject }}
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props:['subjects','fullname'],

    data(){
        return{
            isTimeNow: false,
        }
    },
    methods:{
        compareTime(time) {
            const currentTime = new Date();
            const currentHours = currentTime.getHours();
            const currentMinutes = currentTime.getMinutes();

            const [hours, minutes] = time.split(":");
            const databaseTime = new Date();
            databaseTime.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0);

            if (currentTime < databaseTime) {
                //this.comparisonResult = "Current time is earlier than the database time.";
                //this.isTimeNow = true;
                return false;
            } else if (currentTime > databaseTime) {
                //this.comparisonResult = "Current time is later than the database time.";
                //this.isTimeNow = false;
                return true;
            } else {
                //this.comparisonResult = "Current time is equal to the database time.";
                //this.isTimeNow = true;
                return true;
            }
        },
        startExam(subject, section){
                window.location.href = "/prepare-exam?subject="+subject+"&section="+section;
        },
        logOut(){
            window.location.href = '/log-out'
        }
    },
}
</script>