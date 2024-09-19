function setModalType(type) {
    if (type === "production") {
        // Show fields relevant to Plan Production
        document.getElementById("modalProduction").style.display = "block";
        document.getElementById("modalOperation").style.display = "none";
        document.getElementById("modalMaster").style.display = "none";
        document.getElementById("modalProductionCsv").style.display = "block";
        document.getElementById("modalOperationCsv").style.display = "none";
        document.getElementById("modalMasterCsv").style.display = "none";
        document.getElementById("modalOptionAnother").style.display = "block";
        document.getElementById("modalOptionOperation").style.display = "none";
    } else if (type === "operation") {
        document.getElementById("modalProduction").style.display = "none";
        document.getElementById("modalOperation").style.display = "block";
        document.getElementById("modalMaster").style.display = "none";
        document.getElementById("modalProductionCsv").style.display = "none";
        document.getElementById("modalOperationCsv").style.display = "block";
        document.getElementById("modalMasterCsv").style.display = "none";
        document.getElementById("modalOptionAnother").style.display = "none";
        document.getElementById("modalOptionOperation").style.display = "block";
        // Show fields relevant to Operation Time
    } else if (type === "master") {
        document.getElementById("modalProduction").style.display = "none";
        document.getElementById("modalOperation").style.display = "none";
        document.getElementById("modalMaster").style.display = "block";
        document.getElementById("modalProductionCsv").style.display = "none";
        document.getElementById("modalOperationCsv").style.display = "none";
        document.getElementById("modalMasterCsv").style.display = "block";
        document.getElementById("modalOptionAnother").style.display = "block";
        document.getElementById("modalOptionOperation").style.display = "none";
    }
}

$(function () {
    $("#productionPlanTable").DataTable({
        paging: false,
        lengthChange: false,
        lengthMenu: [6],
        searching: false,
        ordering: true,
        info: false,
        autoWidth: true,
        responsive: true,
        buttons: [],
        dom:
            "<'row'<'col-lg-2'l><'col-lg-5'B><'col-lg-5'f>>" +
            "<'row'<'col-md-12'tr>>" +
            "<'row'<'col-md-7'p>>",
    });

    // Tambahkan kelas CSS ke dropdown panjang data
    $(".dataTables_length select").addClass("custom-select");

    // Atur margin atas dropdown agar berada di tengah secara vertikal
    $(".dataTables_length select.custom-select").css("margin-top", "6px");

    // Tambahkan kelas CSS ke tombol pencarian
    $(".dataTables_filter input").addClass("custom-search");

    // Atur margin atas tombol pencarian agar sejajar dengan dropdown
    $(".dataTables_filter input.custom-search").css("margin-top", "6px");
});

$(document).ready(function () {
    $("#operationTimeTable").DataTable({
        paging: true,
        scrollY: "250px",
        scrollCollapse: true,
        lengthChange: true,
        searching: false,
        ordering: true,
        info: true,
        autoWidth: true,
        responsive: true,
        columnDefs: [
            {
                visible: false,
                targets: [1, 2],
            }, // Sembunyikan kolom "option"
        ],
        order: [
            [1, "asc"],
            [3, "asc"],
        ], // Urutkan berdasarkan "Operation Name" dan "Start"
        rowGroup: {
            dataSrc: 2, // Kelompokkan berdasarkan "Operation Name"
            startRender: function (rows, group) {
                return $("<tr/>").append(
                    '<td colspan="8" class="text-center"><strong>' +
                        group +
                        "</strong></td>"
                );
            },
        },
        initComplete: function () {
            $("#operationTimeTable_wrapper > .row:first").remove();
        },
    });
});

// Variabel global untuk melacak header yang diklik sebelumnya
document.querySelectorAll(".sortable").forEach(function (th) {
    th.addEventListener("click", function () {
        // Hapus tanda panah di semua header
        document.querySelectorAll(".sortable i").forEach(function (icon) {
            if (icon !== th.querySelector("i")) {
                icon.classList.remove("fa-sort");
                icon.classList.remove("fa-sort-up");
                icon.classList.remove("fa-sort-down");
            }
        });

        const icon = th.querySelector("i");
        if (icon) {
            if (icon.classList.contains("fa-sort")) {
                icon.classList.remove("fa-sort");
                icon.classList.add("fa-sort-up");
            } else if (icon.classList.contains("fa-sort-up")) {
                icon.classList.remove("fa-sort-up");
                icon.classList.add("fa-sort-down");
            } else {
                icon.classList.remove("fa-sort-down");
                icon.classList.add("fa-sort-up");
            }
        } else {
            icon.classList.add("fa-sort-up");
        }
    });
});

