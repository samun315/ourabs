let selectedForm = $("#submitForm");

// Get the current URL of the window
const BASE_URL = window.location.origin + "/marchant";

let search = $("#search");

$(".detailsDiv").hide();

$(".payment_gateway").on("change", function () {
    let gatewayId = $(this).val();
    $.ajax({
        type: "GET",
        url: `${BASE_URL}/order/balance/gateway-info/${gatewayId}`,
        dataType: "json",
        success: (response) => {
            if (response?.success && response?.statusCode === 200) {
                const { gatewayInfo } = response;
                let amount = "";
                let total_diamond = "";
                $(".gateway_name_column").html(gatewayInfo?.gateway_name);
                $(".gateway_details").html(gatewayInfo?.details);
                $(".kt_currency").html(gatewayInfo?.currency_code);
                $(".rate-box").html(gatewayInfo?.rate);
                $("#kt_rate").val(gatewayInfo?.rate);

                if ($("#kt_amount").val() == "") {
                    amount = 0;
                    total_diamond = (amount / gatewayInfo?.rate).toFixed(2);
                } else {
                    amount = $("#kt_amount").val();
                    total_diamond = (amount / gatewayInfo?.rate).toFixed(2);
                }
                $("#kt_diamond_quantity").val(total_diamond);
                $(".bd_amount").html(amount);
                $(".diamond_amount").html(total_diamond);

                $(".detailsDiv").show();
            } else {
                $(".detailsDiv").hide();
                toastr.error(
                    response?.message || "An unexpected error occurred."
                );
            }
        },
        error: (jqXHR) => {
            loader(selectedForm, false);

            if (jqXHR.status === 422) {
                displayValidationErrors(jqXHR.responseJSON?.errors);
            } else {
                toastr.error(
                    jqXHR.responseJSON?.message ||
                        "An unexpected error occurred."
                );
            }
        },
    });
});

$("#kt_amount").on("keyup", function () {
    let bd_amount = $(this).val();
    let rate = $("#kt_rate").val();
    let diamond_amount = (bd_amount / rate).toFixed(2);

    $(".bd_amount").html(bd_amount);
    $(".diamond_amount").html(diamond_amount);
    $("#kt_diamond_quantity").val(diamond_amount);
});

$("#kt_attachment").on("change", function (event) {
    let file = event.target.files[0]; // Get the selected file
    if (file) {
        let reader = new FileReader();
        reader.onload = function (e) {
            $("#preview").attr("src", e.target.result).show(); // Set image source and show it
        };
        reader.readAsDataURL(file); // Read file as Data URL
    } else {
        $("#preview").hide(); // Hide preview if no file selected
    }
});

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

const getOrderStatusBadgeHtml = (data) => {
    if (!data) return "";

    const statusKey = String(data).toLowerCase();
    let badgeClass = "cancelled";

    if (statusKey === "pending") {
        badgeClass = "pending";
    } else if (statusKey === "paid") {
        badgeClass = "paid";
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
        return String(data).replace(/<[^>]*>/g, "").trim();
    },
};

let table = null;

if ($("#kt_table_order").length) {
    table = $("#kt_table_order").DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: false,
        ajax: {
            url: BASE_URL + "/order/balance",
            data: function (d) {
                d.search = search.val();
            },
        },
        columns: [
            {
                data: "id",
                name: "id",
            },
            {
                data: "amount",
                name: "amount",
            },
            {
                data: "diamond_quantity",
                name: "diamond_quantity",
            },
            {
                data: "status",
                render: function (data) {
                    return getOrderStatusBadgeHtml(data);
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
                className: "btn btn-success btn-sm order-export-btn",
                title: "Order Balance List",
                filename: "order_balance_list",
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
                className: "btn btn-danger btn-sm order-export-btn",
                title: "Order Balance List",
                filename: "order_balance_list",
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
        ],
    });

    search.keyup(function () {
        table.draw();
    });
}

$(document).on("click", ".showOrderBtn", function () {
    let id = $(this).attr("data-id");
    getOrderBalanceDetails(id);
    $("#showModal").modal("show");
});

