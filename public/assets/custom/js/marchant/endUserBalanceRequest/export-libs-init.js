/* Restore AMD and expose export libs for DataTables */
(function () {
    if (Object.prototype.hasOwnProperty.call(window, "__dtDefineBackup")) {
        window.define = window.__dtDefineBackup;
        delete window.__dtDefineBackup;
    }
    if (Object.prototype.hasOwnProperty.call(window, "__dtRequireAmdBackup")) {
        if (window.require) {
            window.require.amd = window.__dtRequireAmdBackup;
        }
        delete window.__dtRequireAmdBackup;
    }

    if (window.JSZip && window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.Buttons) {
        jQuery.fn.dataTable.Buttons.jszip(window.JSZip);
    }

    if (window.pdfMake && window.jQuery && jQuery.fn.dataTable && jQuery.fn.dataTable.Buttons) {
        jQuery.fn.dataTable.Buttons.pdfMake(window.pdfMake);
    }
})();
