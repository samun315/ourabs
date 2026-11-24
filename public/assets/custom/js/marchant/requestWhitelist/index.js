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

    $("#openWhitelistModal").on("click", function () {
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

        let whitelistId = $("#kt_whitelist_request_id").val();
        let url = "";
        if (whitelistId) {
            url = BASE_URL + "/request/whitelist/update/" + whitelistId;
            formData.append("_method", "PUT");
        } else {
            url = BASE_URL + "/request/whitelist/store";
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
}

function openModal() {
    formReset();
    loader(selectedForm, false);
    $("#kt_whitelist_request_id").val(null);
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

const getWhitelistStatusBadgeHtml = (data) => {
    if (!data) return "";

    const statusKey = String(data).toLowerCase();
    let badgeClass = "suspended";

    if (statusKey === "pending") {
        badgeClass = "pending";
    } else if (statusKey === "active") {
        badgeClass = "active";
    } else if (statusKey === "suspend" || statusKey === "suspended") {
        badgeClass = "suspended";
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

if ($("#kt_table_whitelist").length) {
    table = $("#kt_table_whitelist").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: BASE_URL + "/request/whitelist",
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
                data: "mobile_number",
                name: "mobile_number",
            },
            {
                data: "status",
                render: function (data) {
                    if (!data) return "";

                    let status = "";

                    if (data == "Pending") {
                        status = "badge badge-warning";
                    } else if (data == "Active") {
                        status = "badge badge-success";
                    } else {
                        status = "badge badge-danger";
                    }

                    return `<p class="${status} ">${data}</p>`;
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
            '<"row"<"col-sm-2"l><"col-sm-10 d-flex justify-content-end"B>>' +
            '<"row"<"col-sm-12"tr>>' +
            '<"row mt-2"<"col-sm-6"i><"col-sm-6 d-flex justify-content-end"p>>',
        buttons: [
            {
                extend: "excelHtml5",
                text: "Excel",
                className: "btn btn-success btn-sm",
                attr: {
                    style: "margin-top: 25px;padding: 0 10px; font-size: 12px; line-height: 1; height: 30px;",
                },
                exportOptions: {
                    columns: ":not(:first-child)",
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
                    columns: ":not(:first-child)",
                },
            },
        ],
    });

    search.keyup(function () {
        table.draw();
    });
}

// APPROVE REQUEST WHITELIST START

let serchId = $("#search");
let approveTable = null;

if ($("#kt_table_approve_whitelist").length) {
    approveTable = $("#kt_table_approve_whitelist").DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        scrollX: false,
        ajax: {
            url: BASE_URL + "/all/whitelist/request",
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
                data: "mobile_number",
                name: "mobile_number",
            },
            {
                data: "status",
                render: function (data) {
                    return getWhitelistStatusBadgeHtml(data);
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
                className: "btn btn-success btn-sm whitelist-export-btn",
                title: "All Whitelist Requests",
                filename: "all_whitelist_requests",
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
                className: "btn btn-danger btn-sm whitelist-export-btn",
                title: "All Whitelist Requests",
                filename: "all_whitelist_requests",
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
        approveTable.draw();
    });
}

$(document).on("click", ".approveWhitelistBtn,.suspendWhitelistBtn", function () {
    let id = $(this).attr("data-id");
    let status = $(this).attr("data-status");
    updateWhitelistStatus(status, id);
});

function updateWhitelistStatus(status, id) {
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
                    url: BASE_URL + "/all/whitelist/request/update-status/" + id,
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
