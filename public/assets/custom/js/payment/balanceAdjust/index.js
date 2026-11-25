let selectedForm = $("#submitForm");

// Get the current URL of the window
const BASE_URL = window.location.origin + "/payment/adjust/balance";

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

    $("#openAdjustBalanceModal").on("click", function () {
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

        $.ajax({
            url: BASE_URL + "/store",
            data: formData,
            type: "POST",
            cache: false,
            contentType: false,
            processData: false,
            success: function (response) {
                if (response?.statusCode == 200 || response?.statusCode == 201) {
                    $("#showModal").modal("hide");
                    formReset();
                    toastr.success(response?.message);
                    if (table) {
                        table.draw();
                    }
                }
            },
            error: handleError,
        });
    });
}

function formReset() {
    $("#submitForm").trigger("reset");
    $("#kt_type").val("").trigger("change");
    $("#kt_user_id").val("").trigger("change");
}

function openModal() {
    loader(selectedForm, false);
    $("#kt_gateway_id").val(null);
    $(".modal-title").text("Balance Adjust");
    $(".btnSubmit").text("SEND");
    $("#showModal").modal("show");
}

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

const getAdjustTypeBadgeHtml = (data) => {
    if (!data) return "";

    const typeKey = String(data).toLowerCase();
    const isDebit = typeKey === "debit" || typeKey === "minus";
    const label = isDebit ? "MINUS" : "ADD";
    const badgeClass = isDebit ? "minus" : "add";

    return `<span class="type-badge ${badgeClass}">${label}</span>`;
};

const getAdjustStatusBadgeHtml = (data) => {
    if (!data) return "";

    const typeKey = String(data).toLowerCase();
    const isAdd = typeKey === "add" || typeKey === "credit";

    if (isAdd) {
        return `<span class="status-badge transferred">TRANSFERRED</span>`;
    }

    return `<span class="status-badge minus">MINUS</span>`;
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

if ($("#kt_table_balance_adjust").length) {
    table = $("#kt_table_balance_adjust").DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: false,
        ajax: {
            url: BASE_URL + "/",
            data: function (d) {
                d.search = search.val();
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
                className: "user-cell",
            },
            {
                data: "balance",
                name: "balance",
            },
            {
                data: "type",
                render: function (data) {
                    return getAdjustTypeBadgeHtml(data);
                },
            },
            {
                data: "created_at",
                render: formatTableDate,
            },
            {
                data: "action",
                name: "action",
                orderable: false,
                searchable: false,
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
                className: "btn btn-success btn-sm adjust-export-btn",
                title: "Adjust Balance",
                filename: "adjust_balance",
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
                className: "btn btn-danger btn-sm adjust-export-btn",
                title: "Adjust Balance",
                filename: "adjust_balance",
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
}

$(document).on("click", ".showBalanceAdjustBtn", function () {
    const $tr = $(this).closest("tr");
    if (!table || !$tr.closest("#kt_table_balance_adjust").length) {
        return;
    }

    const rowData = table.row($tr).data();
    if (!rowData) {
        return;
    }

    $("#modal_adjust_id").text(rowData.id ?? "");
    $("#modal_adjust_user").html(rowData.user ?? "");
    $("#modal_adjust_amount").text(rowData.balance ?? "");
    $("#modal_adjust_type").html(getAdjustStatusBadgeHtml(rowData.type));
    $("#modal_adjust_created_on").text(formatModalDate(rowData.created_at));

    $("#showBalanceAdjustDetailModal").modal("show");
});

$("#kt_user_id").on("change", function () {
    let user_id = $(this).val();
    $.ajax({
        url: BASE_URL + "/get-account-data/" + user_id,
        type: "GET",
        success: function (response) {
            loader(selectedForm, false);
            let accountData = response.account;

            if (
                response?.success &&
                response?.statusCode === 200 &&
                accountData
            ) {
                $("#kt_current_balance").val(accountData.current_balance);
            }
        },
    });
});

$("#kt_balance").on("keyup", function () {
    let currentBalance = parseFloat($("#kt_current_balance").val()) || 0;

    let enteredAmount = parseFloat($(this).val()) || 0;

    if ($("#kt_type").val() == "minus" && enteredAmount > currentBalance) {
        toastr.error("This user has insufficient balance to his account", "Error!");
    }
});
