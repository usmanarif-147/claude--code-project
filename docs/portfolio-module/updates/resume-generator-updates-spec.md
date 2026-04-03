# Resume Generator Updates Spec

## 1. UPDATE OVERVIEW

**What:** Transform the Resume Generator from a simple template-selector + preview page into a full Resume Builder with click-to-edit modals, AI-powered template generation from screenshots, AI-powered data import from files, and a template carousel.

**Type:** IMPROVE existing functionality (major feature expansion)
**Mode:** dev (destructive operations allowed, tables can be empty)

**Sub-features:**
1. Template Carousel — replace button grid with arrow-based slideshow + dot indicators
2. Data Summary Bar — expand to show 5 counts (Skills, Experiences, Projects, Technologies, Education)
3. Toolbar Buttons — Upload Template, Upload Details, Download PDF
4. Click-to-Edit Modals — 7 section edit modals that save to existing DB tables
5. AI Template Upload — screenshot → AI → new Blade template
6. AI Details Upload — TXT/JSON → AI → parsed data saved to DB

---

## 2. CURRENT STATE (BEFORE)

### ResumeGenerator.php (Livewire Component)
- Properties: `$selectedTemplate` (string), `$previewHtml` (string)
- Methods: `mount()`, `updatedSelectedTemplate()`, `render()`
- Passes to view: `templates`, `skillCount`, `experienceCount`, `projectCount`

### ResumeService.php
- `getResumeData()` — returns user, profile, skills, technologies, workExperience, education, projects
- `getAvailableTemplates()` — hardcoded array: modern, classic, compact
- `generateHtml()` — renders `resume.templates.{$template}` as HTML string
- `generatePdf()` — DomPDF from same view
- `download()` — PDF download + logs to ResumeDownload
- `validateTemplate()` — checks template key exists in hardcoded array

### resume-generator.blade.php (View)
- Static header with title
- Data summary card: 3 counts (skills, experiences, projects)
- Template selector: 3 clickable cards with `wire:click="$set('selectedTemplate', ...)"`
- Preview: iframe with `srcdoc="{{ $previewHtml }}"` (600px height)
- Download: `<a>` link to `route('admin.resume.download', $selectedTemplate)`

### Templates
- 3 built-in: `resources/views/resume/templates/modern.blade.php`, `classic.blade.php`, `compact.blade.php`
- All use variables: `$user`, `$profile`, `$skills`, `$technologies`, `$workExperience`, `$education`, `$projects`

### Routes
- `GET /admin/resume` → ResumeGenerator component
- `GET /admin/resume/download/{template}` → ResumeController::download

---

## 3. TARGET STATE (AFTER)

### ResumeGenerator.php — New Properties
```
// Template carousel
public int $currentTemplateIndex = 0;

// Modal state
public string $activeModal = '';  // '', 'personal', 'about', 'experience', 'education', 'skills', 'technologies', 'projects', 'upload-template', 'upload-details'

// Personal Info modal
public string $editName = '';
public string $editEmail = '';
public string $editTagline = '';
public string $editPhone = '';
public string $editLocation = '';
public string $editLinkedin = '';
public string $editGithub = '';

// About modal
public string $editBio = '';

// Experience modal (editing one at a time)
public ?int $editExperienceId = null;
public string $editExpRole = '';
public string $editExpCompany = '';
public string $editExpStartDate = '';
public string $editExpEndDate = '';
public bool $editExpIsCurrent = false;
public string $editExpDescription = '';
public array $editExpResponsibilities = [];

// Education modal (editing one at a time)
public ?int $editEducationId = null;
public string $editEduDegree = '';
public string $editEduField = '';
public string $editEduCompany = '';  // institution
public string $editEduStartDate = '';
public string $editEduEndDate = '';
public bool $editEduIsCurrent = false;

// Skills modal
public array $editSkills = [];  // [{id, title, category, proficiency}]
public string $newSkillTitle = '';
public string $newSkillCategory = '';

// Technologies modal
public array $editTechnologies = [];  // [{id, name, category}]
public string $newTechName = '';
public string $newTechCategory = '';

// Projects modal (editing one at a time)
public ?int $editProjectId = null;
public string $editProjTitle = '';
public string $editProjDescription = '';
public array $editProjTechStack = [];

// AI upload state
public bool $isProcessing = false;
public string $processingMessage = '';
```

