@php
    /**
     * Phase 2C — emits inline <style> overrides for the entire resume
     * based on the user's toolbar selections. Included by both the
     * on-screen preview AND the DomPDF template AFTER _styles.blade.php,
     * so these rules win over the defaults via source order.
     *
     * Expected variables (passed from the Livewire view and the PDF view):
     *   $fontFamily, $fontSize, $bold, $textColor, $textAlign,
     *   $lineSpacing, $sectionSpacing
     */
    $fontMap = [
        'helvetica' => 'Helvetica, Arial, sans-serif',
        'dejavu' => "'DejaVu Sans', sans-serif",
        'times' => "'Times-Roman', 'Times New Roman', Times, serif",
        'courier' => "Courier, 'Courier New', monospace",
    ];
    $colorMap = [
        'black' => '#1f2937',
        'gray' => '#4b5563',
        'blue' => '#1d4ed8',
        'red' => '#991b1b',
        'green' => '#15803d',
    ];
    $lineMap = [
        'tight' => '1.2',
        'normal' => '1.45',
        'loose' => '1.7',
    ];
    $sectionMap = [
        'compact' => '6px',
        'normal' => '11px',
        'spacious' => '16px',
    ];

    $ff = $fontMap[$fontFamily ?? 'helvetica'] ?? $fontMap['helvetica'];
    $fs = $fontSize ?? '9pt';
    $tc = $colorMap[$textColor ?? 'black'] ?? $colorMap['black'];
    $ta = in_array($textAlign ?? 'left', ['left', 'center', 'right', 'justify'], true) ? $textAlign : 'left';
    $lh = $lineMap[$lineSpacing ?? 'normal'] ?? $lineMap['normal'];
    $sm = $sectionMap[$sectionSpacing ?? 'normal'] ?? $sectionMap['normal'];
    $isBold = (bool) ($bold ?? false);
@endphp
<style>
    .resume-paper {
        font-family: {{ $ff }};
        font-size: {{ $fs }};
        line-height: {{ $lh }};
        text-align: {{ $ta }};
        color: {{ $tc }};
        @if ($isBold) font-weight: 700; @endif
    }
    .resume-paper .section { margin-bottom: {{ $sm }}; }
</style>
