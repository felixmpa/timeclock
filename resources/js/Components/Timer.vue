<script setup>
import axios from 'axios';
import PrimaryButton from '@/Components/PrimaryButton.vue';
</script>

<script>
export default{
    data(){
        return{
            sec: 0,
            min: 0,
            hour: 0,
            timer: null,
            user: null,
            intervalList: [],
            states: {
                break: "Break",
                lunch: "At Lunch",
                huddle: "In Huddle",
                away: "Away"
            }
        }
    },
    created()
    {
        this.getTracker()
    },
    methods: {
        getTracker(e) {
            axios.get('/sanctum/csrf-cookie').then(response => {
                axios.get('/api/time-tracker')
                .then(response => {
                    this.hour = response.data.hour;
                    this.min  = response.data.min;
                    this.sec  = response.data.sec;
                    this.user = response.data.user;
                    this.intervalList = response.data.intervalList;

                    if(this.user.is_time_tracker_started == 1 && this.intervalList.length > 0)
                       this.timer = this.interval()

                })
                .catch(function(error) {
                    console.log(error);
                });
            });
        },
        postTracker(e, isCheck) {
            axios.get('/sanctum/csrf-cookie').then(response => {
                let existingObj  = this;
                axios.post('/api/time-tracker', { is_check_in: isCheck })
                .then(response => {
                    
                })
                .catch(function(error) {
                    console.log(error);
                });
            });
        },
        zfill(number){
            return number.toString().padStart(2,0)
        },
        play(){
            if(this.timer === null){
                this.postTracker(this, 1)
                this.getTracker()
            }else{
                this.postTracker(this, 0)
                clearInterval(this.timer);
                this.timer = null;
            }
        },
        interval(){
            return setInterval(()=> this.playing(), 1000)
        },
        playing(){
            this.sec++
            if(this.sec >= 59){
                this.sec = 0;
                this.min++;
            }
            if(this.min >= 59){
                this.min = 0;
                this.hour++;
            }
        },
        pause(){
            
        },
        clear(){
            if(this.timer !== null){
                clearInterval(this.timer)
                this.timer = null
            }
            this.sec = 0;
            this.min = 0;
            this.hour = 0;
            this.clearIntervalList();
        },
        clearIntervalList(){
            this.intervalList = []
            console.log(this.intervalList)
        },
        setBreak(e, id) {
            axios.get('/sanctum/csrf-cookie').then(response => {
                let existingObj  = this;
                axios.put('/api/time-tracker/'+id, { note: e.target.value })
                .then(response => {
                    
                })
                .catch(function(error) {
                    console.log(error);
                });
            });
        }
    }
}
</script>

<template>
 
    <a class="inline-flex items-center px-4 py-2 bg-blue-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 active:bg-blue-900 focus:outline-none focus:border-gray-900 focus:shadow-outline-gray transition ease-in-out duration-150 ml-4">{{zfill(hour)}}:{{zfill(min)}}:{{zfill(sec)}}</a>

    <PrimaryButton @click="play" class="ml-4" :class="{ 'opacity-25': false }" :disabled="false">
        {{timer !== null ? "PAUSE" :"START" }}
    </PrimaryButton>

    <div class="flow-root pt-8 ml-4" v-show="intervalList.length > 0">
    <ul role="list" class="-mb-8">
        <li v-for="interval in intervalList" :key="interval">
        <div class="relative pb-8">
            <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
            <div class="relative flex space-x-3">
            <div>
                <span class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-8 ring-white">
                    <!-- Heroicon name: solid/user -->
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </span>
            </div>
            <div class="min-w-0 flex-1 pt-1.5 flex justify-between space-x-4">
                <div>
                    <p class="text-sm text-gray-500" v-if="interval.is_check_in === 1"> Check In </p>
                    <p class="text-sm text-gray-500" v-if="interval.is_check_in === 0"> Pause for 
                        
                        <select v-model="interval.note" @change="setBreak($event, interval.id)" class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 bg-gray-200 hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                            <option :value="states.break">{{ states.break }}</option>
                            <option :value="states.lunch">{{ states.lunch }}</option>
                            <option :value="states.huddle">{{ states.huddle }}</option>
                            <option :value="states.away">{{ states.away }}</option>
                        </select>


                    </p>
                    <span class="text-gray-400 text-xs">at {{ interval.tracked_at }}</span>
                </div>
                <div class="text-right text-sm whitespace-nowrap text-gray-500">
                
                    <time v-if="interval.is_check_in === 0">{{ interval.duration }}</time>                   

                </div>
            </div>
            </div>
        </div>
        </li>
    </ul>
    </div>



</template>
