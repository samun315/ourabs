let selectedForm = $("#submitForm");

// Get the current URL of the window
const BASE_URL = window.location.origin + "/marchant";

let search = $("#search");


$(".formReset").on("click", function () {
    formReset();
});

function formReset() {
    $("#submitForm").trigger("reset");
    $("#kt_mobile_number").val("").trigger("change");
}

const formatDate = (data) => {
    if (!data) return "";

    const date = new Date(data);
    if (Number.isNaN(date.getTime())) return data;

    const year = date.getFullYear();
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const day = date.getDate().toString().padStart(2, "0");
    let hour = date.getHours();
    const amPm = hour >= 12 ? "PM" : "AM";
    hour = hour % 12 || 12;
    const minute = date.getMinutes().toString().padStart(2, "0");

    return `${year}-${month}-${day} ${hour.toString().padStart(2, "0")}:${minute} ${amPm}`;
};

const formatModalDate = (data) => {
    if (!data) return "";

    const date = new Date(data);
    if (Number.isNaN(date.getTime())) return data;

    const year = date.getFullYear().toString().slice(-2);
    const month = (date.getMonth() + 1).toString().padStart(2, "0");
    const day = date.getDate().toString().padStart(2, "0");
    let hour = date.getHours();
    const amPm = hour >= 12 ? "PM" : "AM";
    hour = hour % 12 || 12;
    const minute = date.getMinutes().toString().padStart(2, "0");

    return `${day}-${month}-${year} ${hour.toString().padStart(2, "0")}:${minute} ${amPm}`;
};

const getStatusBadgeHtml = (data) => {
    if (!data) return "";

    const statusKey = String(data).toLowerCase();
    let badgeClass = "cancelled";

    if (statusKey === "pending") {
        badgeClass = "pending";
    } else if (statusKey === "processing") {
        badgeClass = "processing";
    } else if (statusKey === "transferred") {
        badgeClass = "transferred";
    } else if (statusKey === "failed") {
        badgeClass = "failed";
    }

    return `<span class="status-badge ${badgeClass}">${String(data).toUpperCase()}</span>`;
};

const isMobileOrTablet = () => window.matchMedia("(max-width: 991.98px)").matches;

// Ensure export libs are registered; always show Excel/PDF buttons
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
        return String(data).replace(/<[^>]*>/g, "").trim();
    },
};

