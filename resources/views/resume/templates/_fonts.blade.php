@php
    /**
     * Registers the embedded "Carlito" font family — the free, metric-identical
     * open clone of Calibri — for BOTH the on-screen preview and the DomPDF output.
     *
     * The browser needs a web URL (asset()); DomPDF resolves url() against its
     * filesystem chroot, so it needs an absolute path (public_path()). The $pdf
     * flag picks the right base. Defaults to the browser (preview) case.
     *
     * Included by:
     *   - builder.blade.php           with ['pdf' => true]   (PDF)
     *   - resume-builder/index.blade  with ['pdf' => false]  (preview)
     */
    $base = ($pdf ?? false) ? public_path('fonts/carlito') : asset('fonts/carlito');
@endphp
<style>
    @font-face {
        font-family: 'Carlito';
        font-weight: normal;
        font-style: normal;
        src: url('{{ $base }}/Carlito-Regular.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Carlito';
        font-weight: bold;
        font-style: normal;
        src: url('{{ $base }}/Carlito-Bold.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Carlito';
        font-weight: normal;
        font-style: italic;
        src: url('{{ $base }}/Carlito-Italic.ttf') format('truetype');
    }
    @font-face {
        font-family: 'Carlito';
        font-weight: bold;
        font-style: italic;
        src: url('{{ $base }}/Carlito-BoldItalic.ttf') format('truetype');
    }
</style>
