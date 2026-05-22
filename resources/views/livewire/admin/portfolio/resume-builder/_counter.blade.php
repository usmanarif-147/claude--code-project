@php
    /**
     * Live char + word counter rendered under each modal input.
     *
     * @var string|null     $value     The current value of the field.
     * @var array|null      $limit     [max_words, max_chars] — either may be null.
     */
    $value = (string) ($value ?? '');
    [$maxWords, $maxChars] = $limit ?? [null, null];

    $chars = mb_strlen($value);
    $trimmed = trim($value);
    $words = $trimmed === '' ? 0 : count(preg_split('/\s+/', $trimmed));

    $charPct = $maxChars ? ($chars / $maxChars) * 100 : 0;
    $wordPct = $maxWords ? ($words / $maxWords) * 100 : 0;
    $worstPct = max($charPct, $wordPct);

    $state = $worstPct > 100 ? 'over' : ($worstPct >= 80 ? 'warn' : 'ok');
@endphp
<div class="rb-counter rb-counter-{{ $state }}">
    @if ($maxChars !== null)
        {{ $chars }} / {{ $maxChars }} chars
    @endif
    @if ($maxChars !== null && $maxWords !== null)
        ·
    @endif
    @if ($maxWords !== null)
        {{ $words }} / {{ $maxWords }} words
    @endif
</div>