### ResumeGenerator.php — New Methods
```
// Modal management
openModal(string $section): void  — sets activeModal, loads data for that section
closeModal(): void  — resets activeModal to ''
refreshPreview(): void  — regenerates previewHtml from current template

// Save methods (each calls service, then refreshPreview + closeModal)
savePersonalInfo(): void
saveAbout(): void
saveExperience(): void
deleteExperience(int $id): void
saveEducation(): void
deleteEducation(int $id): void
saveSkills(): void  — syncs entire skills list
removeSkill(int $id): void
addSkill(): void
saveTechnologies(): void
removeTechnology(int $id): void
addTechnology(): void
saveProject(): void
deleteProject(int $id): void

// Experience/Education/Project editing
editExperienceItem(int $id): void  — loads specific experience into edit fields
newExperienceItem(): void  — clears edit fields for new entry
editEducationItem(int $id): void
newEducationItem(): void
editProjectItem(int $id): void
newProjectItem(): void

// Carousel
nextTemplate(): void  — increments currentTemplateIndex (wraps around)
prevTemplate(): void  — decrements currentTemplateIndex (wraps around)
selectTemplate(int $index): void  — jumps to specific index via dot indicator

// AI features
uploadTemplateScreenshot(): void  — handles file upload, calls AI service, saves template
uploadResumeDetails(): void  — handles file upload, calls AI service, saves parsed data

// Template management
deleteCustomTemplate(string $key): void  — deletes AI-generated template file
```

### ResumeService.php — New/Modified Methods
```
// MODIFIED: getAvailableTemplates() → now discovers templates dynamically
//   - Scans resources/views/resume/templates/*.blade.php for built-in
//   - Scans storage/app/resume-templates/*.blade.php for AI-generated
//   - Returns ordered array of template keys

// NEW: getAllTemplateKeys(): array — returns ordered list of template keys

// NEW: isValidTemplate(string $key): bool — checks if template exists (built-in or custom)

// MODIFIED: generateHtml() — updated to handle both built-in and custom template paths
//   - Built-in: view("resume.templates.{$key}")
//   - Custom: Blade::render(file_get_contents(storage_path("app/resume-templates/{$key}.blade.php")), $data)

// MODIFIED: generatePdf() — same dual-path logic

// MODIFIED: validateTemplate() → uses isValidTemplate() instead of hardcoded check

// NEW: saveAiTemplate(string $bladeHtml): string
//   - Sanitizes HTML (strips @php, <?php, {!! !!} dangerous directives)
//   - Saves to storage/app/resume-templates/ai-{timestamp}.blade.php
//   - Returns the template key

// NEW: deleteCustomTemplate(string $key): void
//   - Only deletes from storage/app/resume-templates/
//   - Throws if trying to delete built-in template

// NEW: generateTemplateFromScreenshot(string $imageBase64, string $mimeType): string
//   - Calls Claude API with vision (image + prompt to generate Blade resume template)
//   - Returns raw Blade HTML from AI
//   - Uses ApiKey model for Claude key (same pattern as AiCoverLetterService)

// NEW: parseResumeDetails(string $content, string $fileType): array
//   - Calls Claude API with the file content
//   - Prompt instructs AI to return structured JSON with keys:
//     profile, skills, technologies, experiences, education, projects
//   - Returns parsed array

// NEW: importParsedData(array $data): array
//   - Takes parsed data and upserts into DB tables
//   - Returns summary of what was created/updated
//   - Profile: updates existing profile fields
//   - Skills: creates new skills (checks for duplicates by title)
//   - Technologies: creates new (checks duplicates by name+category)
//   - Experiences: creates new entries with responsibilities
//   - Projects: creates new entries

// NEW: updatePersonalInfo(array $data): void — updates User name/email + Profile fields
// NEW: updateAbout(string $bio): void — updates Profile bio
// NEW: saveExperienceItem(array $data, ?int $id = null): Experience — create or update
// NEW: saveEducationItem(array $data, ?int $id = null): Experience — create or update (type=education)
// NEW: syncSkills(array $skills): void — bulk update skills list
// NEW: syncTechnologies(array $technologies): void — bulk update technologies list
// NEW: saveProjectItem(array $data, ?int $id = null): Project — create or update
```