const exportButtons = [
    {
        text: "Excel",
        className: "btn btn-success btn-sm end-user-export-btn",
        title: "End User Balance Request",
        filename: "end_user_balance_request",
        extension: ".xlsx",
        exportOptions: {
            columns: ":visible:not(:last-child)",
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
        className: "btn btn-danger btn-sm end-user-export-btn",
        title: "End User Balance Request",
        filename: "end_user_balance_request",
        extension: ".pdf",
        exportOptions: {
            columns: ":visible:not(:last-child)",
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
];

let table = $("#kt_table_end_user_balance").DataTable({
    processing: true,
    serverSide: true,
    autoWidth: false,
    scrollX: false,
    ajax: {
        url: BASE_URL + "/end-user/balance/request",
        data: function (d) {
            d.search = search.val();
        },
    },
    columns: [
        {
            data: "balance_transfer_id",
            name: "balance_transfer_id",
            width: "8%",
        },
        {
            data: "imo_id",
            name: "imo_id",
            width: "16%",
        },
        {
            data: "amount",
            name: "amount",
            width: "10%",
        },
        {
            data: "status",
            width: "12%",
            render: function (data) {
                return getStatusBadgeHtml(data);
            },
        },
        {
            data: "bonus",
            name: "bonus",
            orderable: false,
            searchable: false,
            width: "8%",
            render: function (data) {
                const value = data ?? 0;
                return Number(value).toFixed(2);
            },
        },
        {
            data: "created_at",
            width: "18%",
            render: function (data) {
                return isMobileOrTablet() ? formatModalDate(data) : formatDate(data);
            },
        },
        {
            data: "action",
            name: "action",
            orderable: false,
            searchable: false,
            width: "10%",
        },
    ],
    columnDefs: [
        {
            targets: "_all",
            defaultContent: "",
        },
        {
            // Hide Bonus on mobile so the row stays one clean line
            targets: [4],
            visible: !window.matchMedia("(max-width: 767.98px)").matches,
        },
    ],
    paging: true,
    pageLength: isMobileOrTablet() ? 10 : 20,
    lengthMenu: [10, 20, 25, 50, 75, 100, 200],
    dom:
        '<"row align-items-center mb-2"<"col-6"l><"col-6 text-end"B>>' +
        '<"row"<"col-sm-12"tr>>' +
        '<"row mt-2"<"col-sm-6"i><"col-sm-6 d-flex justify-content-end"p>>',
    buttons: exportButtons,
});

$(window).on("resize", function () {
    const showBonus = !window.matchMedia("(max-width: 767.98px)").matches;
    if (table.column(4).visible() !== showBonus) {
        table.column(4).visible(showBonus, false);
        table.columns.adjust().draw(false);
    }
});

search.keyup(function () {
    table.draw();
});

$(document).on("click", ".showEndUserBalanceBtn", function () {
    const rowData = table.row($(this).closest("tr")).data();

    if (!rowData) {
        return;
    }

    $("#modal_imo_id").text(rowData.imo_id ?? "");
    $("#modal_amount").text(rowData.amount ?? "");
    $("#modal_status").html(getStatusBadgeHtml(rowData.status));
    $("#modal_created_on").text(formatModalDate(rowData.created_at));

    $("#showModal").modal("show");
});

// APPROVE BALANCE REQUEST START

let serchId = $("#search");
let approveTable = null;

if ($("#kt_table_end_user_balance_approve").length) {
    approveTable = $("#kt_table_end_user_balance_approve").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: BASE_URL + "/all/end-user/balance",
            data: function (d) {
                d.search = serchId.val();
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
                data: "user",
                name: "user",
            },
            {
                data: "amount",
                name: "amount",
                className: "text-center",
                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }

                    let badge = "badge badge-danger";
                    if (row.status == "Pending") {
                        badge = "badge badge-warning";
                    } else if (row.status == "Transferred") {
                        badge = "badge badge-success";
                    }

                    const status = row.status
                        ? `<p class="${badge}">${row.status}</p>`
                        : "";
                    const imoId = row.imo_id
                        ? `<div>${row.imo_id}</div>`
                        : "";

                    return `${data ?? ""}<br>${status}${imoId}`;
                },
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
        paging: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 75, 100, 200],
        dom:
            '<"row align-items-center"<"col-sm-6"l><"col-sm-6 d-flex justify-content-sm-end justify-content-start mt-2 mt-sm-0"B>>' +
            '<"row"<"col-sm-12"tr>>' +
            '<"row mt-2"<"col-sm-6"i><"col-sm-6 d-flex justify-content-end"p>>',
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: "btn btn-success btn-sm end-user-export-btn",
                title: "End User Balance Approve",
                exportOptions: {
                    columns: ":visible:not(:first-child)",
                },
            },
            {
                extend: "pdfHtml5",
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: "btn btn-danger btn-sm end-user-export-btn",
                title: "End User Balance Approve",
                exportOptions: {
                    columns: ":visible:not(:first-child)",
                },
            },
        ],
    });

    serchId.keyup(function () {
        approveTable.draw();
    });
}

$(document).on("click", ".transferredBtn,.cancelledBtn", function () {
    let id = $(this).attr("data-id");
    let status = $(this).attr("data-status");
    updateBalanceRequestStatus(status, id);
});

function updateBalanceRequestStatus(status, id) {
    Swal.fire({
        html: `Are you want to ${status} this?`,
        icon: "info",
        buttonsStyling: false,
        showCancelButton: true,
        confirmButtonText: "Yes, do it!",
        cancelButtonText: "Nope, cancel it",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-danger",
        },
    }).then(
        function (e) {
            if (e.value === true) {
                setCSRFToken();
                $.ajax({
                    type: "PUT",
                    url: BASE_URL + "/all/end-user/balance/update-status/" + id,
                    data: {status},
                    dataType: "JSON",
                    success: function (response) {
                        if (response?.statusCode === 200) {
                            toastr.success(response?.message, "Success!");
                            if (approveTable) {
                                approveTable.draw();
                            }
                        }
                    },
                    error: function (data) {
                        toastr.error(
                            data?.message || "An unexpected error occurred."
                        );
                    },
                });
            } else {
                e.dismiss;
            }
        },
        function (dismiss) {
            return false;
        }
    );
}

$(".amount").on("change",function(){
    let currentBalance = parseFloat($("#kt_current_balance").val()) || 0;

    let enteredAmount = parseFloat($(this).val()) || 0;

     if (enteredAmount > currentBalance) {
        toastr.error('You have insufficient balance to your account', "Error!");
     }
});


const imoIdInput = document.getElementById("kt_imo_id");
if (imoIdInput) {
    imoIdInput.addEventListener("keypress", function (event) {
        if (!/[0-9]/.test(event.key)) {
            event.preventDefault();
        }
    });
}
