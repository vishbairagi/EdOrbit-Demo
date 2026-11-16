<link href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&display=swap" rel="stylesheet">
<style>
    .admit-card {
        background: #ffffff;
        width: 100%;
        max-width: 210mm;
        margin: 0 auto 24px auto;
        padding: 10mm 12mm;
        box-sizing: border-box;
        color: #000;
        font-family: "Segoe UI", Roboto, Arial, sans-serif;
        font-size: 12px;
        line-height: 1.4;
    }
    .admit-card-inner { background: transparent; }

    /* Header */
    .card-school-header { text-align: center; margin-bottom: 8px; }
    .card-school-header .school-name { font-size: 12px; font-weight: 700; margin: 0 0 2px 0; }
    .card-school-header .school-contact,
    .card-school-header .school-address { font-size: 7px; margin: 0 0 1px 0; }
    .card-school-header .header-divider { border-bottom: 1px solid #d1d5db; margin-top: 8px; }

    /* Title */
    .card-title-box { text-align: center; margin: 10px 0 8px 0; }
    .card-title-box .card-title {
        font-size: 14px; font-weight: 700; text-decoration: underline;
        text-underline-offset: 2px; text-transform: uppercase;
    }

    /* Student details */
    .student-details-wrapper { display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; }
    .student-info-col { flex: 1; }
    .info-row {
        display: flex; align-items: center;
        border-bottom: 1px solid #e5e7eb;
        padding: 3px 0 4px 0; font-size: 11px;
    }
    .info-row-multi { gap: 20px; }
    .info-row-multi .roll-group { margin-left: auto; }
    .info-label { font-weight: 700; margin-right: 4px; }

    .student-photo-box {
        width: 70px; height: 70px; flex-shrink: 0;
        border: 1px solid #d1d5db; overflow: hidden;
        display: flex; align-items: center; justify-content: center;
    }
    .student-photo-img { width: 100%; height: 100%; object-fit: cover; }
    .student-photo-placeholder {
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        font-size: 9px; color: #6b7280;
    }
    .student-photo-placeholder .placeholder-icon { width: 22px; height: 22px; }

    /* Exam info */
    .exam-info-line { margin: 12px 0 5px 0; font-size: 11px; display: flex; gap: 10px; }

    /* Table: plain black grid, no theme colours */
    table.papers-schedule-table {
        width: 100% !important; border-collapse: collapse !important; border-spacing: 0 !important;
        border: 1px solid #000 !important; margin-bottom: 24px; font-size: 10px !important;
    }
    table.papers-schedule-table thead tr,
    table.papers-schedule-table thead th,
    table.papers-schedule-table tbody tr,
    table.papers-schedule-table tbody td {
        background: #ffffff !important; color: #000 !important;
        text-transform: none !important; letter-spacing: normal !important;
        border: 1px solid #000 !important; padding: 3px 6px !important;
        text-align: left !important; font-size: 10px !important; white-space: normal !important;
    }
    table.papers-schedule-table thead th { font-weight: 700 !important; }
    table.papers-schedule-table .exam-center-label { font-weight: 700 !important; }

    /* Signature */
    .card-footer-signature { margin-top: 30px; display: flex; justify-content: flex-end; }
    .signature-box { width: 140px; text-align: center; }
    .signature-display { min-height: 26px; display: flex; align-items: center; justify-content: center; }
    .signature-img { height: 28px; max-width: 130px; object-fit: contain; }
    .signature-script { font-family: 'Dancing Script', cursive; font-size: 12px; font-style: italic; }
    .signature-divider { border-bottom: 1px solid #000; margin: 2px 0 3px 0; }
    .signature-title { font-size: 8px; font-weight: 700; }

    @media print {
        @page { size: A4 portrait; margin: 8mm; }
        html, body, main, .page-content-area {
            background: #ffffff !important;
            padding: 0 !important; margin: 0 !important;
            height: auto !important; min-height: 0 !important;
            overflow: visible !important; display: block !important;
        }
        body > aside, main > header, #ui-container, .no-print { display: none !important; }
        #print-container { display: block !important; }
        .admit-card { margin: 0 auto !important; padding: 0 !important; max-width: none !important; page-break-after: always; break-after: page; }
        .admit-card:last-child { page-break-after: auto; break-after: auto; }
        body.print-single .admit-card { display: none !important; }
        body.print-single .admit-card.print-selected { display: block !important; }
    }
</style>
