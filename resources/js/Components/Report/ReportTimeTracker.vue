<script setup>
    import "ag-grid-community/styles/ag-grid.css";
    import "ag-grid-community/styles/ag-theme-alpine.css";
    import { AgGridVue } from "ag-grid-vue3";
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import Datepicker from '@vuepic/vue-datepicker';
    import '@vuepic/vue-datepicker/dist/main.css';
    import { computed, ref } from 'vue';


</script>

<script>

export default {
    components: {
        AgGridVue,
        Datepicker 
    },
    data() {
        return {
            date,
            format,
            handleDate,
            columnDefs: [
                {   headerName: "Date", 
                    field: "date",
                    filter: 'agDateColumnFilter',
                    filterParams: filterParams,
                },
                { headerName: "User Name", field: "user_name" },
                { headerName: "User Email", field: "user_email" },
                { headerName: "Worked time", field: "workedTimeFormat" },
                { headerName: "Break time", field: "breakTimeFormat" },
                { headerName: "Check in", field: "initialTrack" },
                { headerName: "Check out", field: "lastTrack" },
            ],
            gridApi: null,
            columnApi: null,
            defaultColDef: {
                flex: 1,
                minWidth: 100,
                sortable: true,
                filter: true,
                floatingFilter: true,
            },
            rowData: null        
        };
    },
    created() {
        const startDate = new Date(new Date().setDate(new Date().getDate() - 1));
        const endDate = new Date();
        date.value = [startDate, endDate];
    },
    methods: {
        onBtnExport() {
            this.gridApi.exportDataAsCsv();
        },
        onRefreshTable() {
            this.onFetchData().then((data) => this.gridApi.setRowData(data))
        },
        onFetchData() {
            const from  = formatDateInYYYYMMDD(date.value[0]);
            const to    = formatDateInYYYYMMDD(date.value[1]);
            return fetch('/sanctum/csrf-cookie').then(() => fetch(`api/report/time-tracker?dateFrom=${from}&dateTo=${to}`)
                .then((resp) => resp.json())
            )
        },
        onGridReady(params) {
            this.gridApi = params.api;
            this.gridColumnApi = params.columnApi;
            const updateData = (data) => params.api.setRowData(data);
            this.onFetchData().then((data) => updateData(data)).then((data) => console.log('200'));
        },
    }
}

const filterParams = {
  comparator: (filterLocalDateAtMidnight, cellValue) => {
    var dateAsString = cellValue;
    if (dateAsString == null) return -1;
    var dateParts = dateAsString.split('/');
    var cellDate = new Date(
      Number(dateParts[2]),
      Number(dateParts[1]) - 1,
      Number(dateParts[0])
    );
    if (filterLocalDateAtMidnight.getTime() === cellDate.getTime()) {
      return 0;
    }
    if (cellDate < filterLocalDateAtMidnight) {
      return -1;
    }
    if (cellDate > filterLocalDateAtMidnight) {
      return 1;
    }
  },
  browserDatePicker: true,
  minValidYear: 2000,
  maxValidYear: 2025,
  inRangeFloatingFilterDateFormat: 'YYYY-MM-DD',
};

const date = ref();

const formatDateInYYYYMMDD = (date) => {
    const day   = date.getDate();
    const month = date.getMonth() + 1;
    const year  = date.getFullYear();
    return `${year}-${month}-${day}`;
}

const format = (date) => {
    let from  = formatDateInYYYYMMDD(date[0]);
    let to    = formatDateInYYYYMMDD(date[1]);
    return `Display from ${from} until ${to}`;
}

const handleDate = (modelData) => {
     date.value = modelData;
} 

</script>



<template>

    <PrimaryButton v-on:click="onBtnExport()" class="mb-2" :class="{ 'opacity-25': false }" :disabled="false">
        Export CSV
    </PrimaryButton>

    <Datepicker 
    @update:modelValue="handleDate"
    @closed="onRefreshTable"
    v-model="date" 
    class="w-45 mb-2" 
    :format="format" 
    range />
    
  <ag-grid-vue
    style="height: 500px"
    class="ag-theme-alpine"
    :columnDefs="columnDefs"
    @grid-ready="onGridReady"
    :defaultColDef="defaultColDef"
    :pagination="true"
    :rowData="rowData">
    </ag-grid-vue>
</template>