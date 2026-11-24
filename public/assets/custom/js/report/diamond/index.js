// Get the current URL of the window
const BASE_URL = window.location.origin + "/report/total-diamonds";

let search = $("#search");
let role_id = $("#kt_role_id");

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

let table = $("#kt_total_diamonds_table").DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    scrollX: false,
    ajax: {
        url: BASE_URL + "/",
        data: function (d) {
            d.search = search.val();
            d.role_id = role_id.val();
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
            className: "user-cell",
        },
        {
            data: "role_name",
            name: "role_name",
        },
        {
            data: "current_balance",
            name: "current_balance",
            render: function (data, type) {
                if (type !== "display") {
                    return data;
                }
                if (data == null || data === "") return "";
                return `${data} <i class="fas fa-gem diamond-icon" title="Diamond"></i>`;
            },
        },
    ],
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
            className: "btn btn-success btn-sm diamond-export-btn",
            title: "Total Diamonds",
            filename: "total_diamonds",
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
            className: "btn btn-danger btn-sm diamond-export-btn",
            title: "Total Diamonds",
            filename: "total_diamonds",
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

search.keyup(function () {
    table.draw();
});

role_id.change(function () {
    table.draw();
});
