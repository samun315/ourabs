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
                format: "D MMM, YYYY",
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
const BASE_URL = window.location.origin + "/report/transfer";

let date = $("#kt_start_to_end_date");

const formatDate = (data) => {
    if (!data) return "";

    const dateObj = new Date(data);
    if (Number.isNaN(dateObj.getTime())) return data;

    const year = dateObj.getFullYear();
    const month = (dateObj.getMonth() + 1).toString().padStart(2, "0");
    const day = dateObj.getDate().toString().padStart(2, "0");

    return `${year}-${month}-${day}`;
};

(function registerExportLibs() {
    if (window.JSZip && $.fn.dataTable && $.fn.dataTable.Buttons) {
        $.fn.dataTable.Buttons.jszip(window.JSZip);
    }
    if (window.pdfMake && $.fn.dataTable && $.fn.dataTable.Buttons) {
        $.fn.dataTable.Buttons.pdfMake(window.pdfMake);
    }
    if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.buttons) {
        if ($.fn.dataTable.ext.buttons.excelHtml5) {
            $.fn.dataTable.ext.buttons.excelHtml5.available = function () {
                return typeof window.JSZip !== "undefined";
            };
        }
        if ($.fn.dataTable.ext.buttons.pdfHtml5) {
            $.fn.dataTable.ext.buttons.pdfHtml5.available = function () {
                return typeof window.pdfMake !== "undefined";
            };
        }
    }
})();

const exportBodyFormat = {
    body: function (data) {
        if (data == null) return "";
        return String(data).replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
    },
};

let table = $("#kt_table_transfer_report").DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    scrollX: false,
    ajax: {
        url: BASE_URL + "/",
        data: function (d) {
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
            data: "created_date",
            name: "created_date",
            render: formatDate,
        },
        {
            data: "total_amount",
            name: "total_amount",
        },
    ],
    order: [[1, "desc"]],
    columnDefs: [
        {
            targets: "_all",
            defaultContent: "",
        },
    ],
    paging: true,
    pageLength: 20,
    lengthMenu: [10, 20, 25, 50, 75, 100, 200],
    dom:
        '<"row align-items-center mb-2"<"col-6"l><"col-6 text-end"B>>' +
        '<"row"<"col-sm-12"tr>>' +
        '<"row mt-2"<"col-sm-6"i><"col-sm-6 d-flex justify-content-end"p>>',
    buttons: [
        {
            text: "Excel",
            className: "btn btn-success btn-sm transfer-report-export-btn",
            title: "Transfer Report",
            filename: "transfer_report",
            extension: ".xlsx",
            exportOptions: {
                columns: ":visible:not(:first-child)",
                format: exportBodyFormat,
            },
            action: function (e, dt, node, config) {
                if (!window.JSZip) {
                    toastr.error("Excel library failed to load. Please refresh.");
                    return;
                }
                $.fn.dataTable.Buttons.jszip(window.JSZip);
                const excel = $.fn.dataTable.ext.buttons.excelHtml5;
                excel.action.call(this, e, dt, node, $.extend({}, excel, config));
            },
        },
        {
            text: "PDF",
            className: "btn btn-danger btn-sm transfer-report-export-btn",
            title: "Transfer Report",
            filename: "transfer_report",
            extension: ".pdf",
            exportOptions: {
                columns: ":visible:not(:first-child)",
                format: exportBodyFormat,
            },
            action: function (e, dt, node, config) {
                if (!window.pdfMake) {
                    toastr.error("PDF library failed to load. Please refresh.");
                    return;
                }
                $.fn.dataTable.Buttons.pdfMake(window.pdfMake);
                const pdf = $.fn.dataTable.ext.buttons.pdfHtml5;
                pdf.action.call(this, e, dt, node, $.extend({}, pdf, config));
            },
        },
    ],
});

date.change(function () {
    table.draw();
});