$(document).ready(function () {
    // Initialize Select2 for all select elements with class 'bed-models-select'
    $(".bed-models-select").select2({
        width: "100%",
    });

    // Update Select2 elements dynamically after changing the number of rows
    updateRowCount();
    $("#row-count").on("change", function () {
        updateRowCount();
        // Reinitialize Select2 on new visible rows
        $(".bed-models-select").select2({
            width: "100%",
        });
    });
});

function updateRowCount() {
    var rowCount = document.getElementById("row-count").value;
    var table = document.getElementById("form-rows");

    // Hide all rows
    var allRows = table.getElementsByTagName("tr");
    for (var i = 0; i < allRows.length; i++) {
        allRows[i].style.display = "none";
        disableFormElements(allRows[i]);
    }

    // Show selected number of rows
    for (var i = 0; i < rowCount; i++) {
        var currentRow = allRows[i];
        currentRow.style.display = "table-row";
        enableFormElements(currentRow, i); // Passing index to generate unique IDs
    }
}

function disableFormElements(row) {
    var formElements = row.querySelectorAll("input, select");
    for (var i = 0; i < formElements.length; i++) {
        formElements[i].disabled = true;
    }
}

function enableFormElements(row, index) {
    var formElements = row.querySelectorAll("input, select");
    for (var i = 0; i < formElements.length; i++) {
        formElements[i].disabled = false;

        // Generate unique IDs based on the row index
        var oldId = formElements[i].id;
        formElements[i].id = oldId.split("-")[0] + "-" + (index + 1);
    }
}

$(document).ready(function () {
    // Submit form when modal delete button is clicked
    $("#deleteConfirmationModal").on("click", ".btn-danger", function () {
        // Ambil tanggal dari input hidden
        var date = $('#bulkDeleteForm input[name="date"]').val();

        // Tambahkan tanggal ke formulir penghapusan
        $("#bulkDeleteForm").append(
            '<input type="hidden" name="date" value="' + date + '">'
        );

        // Submit formulir penghapusan
        $("#bulkDeleteForm").submit();
    });
});

const specificDate = new Date("2024-04-01"); // Buat objek Date untuk 11 Juni 2024
const jmmm = new Intl.DateTimeFormat("en-TN-u-ca-islamic", {
    day: "numeric",
    month: "long",
    weekday: "long",
    year: "numeric",
}).format(specificDate);

document.addEventListener("DOMContentLoaded", function () {
    updateRowCount2(); // Initialize rows on page load
});

function updateRowCount2() {
    var rowCount = document.getElementById("row-count2").value;
    var table = document.getElementById("form-rows2");

    // Clear existing rows
    table.innerHTML = "";

    // Generate the required number of rows
    for (var i = 0; i < rowCount; i++) {
        var row = document.createElement("tr");

        row.innerHTML = `
        <td style="width: 15%"><input type="number" required class="form-control" name="number[]" value="${
            i + 1
        }" readonly /></td>
        <td><input step="300" required type="time" class="form-control" name="start[]" ></td>
        <td><input step="300" required type="time" class="form-control" name="finish[]" ></td>
        <td>
            <select required class="form-control" name="status[]">
                <option value="1">Work</option>
                <option value="2">Break</option>
            </select>
        </td>
    `;

        table.appendChild(row);
    }
}

document.getElementById("edit-btn").addEventListener("click", function () {
    document.getElementById("show-section").style.display = "none";
    document.getElementById("update-section").style.display = "block";
});

document.getElementById("cancel-btn").addEventListener("click", function () {
    document.getElementById("show-section").style.display = "block";
    document.getElementById("update-section").style.display = "none";
});