### View — New Structure
```
resume-generator.blade.php:
├── Breadcrumb (Dashboard > Portfolio > Resume Builder)
├── Header ("Resume Builder")
├── Data Summary Bar (5 stat cards: Skills, Technologies, Experiences, Education, Projects)
├── Toolbar (3 buttons: Upload Template, Upload Details, Download PDF)
├── Template Carousel
│   ├── Left arrow button (wire:click="prevTemplate")
│   ├── Preview iframe (srcdoc, 800px height)
│   ├── Right arrow button (wire:click="nextTemplate")
│   └── Dot indicators + "Template X of Y" counter
├── Edit Strip (vertical list of clickable section labels next to preview)
│   ├── Personal Info (wire:click="openModal('personal')")
│   ├── About (wire:click="openModal('about')")
│   ├── Experience (wire:click="openModal('experience')")
│   ├── Education (wire:click="openModal('education')")
│   ├── Skills (wire:click="openModal('skills')")
│   ├── Technologies (wire:click="openModal('technologies')")
│   └── Projects (wire:click="openModal('projects')")
├── Processing Banner (shown when isProcessing, with spinner + message)
└── Modals (one per section, shown based on activeModal value)
    ├── Personal Info Modal — form fields for name, email, tagline, phone, location, links
    ├── About Modal — textarea for bio
    ├── Experience Modal — list of existing + add/edit form
    ├── Education Modal — list of existing + add/edit form
    ├── Skills Modal — tag list with add/remove
    ├── Technologies Modal — grouped tag list with add/remove
    ├── Projects Modal — list of existing + add/edit form
    ├── Upload Template Modal — file input (image), description of feature
    └── Upload Details Modal — file input (TXT/JSON), example format shown
```

---

## 4. MIGRATION PATH (BEFORE → AFTER)

### No database migrations needed
All data uses existing tables. AI-generated templates stored as files.

### File storage
- Create directory: `storage/app/resume-templates/` (for AI-generated Blade files)
- Add to `.gitignore`: `storage/app/resume-templates/` (user-generated, not versioned)

### Service changes
- `getAvailableTemplates()` — change from hardcoded array to file-system scan
- `generateHtml()` — add custom template path support
- `generatePdf()` — add custom template path support
- `validateTemplate()` — use new `isValidTemplate()` method
- Add 10+ new methods for edit modals, AI features, data import

### Component changes
- Add ~25 new properties for modal form fields
- Add ~20 new methods for CRUD operations and AI features
- Update `render()` to pass template list as indexed array (for carousel)

### View rewrite
- Complete rewrite of `resume-generator.blade.php` — new layout with carousel, modals, toolbar
- No other views need changing

---

## 5. FILES TO MODIFY

