<style scoped>
    .spinner-grow{
        height: 20px;
        width: 20px;
    }
</style>
<template>
    <div class="px-4 py-4">
         <h4>Upload Scores</h4>
        <div class="row">
            <div class="py-2 col-md-6">
                <input type="file" name="" class="form-control" id="file_upload" @change="handleFileUpload" accept=".xlsx, .xls">
            </div>
            <div class="col-md-5 py-2 text-right fixed">
                <button @click="uploadAllScores" :disabled="Object.keys(allScores).length === 0" style="position:fixed;" class="btn btn-primary">
                    Save Scores
                    <div class="spinner-grow" v-if="is_upload" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                </button>
            </div>
        </div>

        <div class="mt-2">
            <div v-if="Object.keys(workbookData).length">
                <div v-for="(rows, sheetName) in allScores" :key="sheetName">
                    <h4> {{ rows[0].student }} </h4>
                    <table v-if="rows.length" class="table table-striped table-sm table-md-responsive my-4">
                    <thead>
                        <tr>
                        <th v-for="(header, i) in Object.keys(rows[0]).slice(0, -5)" :key="i">{{ header.split('_').join(" ").toUpperCase() }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
                        <td v-for="(header, i) in Object.keys(row).slice(0, -5)" :key="i">{{ row[header] }}</td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div>

            <!-- <div v-if="Object.keys(allScores).length">yes
                <div v-for="(rows, sheetName) in allScores" :key="sheetName">
                    <h3>{{ sheetName }}</h3>
                    <table v-if="rows.length" class="table table-striped table-sm table-responsive my-2">
                    <thead>
                        <tr>
                        <th v-for="(header, i) in Object.keys(rows[0])" :key="i">{{ header }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="(row, rowIndex) in rows" :key="rowIndex">
                        <td v-for="(header, i) in Object.keys(row)" :key="i">{{ row[header] }}</td>
                        </tr>
                    </tbody>
                    </table>
                </div>
            </div> -->
        </div>
    </div>
</template>

<script>
    import * as XLSX from 'xlsx'
    import { Link } from '@inertiajs/inertia-vue'
import axios from 'axios'
    export default{
        props:['section', 'class', 'arm', 'class_id', 'arm_id'],
        components: {Link},
        data(){
            return {
                workbookData: {}, // { sheetName1: [...rows], sheetName2: [...rows] }
                allScores:{},
                is_upload:false
            }
        },
        methods:{
             handleFileUpload(event) {
               this.allScores = {}

                const file = event.target.files[0]
                const reader = new FileReader()

                reader.onload = (e) => {
                    const data = new Uint8Array(e.target.result)
                    const workbook = XLSX.read(data, { type: 'array' })

                    const allSheets = {}
                    workbook.SheetNames.forEach((sheetName) => {
                    const worksheet = workbook.Sheets[sheetName]
                    const json = XLSX.utils.sheet_to_json(worksheet)
                    allSheets[sheetName] = json
                    })

                    this.workbookData = allSheets

                    //manipulate the data to reflect each student's data
                    Object.entries(allSheets).forEach((sheet) => {
                    Object.values(sheet[1]).forEach(row => {
                        if (row['1ST CA']) {
                        const student_id = row['STUDENT ID'];
                        let first_ca = row['1ST CA']? parseFloat(row['1ST CA']): 0
                        let second_ca = row['2ND CA']? parseFloat(row['2ND CA']): 0
                        let exam = row['EXAM']? parseFloat(row['EXAM']): 0
                        let id = row['ID'];
                        let total = (first_ca + second_ca + exam).toFixed(2);

                        const score = {
                            subject: sheet[0],
                            first_ca: first_ca,
                            second_ca: second_ca,
                            exam: exam,
                            total: total,
                            remark: this.getRemarks(total),
                            student_id: id,
                            student: row['STUDENTS'],
                            grade: this.class,
                            arm: this.arm,
                            section: this.section
                        };

                        // If the student already has scores, push to the array
                        if (id in this.allScores) {
                            this.allScores[id].push(score);
                        } else {
                            // Initialize as array with one score object
                            this.allScores[id] = [score];
                        }
                        }
                    });
                    });

                }

                reader.readAsArrayBuffer(file)
            },

            getRemarks(total){
                let remark = ""
                if(total >= 90){
                    remark = "A+"
                }else if(total <=89.98 && total >=79.99){
                    remark = "A"
                }else if(total <= 79.98 && total >= 69.99){
                    remark = "B+"
                }else if(total <=69.98 && total >=59.99){
                    remark = "B"
                }else if(total <=59.98 && total >= 49.99){
                remark = "C"
                }else if(total <=49.98 && total >= 39.99){
                    remark = "D"
                }else if(total <= 39.98){
                    remark = "E"
                }
                return remark
            },

            uploadAllScores(){
                this.is_upload = true
                axios.post('/upload-primary-scores', this.allScores).then((response)=>{
                    this.is_upload = false
                    if(response.data.success){
                        toastr.success("Scores added successfully!", "Success");
                        this.allScores = {}
                        this.workbookData = {}
                        document.getElementById('file_upload').value = ""
                    }
                })
            }
        }
    }
</script>