let selectedForm = $("#submitForm");

// Get the current URL of the window
const BASE_URL = window.location.origin + "/common/diamond";

let search = $("#search");

let validate = selectedForm.validate({
    rules: {
        name: "required",
    },
    onsubmit: true,
});

$(".formReset").on("click", function () {
    formReset();
});

function formReset() {
    $("#submitForm").trigger("reset");
}

$("#openDiamondCreateModal").on("click", function () {
    openModal();
});

function openModal() {
    formReset();
    loader(selectedForm, false);
    $("#kt_diamond_amount_id").val(null);
    $(".modal-title").html("Create Diamond Amount");
    $(".btnSubmit").html("Save");
    $("#showModal").modal("show");
}

selectedForm.submit(function (event) {
    event.preventDefault();

    if (!validate.valid()) return;

    loader(selectedForm, true);

    let formData = new FormData($("form#submitForm")[0]);

    // Setup CSRF token
    setCSRFToken();

    $(".error").remove();

    let diamondId = $("#kt_diamond_amount_id").val();
    let url = "";
    if (diamondId) {
        url = BASE_URL + "/update/" + diamondId;
        formData.append("_method", "PUT");
    } else {
        url = BASE_URL + "/store";
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

let table = $("#diamond-table").DataTable({
    processing: true,
    serverSide: true,
    ajax: {
        url: BASE_URL + "/list",
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
            data: "amount",
            name: "amount",
        },
        {
            data: "active",
            render: function (data, type, full, meta) {
                const checked = data === "YES" ? "checked" : "";
                const toggleStatus = data === "YES" ? "NO" : "YES";
                const checkbox =
                    '<input class="form-check-input activeStatus" data-id=' +
                    full.diamond_amount_id +
                    ' name="active" type="checkbox" value="' +
                    data +
                    '" ' +
                    checked +
                    "/>" +
                    '<input type="hidden" id="toggleData_' +
                    full.diamond_amount_id +
                    '" value="' +
                    toggleStatus +
                    '"';
                const label =
                    '<span class="form-check-label fw-bold text-muted">' +
                    data +
                    "</span>";
                return (
                    '<label class="form-check form-switch form-check-custom form-check-solid">' +
                    checkbox +
                    label +
                    "</label>"
                );
            },
        },
        {
            data: "action",
            name: "action",
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
        '<"row"<"col-sm-2"l><"col-sm-10 d-flex justify-content-end"B>>' + // Length menu & buttons
        '<"row"<"col-sm-12"tr>>' + // Table rows
        '<"row mt-2"<"col-sm-6"i><"col-sm-6 d-flex justify-content-end"p>>', // Pagination & info
    buttons: [
        {
            extend: "excelHtml5",
            text: "Excel",
            className: "btn btn-info btn-sm",
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

search.keyup(function () {
    table.draw();
});

$(document).on("click", ".editDiamondButton", function () {
    var diamond_id = $(this).attr("data-id");
    editDiamondInfo(diamond_id);
});


function editDiamondInfo(diamond_id) {
    loader(selectedForm, true);
    $(".error").remove();

    $.ajax({
        url: BASE_URL + "/edit/" + diamond_id,
        type: "GET",
        success: function (response) {
            loader(selectedForm, false);
            const data = response.diamondInfo;
            // console.log(data.item);
            if (response?.success && response?.statusCode === 200 && data) {
                $("#kt_diamond_amount_id").val(data.diamond_amount_id);
                $("#kt_amount").val(data.amount);

                $(".modal-title").text("Edit Diamond Amount");
                $(".btnSubmit").text("Update");
            }
        },
    });
}

$(document).on("change", ".activeStatus", function () {
    let id = $(this).attr("data-id");
    let active = $("#toggleData_" + id).val();
    updateActiveStatus(active, id);
});

function updateActiveStatus(active, id) {
    $.ajax({
        type: "PUT",
        url: BASE_URL + "/update-status",
        data: {
            id: id,
            active: active,
            _token: setCSRFToken(),
        },
        dataType: "json",
        success: function (response) {
            if (response?.statusCode === 200) {
                toastr.success(response?.message, "Success!");
                table.draw();
            } else {
                toastr.error(
                    response?.message || "An unexpected error occurred."
                );
            }
        },
    });
}