```
MODIFY: app/Services/ResumeService.php
  - Modify: getAvailableTemplates() — dynamic template discovery
  - Modify: generateHtml() — support custom template paths
  - Modify: generatePdf() — support custom template paths
  - Modify: validateTemplate() → renamed to isValidTemplate(), uses file check
  - Add: getAllTemplateKeys()
  - Add: isValidTemplate(string $key)
  - Add: saveAiTemplate(string $bladeHtml): string
  - Add: deleteCustomTemplate(string $key): void
  - Add: generateTemplateFromScreenshot(string $imageBase64, string $mimeType): string
  - Add: parseResumeDetails(string $content, string $fileType): array
  - Add: importParsedData(array $data): array
  - Add: updatePersonalInfo(array $data): void
  - Add: updateAbout(string $bio): void
  - Add: saveExperienceItem(array $data, ?int $id): Experience
  - Add: saveEducationItem(array $data, ?int $id): Experience
  - Add: syncSkills(array $skills): void
  - Add: syncTechnologies(array $technologies): void
  - Add: saveProjectItem(array $data, ?int $id): Project

MODIFY: app/Livewire/Admin/ResumeGenerator.php
  - Add: ~25 new public properties (modal fields, carousel state, processing state)
  - Add: ~20 new public methods (openModal, closeModal, save methods, AI upload methods, carousel)
  - Modify: render() — pass indexed template list, all counts
  - Add: WithFileUploads trait (for template screenshot + resume file uploads)
  - Add: public $templateScreenshot (temp upload)
  - Add: public $resumeFile (temp upload)

MODIFY: resources/views/livewire/admin/resume-generator.blade.php
  - FULL REWRITE — new layout with breadcrumb, stat bar, toolbar, carousel, edit strip, 9 modals, processing banner

MODIFY: app/Http/Controllers/ResumeController.php
  - Modify: download() — update template validation to use new isValidTemplate()
```

---

## 6. FILES TO CREATE

```
CREATE: storage/app/resume-templates/.gitkeep  (empty directory for AI-generated templates)
```

No new PHP files, migrations, or views needed.

---

## 7. FILES TO DELETE

None.

---

## 8. CROSS-MODULE IMPACT

```
CHECK: app/Http/Controllers/PortfolioController.php
  - Uses ResumeService::download() — method signature unchanged
  - RESULT: NO IMPACT

CHECK: app/Services/AnalyticsService.php
  - Queries ResumeDownload model — model unchanged
  - RESULT: NO IMPACT

CHECK: resources/views/welcome.blade.php
  - Uses route('resume.download') — route unchanged
  - RESULT: NO IMPACT

CHECK: resources/views/components/layouts/admin.blade.php
  - Sidebar link to route('admin.resume') — route unchanged
  - RESULT: NO IMPACT

CHECK: app/Http/Controllers/ResumeController.php
  - Uses ResumeService::download() and validateTemplate()
  - validateTemplate() is being replaced by isValidTemplate()
  - RESULT: MINOR UPDATE NEEDED — update download() to use new validation
```

---

## 9. VALIDATION RULES

### Personal Info Modal
```php
'editName' => 'required|string|max:255',
'editEmail' => 'required|email|max:255',
'editTagline' => 'nullable|string|max:255',
'editPhone' => 'nullable|string|max:50',
'editLocation' => 'nullable|string|max:255',
'editLinkedin' => 'nullable|url|max:500',
'editGithub' => 'nullable|url|max:500',
```

### About Modal
```php
'editBio' => 'nullable|string|max:5000',
```

### Experience Modal
```php
'editExpRole' => 'required|string|max:255',
'editExpCompany' => 'required|string|max:255',
'editExpStartDate' => 'required|date',
'editExpEndDate' => 'nullable|date|after_or_equal:editExpStartDate',
'editExpIsCurrent' => 'boolean',
'editExpDescription' => 'nullable|string|max:5000',
'editExpResponsibilities.*' => 'nullable|string|max:500',
```

### Education Modal
```php
'editEduDegree' => 'required|string|max:255',
'editEduField' => 'nullable|string|max:255',
'editEduCompany' => 'required|string|max:255',
'editEduStartDate' => 'required|date',
'editEduEndDate' => 'nullable|date|after_or_equal:editEduStartDate',
'editEduIsCurrent' => 'boolean',
```

