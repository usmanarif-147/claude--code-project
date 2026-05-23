<?php

/*
|--------------------------------------------------------------------------
| Social Templates Registry
|--------------------------------------------------------------------------
|
| Registry of available carousel templates for the Social Scheduler. Each
| template's slug must correspond to a Blade file located at
| `resources/views/social-posts/templates/{slug}.blade.php`.
|
| Add additional templates by appending new slug-keyed entries here. The
| `fields` array is reserved for future per-template customisation (e.g.
| accent colors, fonts) and is intentionally empty for Phase 2.
|
*/

return [
    'default' => [
        'label' => 'Default',
        'fields' => [],
    ],
];
