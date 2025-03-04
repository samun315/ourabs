
$(document).ready(function () {
    // Initialize date range variables
    const start = moment().subtract(29, "days");
    const end = moment();

    // Callback to update the displayed date range
    function updateDateRange(start, end) {
        $("#kt_start_to_end_date").html(
            `${start.format("D MMM,YYYY")} - ${end.format("D MMM,YYYY")}`
        );
    }

    // Date range picker configuration
    $("#kt_start_to_end_date").daterangepicker(
        {
            startDate: start,
            endDate: end,
            locale: {
                format: "D MMM, YYYY", // Date format for the date picker
            },
            ranges: {
                Today: [moment(), moment()],
                Yesterday: [
                    moment().subtract(1, "days"),
                    moment().subtract(1, "days"),
                ],
                "Last 7 Days": [moment().subtract(6, "days"), moment()],
                "Last 30 Days": [moment().subtract(29, "days"), moment()],
                "This Month": [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
                "Last Month": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
        },
        updateDateRange
    );

    // Initialize the display
    updateDateRange(start, end);
});

// Get the current URL of the window
const BASE_URL = window.location.origin + "/report/transaction-history";

let search = $("#search");
let type = $("#kt_type");
let date = $("#kt_start_to_end_date");

const formatDate = (data) => {
    if (!data) return "";

    const date = new Date(data);
    // Month and day as textual representations
    let monthNames = [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ];

    let month = monthNames[date.getMonth()];
    let day = date.getDate().toString().padStart(2, "0");
    let hour = date.getHours().toString().padStart(2, "0");

    let amPm = hour >= 12 ? "PM" : "AM";
    hour = hour % 12 || 12; // Convert 0 to 12 for 12 AM

    let minute = date.getMinutes().toString().padStart(2, "0");
    let second = date.getSeconds().toString().padStart(2, "0");

    return `${day} ${month}, ${date.getFullYear()} ,${hour}:${minute} ${amPm}`;
};

let table = $("#kt_table_transaction_history").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: BASE_URL + "/",
        data: function (d) {
            d.search = search.val();
            d.type = type.val();
            d.date = date.val();
        },
    },
    columns: [
        {
            data: "DT_RowIndex",
            name: "DT_RowIndex",
            orderable: false,
            searchable: false,
        },
        {
            data: "full_name",
            name: "full_name",
        },
        {
            data: "type",
            render: function (data, type, row) {
                if (!data) return "";
            
                let status = 'badge badge-primary';
          
                return `<p class="${status}">${data}</p>`;
            },
        }, 
         {
            data: "refference",
            name: "refference",
        },
        {
            data: "amount",
            name: "amount",
        }, 
        {
            data: "before_balance",
            name: "before_balance",
        }, 
         {
            data: "after_balance",
            name: "after_balance",
        },
        {
            data: "created_at",
            render: formatDate,
        },
    ],
    columnDefs: [
        {
            targets: "_all",
            defaultContent: "",
        },
    ],
    paging: true, // Enables pagination
    pageLength: 10, // Show 5 records per page
    lengthMenu: [10, 25, 50, 75, 100, 200], // Dropdown for selecting number of rows
    dom:
        '<"row"<"col-sm-12"tr>>' + // Table rows
        '<"row"<"col-sm-6 d-flex align-items-center"l i><"col-sm-6 d-flex justify-content-end"p>>'+ // Pagination & info
        '<"row"<"col-sm-12 d-flex justify-content-end"B>>', // Length menu & buttons
    buttons: [
        {
            extend: "excelHtml5",
            text: "Excel",
            className: "btn btn-success btn-sm",
            attr: {
                style: "margin-top: 25px;padding: 0 10px; font-size: 12px; line-height: 1; height: 30px;",
            },
            exportOptions: {
                columns: ":not(:first-child)", // Exclude the first column (DT_RowIndex)
            },
        },
        {
            extend: "pdfHtml5",
            text: "PDF",
            className: "btn btn-danger btn-sm",
            attr: {
                style: "margin-top: 25px;padding: 0 10px; font-size: 12px; line-height: 1; height: 30px;",
            },
            exportOptions: {
                columns: ":not(:first-child)", // Exclude the first column (DT_RowIndex)
            },
        },
    ],
});

type.change(function () {
    table.draw();
});

date.change(function () {
    table.draw();
});