### Skills Modal
```php
'newSkillTitle' => 'required|string|max:255',
'newSkillCategory' => 'nullable|string|max:100',
```

### Technologies Modal
```php
'newTechName' => 'required|string|max:255',
'newTechCategory' => 'required|string|max:100',
```

### Projects Modal
```php
'editProjTitle' => 'required|string|max:255',
'editProjDescription' => 'nullable|string|max:5000',
```

### Upload Template
```php
'templateScreenshot' => 'required|image|mimes:png,jpg,jpeg,webp|max:5120',
```

### Upload Details
```php
'resumeFile' => 'required|file|mimes:txt,json|max:2048',
```

---

## 10. EDGE CASES & RISKS

1. **AI template injection** — AI-generated Blade templates could contain malicious PHP directives. MITIGATION: sanitize by stripping `@php`, `<?php`, `{!! !!}`, `@include`, `@extends`, `@require`. Only allow `{{ }}` (escaped output), basic HTML, and inline CSS.

2. **AI response format** — AI might return non-valid HTML/Blade. MITIGATION: wrap in try/catch, show error flash message. Allow user to retry.

3. **AI data parsing errors** — uploaded file might have unexpected format. MITIGATION: validate AI response JSON structure before importing. Show user what was parsed before saving (optional — or just save + show flash summary).

4. **Large template files** — AI might generate very long templates. MITIGATION: limit AI response to 8000 tokens.

5. **Custom template rendering errors** — malformed Blade in custom template crashes preview. MITIGATION: wrap `Blade::render()` in try/catch, show error message in preview area.

6. **File upload size** — 5MB screenshots, 2MB data files. Already enforced by validation.

7. **Carousel with 0 templates** — shouldn't happen (3 built-in always exist), but guard against empty template list.

8. **Concurrent edits** — single-user app, not a concern.

9. **ResumeController validation** — must update to use new `isValidTemplate()` method or it will break for custom templates.

---

## 11. IMPLEMENTATION ORDER

### Phase 1: Service Layer Foundation
1. Modify `ResumeService::getAvailableTemplates()` → dynamic discovery
2. Add `getAllTemplateKeys()`, `isValidTemplate()`
3. Modify `generateHtml()` and `generatePdf()` for custom template paths
4. Update `ResumeController::download()` to work with new validation
5. Create `storage/app/resume-templates/.gitkeep`
6. Add CRUD methods: `updatePersonalInfo()`, `updateAbout()`, `saveExperienceItem()`, `saveEducationItem()`, `syncSkills()`, `syncTechnologies()`, `saveProjectItem()`

### Phase 2: AI Service Methods
7. Add `generateTemplateFromScreenshot()` — Claude API with vision
8. Add `saveAiTemplate()` — sanitize + save Blade file
9. Add `deleteCustomTemplate()`
10. Add `parseResumeDetails()` — Claude API for file parsing
11. Add `importParsedData()` — upsert parsed data into DB

### Phase 3: Livewire Component
12. Add all new properties (modal fields, carousel state, upload properties)
13. Add `WithFileUploads` trait
14. Add modal management methods (`openModal`, `closeModal`, `refreshPreview`)
15. Add carousel methods (`nextTemplate`, `prevTemplate`, `selectTemplate`)
16. Add all save methods (one per modal section)
17. Add AI upload methods (`uploadTemplateScreenshot`, `uploadResumeDetails`)
18. Update `render()` to pass all needed data

### Phase 4: View Rewrite
19. Rewrite `resume-generator.blade.php`:
    - Breadcrumb
    - Header
    - Data summary bar (5 stat cards)
    - Toolbar (3 buttons)
    - Template carousel with arrows + dots
    - Edit strip sidebar
    - Processing banner
    - All 9 modals

### Phase 5: Lint + Verify
20. Run Pint
21. Verify routes
22. Test template carousel with built-in templates
23. Test modal open/close/save
24. Test AI upload (requires API key)
