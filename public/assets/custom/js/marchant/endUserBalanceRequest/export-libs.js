/* Disable AMD temporarily so JSZip/pdfMake attach to window.JSZip / window.pdfMake */
(function () {
    window.__dtDefineBackup = window.define;
    if (typeof window.define === "function") {
        window.define = undefined;
    }
    if (typeof window.require === "function" && window.require.amd) {
        window.__dtRequireAmdBackup = window.require.amd;
        window.require.amd = undefined;
    }
})();
