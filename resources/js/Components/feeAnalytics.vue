<template>
    <div>
        <div class="row my-4">
            <div class="col-md-8 ">
                <div class="card px-2 py-1">
                    <Bar
                        id="my-chart-id"
                        :options="chartOptions"
                        :data="chartData"
                    />
                </div>
            </div>
            <div class="col-md-4 ">
                <div class="card px-4 py-3">
                    <Pie :data="piechartData" :options="chartOptions" />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { Bar } from 'vue-chartjs'
import { Pie } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement } from 'chart.js'
ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale, ArcElement)

export default{
    components: {Bar, Pie},
    data(){
        return{
            chartData: {
                labels: [],
                datasets: [ { 
                    data: [] ,
                    backgroundColor: '#87CEEB',
                    label: "Payments by classes"
                } ]
            },
            piechartData:{
                labels: ['Total Fee', 'Total Paid', 'Outstanding', 'Credit'],
                datasets: [
                    {
                    backgroundColor: ['#41B883', '#E46651', '#00D8FF', '#DD1B16'],
                    data: [40, 20, 80, 10]
                    }
                ]
            },
            chartOptions: {
                responsive: true
            }
        }
    },
    methods:{
        getAnalytics(){
            axios.get('/fee-analytics').then((response)=>{
                console.log(response.data)
                this.chartData = {
                    labels: response.data.bar_label,
                    datasets: [{
                        label: 'Payments by classes',
                        backgroundColor: '#87CEEB',
                        data: response.data.bar_data
                    }]
                }

                this.piechartData = {
                    labels: ['Total Fee', 'Total Paid', 'Outstanding', 'Credit'],
                    datasets: [
                    {
                    backgroundColor: ['#17a2b8', '#28a745','#ffc107', '#dc3545'],
                    data: [
                        response.data.total_fee,
                        response.data.total_paid,
                        response.data.outstanding,
                        response.data.credit
                    ]

                    }
                ]
                }

                let data = {
                        total_fee: response.data.total_fee,
                        total_paid: response.data.total_paid,
                        outstanding: response.data.outstanding,
                        credit: response.data.credit
                }

                this.$emit('data', data)

            })
        }
    },
    mounted(){
        this.getAnalytics()
    }
}
</script>