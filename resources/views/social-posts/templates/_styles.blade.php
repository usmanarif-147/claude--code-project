<style>
    /* ============================================================
       Social Carousel — Plain CSS only (DomPDF safe).
       Rules: no flex, no grid, no CSS variables, no transform,
       no filter. Use display: block/inline-block/table,
       padding/margin, borders, text-align, gradients (backgrounds
       only), text-shadow.
       Canvas: 1200 x 1200 px square.
       ============================================================ */

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    html, body {
        background: #050510;
        color: #ffffff;
        font-family: 'DejaVu Sans', sans-serif;
        font-size: 14pt;
        line-height: 1.5;
    }

    /* ─── Page container (1200 x 1200 canvas) ──────────────────── */
    .page {
        width: 1200px;
        height: 1200px;
        box-sizing: border-box;
        padding: 100px 90px;
        background: #0a0a14;
        color: #ffffff;
        font-family: 'DejaVu Sans', sans-serif;
        position: relative;
        overflow: hidden;
    }

    .page p {
        margin: 0 0 22px 0;
    }

    .page p:last-child {
        margin-bottom: 0;
    }

    /* Force DomPDF to start a new page after every wrapper that
       isn't the last one. */
    .page-break {
        page-break-after: always;
    }

    /* ============================================================
       COVER PAGE — bold purple-to-pink gradient with large title
       ============================================================ */
    .page--cover {
        background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 45%, #db2777 100%);
        text-align: center;
        padding: 200px 110px 220px 110px;
    }

    .page--cover p {
        font-size: 48pt;
        font-weight: bold;
        line-height: 1.25;
        color: #ffffff;
        text-shadow: 0 2px 8px rgba(0, 0, 0, 0.28);
        margin-bottom: 30px;
    }

    .page--cover p:first-child {
        font-size: 58pt;
        line-height: 1.12;
        margin-bottom: 40px;
    }

    /* Decorative footer brand mark on the cover */
    .page--cover:after {
        content: "usmaniqbal.dev";
        display: block;
        position: absolute;
        bottom: 70px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        letter-spacing: 6px;
        color: #fde68a;
    }

    /* ============================================================
       CONTENT PAGE — dark slate with purple accent rail
       ============================================================ */
    .page--content {
        background: #0f172a;
        text-align: left;
        padding: 140px 100px 140px 130px;
        font-size: 22pt;
        line-height: 1.65;
        color: #e2e8f0;
        border-left: 14px solid #7c3aed;
    }

    .page--content p {
        font-size: 22pt;
        line-height: 1.6;
        color: #e2e8f0;
        margin-bottom: 28px;
    }

    .page--content p:first-child {
        font-size: 26pt;
        line-height: 1.4;
        color: #ffffff;
        font-weight: bold;
        margin-bottom: 40px;
        padding-bottom: 24px;
        border-bottom: 2px solid #1e293b;
    }

    .page--content:after {
        content: "usmaniqbal.dev";
        display: block;
        position: absolute;
        bottom: 60px;
        right: 80px;
        font-size: 12pt;
        color: #64748b;
        letter-spacing: 3px;
    }

    /* ============================================================
       FINAL / CTA PAGE — dark indigo with bordered card
       ============================================================ */
    .page--final {
        background: linear-gradient(160deg, #111827 0%, #1e1b4b 100%);
        text-align: center;
        padding: 180px 110px;
        border: 8px solid #7c3aed;
    }

    .page--final p {
        font-size: 38pt;
        font-weight: bold;
        line-height: 1.35;
        color: #ffffff;
        margin-bottom: 28px;
        text-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
    }

    .page--final p:first-child {
        font-size: 46pt;
        line-height: 1.25;
        margin-bottom: 40px;
    }

    .page--final:before {
        content: "THANK YOU";
        display: block;
        position: absolute;
        top: 90px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 14pt;
        font-weight: bold;
        letter-spacing: 12px;
        color: #fbbf24;
    }

    .page--final:after {
        content: "usmaniqbal.dev";
        display: block;
        position: absolute;
        bottom: 80px;
        left: 0;
        right: 0;
        text-align: center;
        font-size: 16pt;
        font-weight: bold;
        letter-spacing: 6px;
        color: #a78bfa;
    }
</style>
