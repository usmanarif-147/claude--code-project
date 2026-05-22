<style>
    /* ============================================================
       RESUME PAPER — shared by on-screen preview AND PDF.
       Edit this block to change the resume's appearance everywhere.
       ============================================================ */
    .resume-paper {
        background: #ffffff;
        color: #1f2937;
        font-family: Helvetica, Arial, sans-serif;
        font-size: 10.5px;
        line-height: 1.45;
        width: 210mm;
        height: 297mm;
        padding: 14mm 16mm;
        margin: 0 auto;
        box-sizing: border-box;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    /* ---- Header ---- */
    .resume-paper .resume-header {
        border-bottom: 2px solid #d1d5db;
        padding-bottom: 14px;
        margin-bottom: 16px;
        position: relative;
    }
    .resume-paper .resume-header h1 {
        font-size: 30px;
        font-weight: 700;
        color: #1d4ed8;
        letter-spacing: 0.5px;
        margin: 0 0 4px 0;
    }
    .resume-paper .resume-header .tagline {
        font-size: 11px;
        font-weight: 700;
        color: #374151;
        text-transform: uppercase;
        letter-spacing: 1.4px;
        margin-bottom: 10px;
    }
    .resume-paper .resume-header .contact {
        font-size: 10px;
        color: #4b5563;
    }
    .resume-paper .resume-header .contact span {
        margin-right: 16px;
    }
    .resume-paper .empty-hint {
        color: #9ca3af;
        font-size: 10px;
        font-style: italic;
        padding: 6px 0;
    }

    /* ---- Two-column body (table — only layout DomPDF reliably renders side-by-side) ---- */
    .resume-paper table.resume-body {
        width: 100%;
        border-collapse: collapse;
    }
    .resume-paper table.resume-body > tbody > tr > td {
        vertical-align: top;
    }
    .resume-paper td.col-left {
        width: 60%;
        padding-right: 18px;
    }
    .resume-paper td.col-right {
        width: 40%;
        padding-left: 18px;
        border-left: 1px solid #e5e7eb;
    }

    /* ---- Section heading ---- */
    .resume-paper .section {
        margin-bottom: 11px;
        position: relative;
    }
    .resume-paper .section-title-row {
        display: table;
        width: 100%;
        border-bottom: 1.5px solid #1d4ed8;
        padding-bottom: 3px;
        margin-bottom: 7px;
    }
    .resume-paper .section-title-row h2 {
        display: table-cell;
        font-size: 11px;
        font-weight: 700;
        color: #1d4ed8;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        margin: 0;
        vertical-align: middle;
    }
    .resume-paper .section-title-row .add-cell {
        display: table-cell;
        text-align: right;
        vertical-align: middle;
        width: 30px;
    }

    /* ---- Profile ---- */
    .resume-paper .profile p {
        font-size: 10.5px;
        color: #374151;
        line-height: 1.6;
        margin: 0;
    }

    /* ---- Work experience ---- */
    .resume-paper .job {
        margin-bottom: 8px;
    }
    .resume-paper .job .job-head {
        display: table;
        width: 100%;
    }
    .resume-paper .job .company {
        display: table-cell;
        font-size: 11px;
        font-weight: 700;
        color: #111827;
    }
    .resume-paper .job .dates {
        display: table-cell;
        text-align: right;
        font-size: 9.5px;
        color: #6b7280;
        font-style: italic;
        white-space: nowrap;
    }
    .resume-paper .job .role {
        font-size: 10px;
        color: #1d4ed8;
        font-weight: 600;
        margin: 1px 0 4px 0;
    }
    .resume-paper ul.bullets {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .resume-paper ul.bullets li {
        font-size: 10px;
        color: #374151;
        margin-bottom: 2px;
        padding-left: 11px;
        text-indent: -11px;
    }
    .resume-paper ul.bullets li::before {
        content: "• ";
        color: #1d4ed8;
        font-weight: 700;
    }

    /* ---- Projects ---- */
    .resume-paper .project {
        margin-bottom: 8px;
    }
    .resume-paper .project .title {
        font-size: 11px;
        font-weight: 700;
        color: #111827;
    }
    .resume-paper .project .subtitle {
        font-size: 9.5px;
        color: #6b7280;
        font-style: italic;
        margin-bottom: 3px;
    }
    .resume-paper .project .tech {
        font-size: 9.5px;
        color: #374151;
        margin-top: 3px;
    }
    .resume-paper .project .tech strong {
        font-weight: 700;
    }
    .resume-paper .project .tech .stack {
        font-style: italic;
    }

    /* ---- Skills ---- */
    .resume-paper .skill-group {
        margin-bottom: 8px;
    }
    .resume-paper .skill-group .category {
        font-size: 10px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 4px;
    }
    .resume-paper .skill-group .tags {
        line-height: 1.9;
    }
    .resume-paper .skill-group .tag {
        display: inline-block;
        background: #eff6ff;
        border: 1px solid #bfdbfe;
        color: #1d4ed8;
        font-size: 9px;
        padding: 1px 7px;
        margin: 0 3px 3px 0;
        border-radius: 3px;
    }

    /* ---- Strengths ---- */
    .resume-paper table.strengths {
        width: 100%;
        border-collapse: collapse;
    }
    .resume-paper table.strengths td {
        font-size: 10px;
        color: #374151;
        padding: 2px 4px 2px 0;
        width: 50%;
        vertical-align: top;
    }

    /* ---- Achievements ---- */
    .resume-paper .achievement-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .resume-paper .achievement-list li {
        font-size: 10px;
        color: #374151;
        margin-bottom: 4px;
        padding-left: 11px;
        text-indent: -11px;
    }
    .resume-paper .achievement-list li::before {
        content: "• ";
        color: #1d4ed8;
        font-weight: 700;
    }

    /* ---- Education ---- */
    .resume-paper .education-entry {
        margin-bottom: 8px;
    }
    .resume-paper .education-entry .degree {
        font-size: 10.5px;
        font-weight: 700;
        color: #111827;
    }
    .resume-paper .education-entry .institution {
        font-size: 10px;
        color: #374151;
    }
    .resume-paper .education-entry .dates {
        font-size: 9.5px;
        color: #1d4ed8;
        font-weight: 600;
        margin-top: 1px;
    }

    /* ============================================================
       INTERACTIVE-ONLY — `+` / `✎` buttons inside the preview.
       DomPDF never renders these (omitted via $interactive flag).
       ============================================================ */
    .rb-add-btn,
    .rb-edit-btn {
        display: inline-block;
        width: 22px;
        height: 22px;
        line-height: 20px;
        text-align: center;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-weight: 700;
        padding: 0;
        vertical-align: middle;
    }
    .rb-add-btn {
        background: #1d4ed8;
        color: #ffffff;
    }
    .rb-add-btn:hover {
        background: #1e40af;
    }
    .rb-edit-btn {
        background: #e5e7eb;
        color: #374151;
        font-size: 11px;
    }
    .rb-edit-btn:hover {
        background: #d1d5db;
    }
    .rb-add-btn-lg {
        width: 32px;
        height: 32px;
        line-height: 28px;
        font-size: 18px;
        position: absolute;
        top: 0;
        right: 0;
    }

    /* ============================================================
       PAGE CHROME — Resume Builder page title & Download button.
       ============================================================ */
    .rb-page-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 24px;
    }
    .rb-page-title {
        font-family: 'Fira Code', monospace;
        font-size: 22px;
        font-weight: 700;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        margin: 0;
    }
    .rb-page-subtitle {
        font-size: 13px;
        color: #9ca3af;
        margin-top: 4px;
    }
    .rb-page-subtitle .accent {
        color: #a78bfa;
    }
    .rb-download-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #7c3aed;
        color: #ffffff;
        font-weight: 500;
        font-size: 14px;
        padding: 10px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: background-color 150ms;
        white-space: nowrap;
    }
    .rb-download-btn:hover {
        background: #8b5cf6;
    }
    .rb-download-btn svg {
        width: 16px;
        height: 16px;
    }
    .rb-header-actions {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .rb-secondary-btn {
        display: inline-flex;
        align-items: center;
        background: transparent;
        color: #d1d5db;
        font-weight: 500;
        font-size: 13px;
        padding: 9px 14px;
        border-radius: 8px;
        border: 1px solid #25253a;
        cursor: pointer;
        transition: background-color 150ms, color 150ms, border-color 150ms;
        white-space: nowrap;
    }
    .rb-secondary-btn:hover {
        background: #1a1a24;
        color: #ffffff;
        border-color: #7c3aed;
    }

    /* ============================================================
       MODALS — overlay, panel, form controls, buttons.
       ============================================================ */
    .rb-modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        z-index: 50;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .rb-modal-panel {
        background: #111118;
        border: 1px solid #1a1a24;
        border-radius: 12px;
        width: 100%;
        max-width: 640px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }
    .rb-modal-header {
        position: sticky;
        top: 0;
        background: #111118;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-bottom: 1px solid #1a1a24;
    }
    .rb-modal-title {
        font-family: 'Fira Code', monospace;
        font-weight: 700;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        font-size: 16px;
        margin: 0;
    }
    .rb-modal-close {
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        line-height: 0;
    }
    .rb-modal-close:hover {
        color: #ffffff;
    }
    .rb-modal-close svg {
        width: 22px;
        height: 22px;
    }
    .rb-modal-body {
        padding: 20px 24px;
    }
    .rb-modal-body > * + * {
        margin-top: 16px;
    }
    .rb-modal-footer {
        position: sticky;
        bottom: 0;
        background: #111118;
        z-index: 10;
        display: flex;
        justify-content: flex-end;
        align-items: center;
        gap: 12px;
        padding: 16px 24px;
        border-top: 1px solid #1a1a24;
    }
    .rb-field-label {
        display: block;
        font-size: 13px;
        font-weight: 500;
        color: #d1d5db;
        margin-bottom: 6px;
    }
    .rb-field-label-sm {
        display: block;
        font-size: 12px;
        font-weight: 500;
        color: #9ca3af;
        margin-bottom: 4px;
    }
    .rb-input,
    .rb-textarea {
        width: 100%;
        background: #1a1a24;
        border: 1px solid #25253a;
        border-radius: 8px;
        padding: 10px 14px;
        color: #ffffff;
        font-size: 14px;
        font-family: inherit;
    }
    .rb-input::placeholder,
    .rb-textarea::placeholder {
        color: #6b7280;
    }
    .rb-input:focus,
    .rb-textarea:focus {
        outline: none;
        border-color: transparent;
        box-shadow: 0 0 0 2px #7c3aed;
    }
    .rb-input-sm {
        padding: 8px 12px;
        font-size: 13px;
    }
    .rb-checkbox-label {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        color: #d1d5db;
        cursor: pointer;
    }
    .rb-row-box {
        border: 1px solid #1a1a24;
        border-radius: 8px;
        padding: 16px;
    }
    .rb-row-box > * + * {
        margin-top: 12px;
    }
    .rb-row-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .rb-row-head h4 {
        font-size: 13px;
        font-weight: 500;
        color: #d1d5db;
        margin: 0;
    }
    .rb-grid-2 {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }
    .rb-inline-row {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 8px;
    }
    .rb-inline-row .rb-input {
        flex: 1;
    }
    .rb-btn-primary {
        background: #7c3aed;
        color: #ffffff;
        font-weight: 500;
        font-size: 14px;
        padding: 8px 20px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        transition: background-color 150ms;
    }
    .rb-btn-primary:hover {
        background: #8b5cf6;
    }
    .rb-btn-secondary {
        background: transparent;
        color: #d1d5db;
        font-size: 14px;
        padding: 8px 16px;
        border: none;
        cursor: pointer;
    }
    .rb-btn-secondary:hover {
        color: #ffffff;
    }
    .rb-btn-link-blue {
        background: transparent;
        border: none;
        color: #a78bfa;
        cursor: pointer;
        padding: 0;
    }
    .rb-btn-link-blue:hover {
        color: #7c3aed;
    }
    .rb-btn-link-blue-sm {
        font-size: 12px;
    }
    .rb-btn-link-blue-md {
        font-size: 14px;
    }
    .rb-btn-link-red {
        background: transparent;
        border: none;
        color: #f87171;
        cursor: pointer;
        padding: 0;
        font-size: 12px;
    }
    .rb-btn-link-red:hover {
        color: #fca5a5;
    }
    .rb-icon-btn-x {
        background: transparent;
        border: none;
        color: #9ca3af;
        cursor: pointer;
        padding: 4px;
        line-height: 0;
    }
    .rb-icon-btn-x:hover {
        color: #f87171;
    }
    .rb-icon-btn-x svg {
        width: 16px;
        height: 16px;
    }

    /* ============================================================
       LIMITS — counters & cap hints under modal inputs.
       ============================================================ */
    .rb-counter {
        font-size: 11px;
        color: #6b7280;
        margin-top: 4px;
        line-height: 1.3;
    }
    .rb-counter-warn {
        color: #d97706;
    }
    .rb-counter-over {
        color: #dc2626;
        font-weight: 600;
    }
    .rb-cap-hint {
        font-size: 12px;
        color: #6b7280;
        margin-left: 10px;
        font-style: italic;
    }
    .rb-modal-warning {
        background: rgba(220, 38, 38, 0.1);
        border: 1px solid rgba(220, 38, 38, 0.3);
        color: #fca5a5;
        font-size: 13px;
        padding: 8px 12px;
        border-radius: 6px;
        margin-right: auto;
    }

    /* ---- Disabled / inactive button states ---- */
    .rb-btn-primary:disabled,
    .rb-btn-link-blue:disabled,
    .rb-btn-link-red:disabled {
        opacity: 0.45;
        cursor: not-allowed;
    }
    .rb-btn-primary:disabled:hover {
        background: #7c3aed;
    }
    .rb-btn-link-blue:disabled:hover {
        color: #a78bfa;
    }
</style>
