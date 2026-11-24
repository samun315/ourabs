let selectedForm = $("#submitForm");

// Get the current URL of the window
const BASE_URL = window.location.origin + "/marchant";

let search = $("#search");
let validate = null;

if (selectedForm.length) {
    validate = selectedForm.validate({
        rules: {
            name: "required",
        },
        onsubmit: true,
    });

    $(".formReset").on("click", function () {
        formReset();
    });

    $("#openTransferBalanceModal").on("click", function () {
        openModal();
    });

    selectedForm.submit(function (event) {
        event.preventDefault();

        if (!validate.valid()) return;

        loader(selectedForm, true);

        let formData = new FormData($("form#submitForm")[0]);

        // Setup CSRF token
        setCSRFToken();

        $(".error").remove();

        let transferId = $("#kt_transfer_balance_id").val();
        let url = "";
        if (transferId) {
            url = BASE_URL + "/sub-distributor/transfer/balance/update/" + transferId;
            formData.append("_method", "PUT");
        } else {
            url = BASE_URL + "/sub-distributor/transfer/balance/store";
        }

        $.ajax({
            url: url,
            data: formData,
            type: "POST", // Always use POST for FormData, append _method for PUT
            cache: false,
            contentType: false,
            processData: false,
            success: handleSuccessWithModal,
            error: handleError,
        });
    });
}

function formReset() {
    $("#submitForm").trigger("reset");
    $("#kt_transfer_to_user").val("").trigger("change");
}

function openModal() {
    formReset();
    loader(selectedForm, false);
    $("#kt_transfer_balance_id").val(null);
    $("#showModal").modal("show");
}

const formatDate = (data) => {
    if (!data) return "";

    const date = new Date(data);
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
    hour = hour % 12 || 12;

    let minute = date.getMinutes().toString().padStart(2, "0");

    return `${day} ${month}, ${date.getFullYear()} ,${hour}:${minute} ${amPm}`;
};

const formatTableDate = (data) => {
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

const getTransferStatusBadgeHtml = (data) => {
    if (!data) return "";

    const statusKey = String(data).toLowerCase();
    let badgeClass = "cancelled";

    if (statusKey === "pending") {
        badgeClass = "pending";
    } else if (statusKey === "transferred") {
        badgeClass = "transferred";
    }

    return `<span class="status-badge ${badgeClass}">${String(data).toUpperCase()}</span>`;
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

let table = null;

if ($("#kt_table_transfer_balance").length) {
    table = $("#kt_table_transfer_balance").DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: false,
        ajax: {
            url: BASE_URL + "/sub-distributor/transfer/balance",
            data: function (d) {
                d.search = search.val();
            },
        },
        columns: [
            {
                data: "id",
                name: "balance_transfers.id",
            },
            {
                data: "from_user",
                name: "from_user",
                className: "user-cell",
            },
            {
                data: "to_user",
                name: "to_user",
                className: "user-cell",
            },
            {
                data: "amount",
                name: "amount",
            },
            {
                data: "status",
                render: function (data) {
                    return getTransferStatusBadgeHtml(data);
                },
            },
            {
                data: "created_at",
                render: formatTableDate,
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
                className: "btn btn-success btn-sm transfer-export-btn",
                title: "Sub Distributor Transfer Balance",
                filename: "sub_distributor_transfer_balance",
                extension: ".xlsx",
                exportOptions: {
                    columns: ":visible",
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
                className: "btn btn-danger btn-sm transfer-export-btn",
                title: "Sub Distributor Transfer Balance",
                filename: "sub_distributor_transfer_balance",
                extension: ".pdf",
                exportOptions: {
                    columns: ":visible",
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
}

// APPROVE BALANCE REQUEST START

let serchId = $("#search");
let approveTable = null;

if ($("#kt_table_transfer_sub_approve").length) {
    approveTable = $("#kt_table_transfer_sub_approve").DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: false,
        ajax: {
            url: BASE_URL + "/all/sub-distributor/transfer/balance",
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
                data: "from_user",
                name: "from_user",
                className: "user-cell",
            },
            {
                data: "to_user",
                name: "to_user",
                className: "user-cell",
            },
            {
                data: "amount",
                name: "amount",
            },
            {
                data: "status",
                render: function (data) {
                    return getTransferStatusBadgeHtml(data);
                },
            },
            {
                data: "created_at",
                render: formatTableDate,
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
                className: "btn btn-success btn-sm transfer-export-btn",
                title: "All Sub Distributor Transfer Balance",
                filename: "all_sub_distributor_transfer_balance",
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
                className: "btn btn-danger btn-sm transfer-export-btn",
                title: "All Sub Distributor Transfer Balance",
                filename: "all_sub_distributor_transfer_balance",
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

    serchId.keyup(function () {
        approveTable.draw();
    });
}

$(document).on("click", ".transferredBtn,.cancelledBtn", function () {
    let id = $(this).attr("data-id");
    let status = $(this).attr("data-status");
    updateTransferBalanceStatus(status, id);
});

function updateTransferBalanceStatus(status, id) {
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
                    url: BASE_URL + "/all/sub-distributor/transfer/balance/update-status/" + id,
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

$("#kt_amount").on("keyup",function(){
    let currentBalance = parseFloat($("#kt_current_balance").val()) || 0;

    let enteredAmount = parseFloat($(this).val()) || 0;

     if (enteredAmount > currentBalance) {
        toastr.error('You have insufficient balance to your account', "Error!");
     }
});
