<script setup>
import "ag-grid-community/styles/ag-grid.css";
import "ag-grid-community/styles/ag-theme-alpine.css";
import { AgGridVue } from "ag-grid-vue3";
</script>

<script>
export default {
    components: {
        "ag-grid-vue": AgGridVue
    },
    data() {
        return {
            columnDefs: [
                { headerName: "Date", field: "date" },
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
            },
            rowData: null        
        };
    },
    methods: {
        onGridReady(params) {
            this.gridApi = params.api;
            this.gridColumnApi = params.columnApi;

            const updateData = (data) => params.api.setRowData(data);

            fetch('api/report/time-tracker')
                .then((resp) => resp.json())
                .then((data) => updateData(data));

        }
    }
}
</script>


<template>
  <ag-grid-vue
    style="height: 500px"
    class="ag-theme-alpine"
    :columnDefs="columnDefs"
    @grid-ready="onGridReady"
    :defaultColDef="defaultColDef"
    :rowData="rowData">
    </ag-grid-vue>
</template>