function getOrderBalanceDetails(id) {
    $.ajax({
        type: "GET",
        url: BASE_URL + "/order/balance/details/" + id,
        dataType: "JSON",
        success: function (response) {
            if (response?.success && response?.statusCode === 200) {
                let orderDetails = response.data;

                let amount = "";
                let total_diamond = "";
                $(".gateway_name_column").html(orderDetails?.gateway_name);
                $(".gateway_details").html(orderDetails?.details);
                $(".kt_currency").html(orderDetails?.currency_code);
                $(".rate-box").html(orderDetails?.rate);

                let orderStatus = "";

                if (orderDetails?.status == "Pending") {
                    orderStatus = "badge badge-warning";
                } else if (orderDetails?.status == "Paid") {
                    orderStatus = "badge badge-success";
                } else {
                    orderStatus = "badge badge-danger";
                }
                let kt_status = `<span class="${orderStatus}">${orderDetails?.status}</span>`;

                $(".kt_transaction_id").html(orderDetails?.transaction_id);
                $(".kt_status").html(kt_status);
                $(".kt_date_time").html(formatDate(orderDetails?.updated_at));
                $(".bd_amount").html(orderDetails?.amount);
                $(".diamond_amount").html(orderDetails?.diamond_quantity);

                // $("#uploaded_photo").html();
                document.getElementById("uploaded_photo").src =
                    window.location.origin +
                    "/uploads/order/" +
                    orderDetails?.attachment_url;

                document.getElementById("link_image").href =
                    window.location.origin +
                    "/uploads/order/" +
                    orderDetails?.attachment_url;
            }
        },
        error: function (data) {
            toastr.error(data?.message || "An unexpected error occurred.");
        },
    });
}

// ORDER BALANCE APPROVAL START

let serchId = $("#search");
let paidTable = null;

if ($("#kt_table_order_approval").length) {
    paidTable = $("#kt_table_order_approval").DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: false,
        ajax: {
            url: BASE_URL + "/all/order/balance",
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
                className: "user-cell",
            },
            {
                data: "gateway_name",
                name: "gateway_name",
            },
            {
                data: "amount",
                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }
                    if (!data) return "";
                    return `TK ${data} ${row.currency_code ?? ""}`;
                },
            },
            {
                data: "diamond_quantity",
                className: "diamond-cell",
                render: function (data, type, row) {
                    if (type !== "display") {
                        return data;
                    }
                    if (!data) return "";
                    const rateNote = row.rate
                        ? `<div class="text-muted" style="font-size:11px;">1 IMO Diamond = ${row.rate} ${row.currency_code ?? ""}</div>`
                        : "";
                    return `${data} <i class="fas fa-gem order-diamond-icon" title="Diamond"></i>${rateNote}`;
                },
            },
            {
                data: "status",
                render: function (data) {
                    return getOrderStatusBadgeHtml(data);
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
                className: "btn btn-success btn-sm order-export-btn",
                title: "Order Approval List",
                filename: "order_approval_list",
                extension: ".xlsx",
                exportOptions: {
                    columns: ":visible:not(:first-child):not(:last-child)",
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
                className: "btn btn-danger btn-sm order-export-btn",
                title: "Order Approval List",
                filename: "order_approval_list",
                extension: ".pdf",
                exportOptions: {
                    columns: ":visible:not(:first-child):not(:last-child)",
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
        paidTable.draw();
    });
}

$(document).on("click", ".paidBtn,.cancelledBtn", function () {
    let id = $(this).attr("data-id");
    let status = $(this).attr("data-status");
    let user_id = $(this).attr("data-user");
    let order_amount = $(this).attr("data-amount");
    updateOrderBalanceRequestStatus(id, user_id, status, order_amount);
});

function updateOrderBalanceRequestStatus(id, user_id, status, order_amount) {
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
                    url: BASE_URL + "/all/order/balance/update-status/" + id,
                    data: { status, user_id, order_amount },
                    dataType: "JSON",
                    success: function (response) {
                        if (response?.statusCode === 200) {
                            toastr.success(response?.message, "Success!");
                            if (paidTable) {
                                paidTable.draw();
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
