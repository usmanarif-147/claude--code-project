# Project Status

Last updated: 2026-04-03 (Home module added)

## Completed Modules

### Portfolio (module group: portfolio)
Completed: 2026-03-30
Last updated: 2026-04-04 (Resume Generator → Resume Builder: click-to-edit modals, template carousel, AI template generation from screenshots, AI data import from TXT/JSON)
Features: 7 (Skills, Technologies, Experiences, Projects, Testimonials, Blog, Analytics + Resume Builder)
Side: BOTH

Routes:
  - GET /admin/skills → admin.skills.index
  - GET /admin/skills/create → admin.skills.create
  - GET /admin/skills/{skill}/edit → admin.skills.edit
  - GET /admin/technologies → admin.technologies.index
  - GET /admin/technologies/create → admin.technologies.create
  - GET /admin/technologies/{technology}/edit → admin.technologies.edit
  - GET /admin/experiences → admin.experiences.index
  - GET /admin/experiences/create → admin.experiences.create
  - GET /admin/experiences/{experience}/edit → admin.experiences.edit
  - GET /admin/projects → admin.projects.index
  - GET /admin/projects/create → admin.projects.create
  - GET /admin/projects/{project}/edit → admin.projects.edit
  - GET /admin/testimonials → admin.testimonials.index
  - GET /admin/testimonials/create → admin.testimonials.create
  - GET /admin/testimonials/{testimonial}/edit → admin.testimonials.edit
  - GET /admin/blog → admin.blog.index
  - GET /admin/blog/create → admin.blog.create
  - GET /admin/blog/{blogPost}/edit → admin.blog.edit
  - GET /admin/analytics → admin.analytics
  - GET /admin/resume → admin.resume
  - GET /admin/resume/download/{template} → admin.resume.download

Models:
  - Skill → app/Models/Skill.php
  - Technology → app/Models/Technology.php
  - Project → app/Models/Project/Project.php
  - ProjectImage → app/Models/Project/ProjectImage.php
  - Experience → app/Models/Experience/Experience.php
  - ExperienceResponsibility → app/Models/Experience/ExperienceResponsibility.php
  - Testimonial → app/Models/Testimonial.php
  - BlogPost → app/Models/Blog/BlogPost.php
  - BlogPostTag → app/Models/Blog/BlogPostTag.php

Services:
  - SkillService → app/Services/SkillService.php
  - TechnologyService → app/Services/TechnologyService.php
  - ProjectService → app/Services/ProjectService.php
  - ExperienceService → app/Services/ExperienceService.php
  - TestimonialService → app/Services/TestimonialService.php
  - BlogPostService → app/Services/BlogPostService.php
  - AnalyticsService → app/Services/AnalyticsService.php
  - ResumeService → app/Services/ResumeService.php

Livewire Components:
  - SkillIndex → app/Livewire/Admin/Portfolio/Skills/SkillIndex.php
  - SkillForm → app/Livewire/Admin/Portfolio/Skills/SkillForm.php
  - TechnologyIndex → app/Livewire/Admin/Portfolio/Technologies/TechnologyIndex.php
  - TechnologyForm → app/Livewire/Admin/Portfolio/Technologies/TechnologyForm.php
  - ExperienceIndex → app/Livewire/Admin/Portfolio/Experiences/ExperienceIndex.php
  - ExperienceForm → app/Livewire/Admin/Portfolio/Experiences/ExperienceForm.php
  - ProjectIndex → app/Livewire/Admin/Portfolio/Projects/ProjectIndex.php
  - ProjectForm → app/Livewire/Admin/Portfolio/Projects/ProjectForm.php
  - TestimonialIndex → app/Livewire/Admin/Portfolio/Testimonials/TestimonialIndex.php
  - TestimonialForm → app/Livewire/Admin/Portfolio/Testimonials/TestimonialForm.php
  - BlogPostIndex → app/Livewire/Admin/Portfolio/Blog/BlogPostIndex.php
  - BlogPostForm → app/Livewire/Admin/Portfolio/Blog/BlogPostForm.php
  - Analytics → app/Livewire/Admin/Portfolio/Analytics.php

Database Tables:
  - profiles — user profile (tagline, bio, photo, social links, timezone, language)
  - skills — skill cards with category and proficiency
  - technologies — tech stack grouped by category
  - experiences — work history and education timeline
  - experience_responsibilities — bullet points under each experience
  - projects — portfolio projects with tech stack, links, featured flag
  - project_images — gallery images for projects
  - testimonials — client reviews with rating
  - blog_posts — articles with status (draft/published)
  - blog_post_tags — tags per blog post
  - portfolio_visitors — visitor tracking records
  - resume_downloads — resume download count tracking

Sidebar: Portfolio group with Profile, Skills, Technologies, Experiences, Projects, Testimonials, Blog, Analytics, Resume

---

### Settings (module group: settings)
Completed: 2026-04-01
Features: 3 (Profile Settings, API Keys, Job Search Filters)
Side: ADMIN

Routes:
  - GET /admin/settings/profile → admin.settings.profile
  - GET /admin/settings/api-keys → admin.settings.api-keys
  - GET /admin/settings/job-search-filters → admin.settings.job-search-filters

Models:
  - ApiKey → app/Models/ApiKey.php
  - JobSearchFilter → app/Models/JobSearchFilter.php
  - Profile (shared with Portfolio) → app/Models/Profile.php

Services:
  - ProfileSettingsService → app/Services/ProfileSettingsService.php
  - ApiKeyService → app/Services/ApiKeyService.php
  - JobSearchFilterService → app/Services/JobSearchFilterService.php

Livewire Components:
  - ProfileSettingsEdit → app/Livewire/Admin/Settings/ProfileSettings/ProfileSettingsEdit.php
  - ApiKeysIndex → app/Livewire/Admin/Settings/ApiKeys/ApiKeysIndex.php
  - JobSearchFiltersEdit → app/Livewire/Admin/Settings/JobSearchFilters/JobSearchFiltersEdit.php

Database Tables:
  - profiles (extended) — added fiverr_url, youtube_url, timezone, language
  - api_keys — encrypted API keys per provider with test status
  - job_search_filters — job search preferences (titles, tech, location, salary, platforms)

Sidebar: Settings group with Profile Settings, API Keys, Job Search Filters

---

### Tasks (module group: tasks)
Completed: 2026-04-01
Last updated: 2026-04-04 (Project Board: SweetAlert confirmations, column drag-drop reorder, column edit name/color, adjacent-column task restriction, board export PDF/CSV/MD, removed cross-board movement)
Features: 13 (Task Categories, Daily Planner, Quick Capture, Calendar View, Recurring Tasks, Weekly Review, AI Prioritization, Project Board, TXT Import, PDF Download, AI Category Identification, Calendar Day Modal, Board Export)
Side: ADMIN

Routes:
  - GET /admin/tasks/categories → admin.tasks.categories.index
  - GET /admin/tasks/categories/create → admin.tasks.categories.create
  - GET /admin/tasks/categories/{taskCategory}/edit → admin.tasks.categories.edit
  - GET /admin/tasks/planner → admin.tasks.planner.index
  - GET /admin/tasks/calendar → admin.tasks.calendar.index
  - GET /admin/tasks/recurring-tasks → admin.tasks.recurring.index
  - GET /admin/tasks/recurring-tasks/create → admin.tasks.recurring.create
  - GET /admin/tasks/recurring-tasks/{recurringTask}/edit → admin.tasks.recurring.edit
  - GET /admin/tasks/weekly-review → admin.tasks.weekly-review.index
  - GET /admin/tasks/ai-prioritization → admin.tasks.ai-prioritization.index
  - GET /admin/tasks/project-board → admin.tasks.project-board.index
  - GET /admin/tasks/project-board/export/{format}/{boardId} → admin.tasks.project-board.export
  - GET /admin/tasks/pdf/download → admin.tasks.pdf.download

Models:
  - Task → app/Models/Task/Task.php
  - TaskCategory → app/Models/Task/TaskCategory.php
  - RecurringTask → app/Models/Task/RecurringTask.php
  - WeeklyReview → app/Models/Task/WeeklyReview.php
  - ProjectBoard → app/Models/Task/ProjectBoard.php
  - ProjectBoardColumn → app/Models/Task/ProjectBoardColumn.php
  - ProjectTask → app/Models/Task/ProjectTask.php
  - ProjectTaskImage → app/Models/Task/ProjectTaskImage.php

Services:
  - TaskService → app/Services/TaskService.php (updated: AI auto-categorize on create)
  - TaskCategoryService → app/Services/TaskCategoryService.php
  - RecurringTaskService → app/Services/RecurringTaskService.php
  - WeeklyReviewService → app/Services/WeeklyReviewService.php
  - CalendarService → app/Services/CalendarService.php (updated: dual-query personal + project tasks, day modal support)
  - AiTaskPrioritizationService → app/Services/AiTaskPrioritizationService.php
  - ProjectBoardService → app/Services/ProjectBoardService.php
  - ProjectTaskService → app/Services/ProjectTaskService.php (updated: removed moveToBoard)
  - ProjectBoardExportService → app/Services/ProjectBoardExportService.php
  - TaskImportService → app/Services/TaskImportService.php
  - TaskPdfService → app/Services/TaskPdfService.php
  - AiCategoryIdentificationService → app/Services/AiCategoryIdentificationService.php

Controllers:
  - TaskPdfController → app/Http/Controllers/TaskPdfController.php
  - ProjectBoardExportController → app/Http/Controllers/ProjectBoardExportController.php

Livewire Components:
  - TaskCategoryIndex → app/Livewire/Admin/Tasks/Categories/TaskCategoryIndex.php
  - TaskCategoryForm → app/Livewire/Admin/Tasks/Categories/TaskCategoryForm.php
  - DailyPlannerIndex → app/Livewire/Admin/Tasks/DailyPlanner/DailyPlannerIndex.php (updated: TXT import, PDF download, AI auto-categorize)
  - QuickCapture → app/Livewire/Admin/Tasks/QuickCapture/QuickCapture.php
  - CalendarIndex → app/Livewire/Admin/Tasks/Calendar/CalendarIndex.php (updated: day modal replaces redirect)
  - RecurringTaskIndex → app/Livewire/Admin/Tasks/RecurringTasks/RecurringTaskIndex.php
  - RecurringTaskForm → app/Livewire/Admin/Tasks/RecurringTasks/RecurringTaskForm.php
  - WeeklyReviewIndex → app/Livewire/Admin/Tasks/WeeklyReview/WeeklyReviewIndex.php
  - AiPrioritizationIndex → app/Livewire/Admin/Tasks/AiPrioritization/AiPrioritizationIndex.php
  - ProjectBoardIndex → app/Livewire/Admin/Tasks/ProjectBoard/ProjectBoardIndex.php (updated: SweetAlert, column edit/reorder, adjacent-column restriction, export dropdown, removed cross-board move)

Database Tables:
  - task_categories — task grouping with color and icon
  - tasks — daily tasks with priority, status, due date, category
  - recurring_tasks — repeating task templates with frequency
  - weekly_reviews — weekly summary snapshots
  - project_boards — kanban boards with name and description
  - project_board_columns — columns per board with color and sort order
  - project_tasks — kanban tasks with board, column, priority, tags, target date
  - project_task_images — image attachments for project tasks

Sidebar: Tasks group with Daily Planner, Categories, Recurring Tasks, Project Board, Calendar, AI Prioritization, Weekly Review

---

## Core Components (not part of any module)

- Dashboard → app/Livewire/Admin/Dashboard.php
- FileManager → app/Livewire/Admin/FileManager.php
- Login → app/Livewire/Admin/Login.php
- ProfileEdit → app/Livewire/Admin/ProfileEdit.php
- ResumeGenerator → app/Livewire/Admin/ResumeGenerator.php

## Shared Models

- User → app/Models/User.php
- Profile → app/Models/Profile.php (used by Portfolio + Settings)
- File → app/Models/File.php
- PortfolioVisitor → app/Models/PortfolioVisitor.php
- ResumeDownload → app/Models/ResumeDownload.php

### Job Search (module group: job-search)
Completed: 2026-04-01
Features: 7 (Job Feed, Saved Searches, Application Tracker, AI Job Match Scoring, AI Cover Letter Generator, Job Alerts, Application Stats)
Side: ADMIN

Routes:
  - GET /admin/job-search/feed → admin.job-search.feed.index
  - GET /admin/job-search/saved-searches → admin.job-search.saved-searches.index
  - GET /admin/job-search/saved-searches/create → admin.job-search.saved-searches.create
  - GET /admin/job-search/saved-searches/{savedSearch}/edit → admin.job-search.saved-searches.edit
  - GET /admin/job-search/applications → admin.job-search.applications.index
  - GET /admin/job-search/applications/create → admin.job-search.applications.create
  - GET /admin/job-search/applications/{jobApplication}/edit → admin.job-search.applications.edit
  - GET /admin/job-search/ai-match-scoring → admin.job-search.ai-match-scoring.index
  - GET /admin/job-search/cover-letters → admin.job-search.cover-letters.index
  - GET /admin/job-search/cover-letters/create → admin.job-search.cover-letters.create
  - GET /admin/job-search/cover-letters/{coverLetter}/edit → admin.job-search.cover-letters.edit
  - GET /admin/job-search/alerts → admin.job-search.alerts.index
  - GET /admin/job-search/alerts/settings → admin.job-search.alerts.settings
  - GET /admin/job-search/application-stats → admin.job-search.application-stats.index

Models:
  - JobListing → app/Models/JobSearch/JobListing.php
  - JobFetchLog → app/Models/JobSearch/JobFetchLog.php
  - SavedSearch → app/Models/JobSearch/SavedSearch.php
  - JobApplication → app/Models/JobSearch/JobApplication.php
  - JobMatchScore → app/Models/JobSearch/JobMatchScore.php
  - CoverLetter → app/Models/JobSearch/CoverLetter.php
  - JobAlert → app/Models/JobSearch/JobAlert.php
  - JobAlertNotification → app/Models/JobSearch/JobAlertNotification.php

Services:
  - JobFeedService → app/Services/JobFeedService.php
  - SavedSearchService → app/Services/SavedSearchService.php
  - ApplicationTrackerService → app/Services/ApplicationTrackerService.php
  - AiJobMatchService → app/Services/AiJobMatchService.php
  - AiCoverLetterService → app/Services/AiCoverLetterService.php
  - JobAlertService → app/Services/JobAlertService.php
  - ApplicationStatsService → app/Services/ApplicationStatsService.php

Livewire Components:
  - JobFeedIndex → app/Livewire/Admin/JobSearch/JobFeed/JobFeedIndex.php
  - SavedSearchIndex → app/Livewire/Admin/JobSearch/SavedSearches/SavedSearchIndex.php
  - SavedSearchForm → app/Livewire/Admin/JobSearch/SavedSearches/SavedSearchForm.php
  - ApplicationTrackerIndex → app/Livewire/Admin/JobSearch/ApplicationTracker/ApplicationTrackerIndex.php
  - ApplicationTrackerForm → app/Livewire/Admin/JobSearch/ApplicationTracker/ApplicationTrackerForm.php
  - AiMatchScoringIndex → app/Livewire/Admin/JobSearch/AiMatchScoring/AiMatchScoringIndex.php
  - AiCoverLetterIndex → app/Livewire/Admin/JobSearch/AiCoverLetter/AiCoverLetterIndex.php
  - AiCoverLetterForm → app/Livewire/Admin/JobSearch/AiCoverLetter/AiCoverLetterForm.php
  - JobAlertSettings → app/Livewire/Admin/JobSearch/JobAlerts/JobAlertSettings.php
  - JobAlertIndex → app/Livewire/Admin/JobSearch/JobAlerts/JobAlertIndex.php
  - ApplicationStatsIndex → app/Livewire/Admin/JobSearch/ApplicationStats/ApplicationStatsIndex.php

Database Tables:
  - job_listings — jobs fetched from external APIs with deduplication
  - job_fetch_logs — fetch operation tracking per platform
  - saved_searches — named search filter configurations
  - job_applications — application tracking with kanban statuses
  - job_match_scores — AI-generated match scores per job listing
  - cover_letters — AI-generated cover letters for job listings
  - job_alerts — user alert configuration (threshold, frequency, channels)
  - job_alert_notifications — individual alert notifications per job

Sidebar: Job Search group with Job Feed, Saved Searches, Application Tracker, AI Match Scoring, AI Cover Letter, Job Alerts, Application Stats

---

### Email (module group: email)
Completed: 2026-04-01
Features: 5 (Email Templates, Morning Email Digest, Auto-Categorize Emails, Smart Reply Drafts, Recruiter Alerts)
Side: ADMIN

Routes:
  - GET /admin/email/inbox → admin.email.inbox.index
  - GET /admin/email/digest → admin.email.digest.index
  - GET /admin/email/templates → admin.email.templates.index
  - GET /admin/email/templates/create → admin.email.templates.create
  - GET /admin/email/templates/{emailTemplate}/edit → admin.email.templates.edit
  - GET /admin/email/categories → admin.email.categories.index
  - GET /admin/email/categories/create → admin.email.categories.create
  - GET /admin/email/categories/{emailCategory}/edit → admin.email.categories.edit
  - GET /admin/email/categorize → admin.email.categorize.index
  - GET /admin/email/smart-reply → admin.email.smart-reply.index
  - GET /admin/email/smart-reply/create/{email?} → admin.email.smart-reply.create
  - GET /admin/email/smart-reply/{smartReplyDraft}/edit → admin.email.smart-reply.edit
  - GET /admin/email/recruiter-alerts → admin.email.recruiter-alerts.index
  - GET /admin/email/recruiter-alerts/settings → admin.email.recruiter-alerts.settings

Models:
  - Email → app/Models/Email/Email.php
  - EmailDigest → app/Models/Email/EmailDigest.php
  - EmailSyncLog → app/Models/Email/EmailSyncLog.php
  - EmailTemplate → app/Models/EmailTemplate.php
  - EmailCategory → app/Models/Email/EmailCategory.php
  - EmailCategoryCorrection → app/Models/Email/EmailCategoryCorrection.php
  - SmartReplyDraft → app/Models/Email/SmartReplyDraft.php
  - RecruiterAlert → app/Models/Email/RecruiterAlert.php
  - RecruiterAlertSetting → app/Models/Email/RecruiterAlertSetting.php

Services:
  - GmailSyncService → app/Services/GmailSyncService.php
  - EmailInboxService → app/Services/EmailInboxService.php
  - EmailDigestService → app/Services/EmailDigestService.php
  - EmailTemplateService → app/Services/EmailTemplateService.php
  - EmailCategorizationService → app/Services/EmailCategorizationService.php
  - SmartReplyDraftService → app/Services/SmartReplyDraftService.php
  - RecruiterAlertService → app/Services/RecruiterAlertService.php

Livewire Components:
  - EmailInboxIndex → app/Livewire/Admin/Email/Inbox/EmailInboxIndex.php
  - EmailDigestIndex → app/Livewire/Admin/Email/Digest/EmailDigestIndex.php
  - EmailTemplateIndex → app/Livewire/Admin/Email/Templates/EmailTemplateIndex.php
  - EmailTemplateForm → app/Livewire/Admin/Email/Templates/EmailTemplateForm.php
  - EmailCategoryIndex → app/Livewire/Admin/Email/Categories/EmailCategoryIndex.php
  - EmailCategoryForm → app/Livewire/Admin/Email/Categories/EmailCategoryForm.php
  - CategorizeDashboard → app/Livewire/Admin/Email/Categorize/CategorizeDashboard.php
  - SmartReplyIndex → app/Livewire/Admin/Email/SmartReply/SmartReplyIndex.php
  - SmartReplyForm → app/Livewire/Admin/Email/SmartReply/SmartReplyForm.php
  - RecruiterAlertIndex → app/Livewire/Admin/Email/RecruiterAlerts/RecruiterAlertIndex.php
  - RecruiterAlertSettings → app/Livewire/Admin/Email/RecruiterAlerts/RecruiterAlertSettings.php

Database Tables:
  - emails — emails fetched from Gmail with deduplication by gmail_id
  - email_digests — AI-generated daily digest summaries
  - email_sync_logs — Gmail sync operation tracking
  - email_templates — reusable email templates with categories
  - email_categories — email classification categories (Job Response, Freelance, etc.)
  - email_category_corrections — manual category correction history for AI learning
  - smart_reply_drafts — AI-generated reply drafts with tone and status
  - recruiter_alerts — detected recruiter/hiring manager email alerts
  - recruiter_alert_settings — user preferences for alert behavior

Sidebar: Email group with Inbox, Morning Digest, Templates, Categories, Auto-Categorize, Smart Reply, Recruiter Alerts, Alert Settings

---

### AI Assistant (module group: ai-assistant)
Completed: 2026-04-01
Features: 2 (Private AI Chat, Public AI Chatbot)
Side: BOTH

Routes:
  - GET /admin/ai-assistant/chat → admin.ai-assistant.chat.index
  - GET /admin/ai-assistant/chat-logs → admin.ai-assistant.chat-logs.index
  - POST /chatbot/message → chatbot.message

Models:
  - AiChatConversation → app/Models/AiChat/AiChatConversation.php
  - AiChatMessage → app/Models/AiChat/AiChatMessage.php
  - ChatbotConversation → app/Models/Chatbot/ChatbotConversation.php
  - ChatbotMessage → app/Models/Chatbot/ChatbotMessage.php

Services:
  - AiChatService → app/Services/AiChatService.php
  - ChatbotService → app/Services/ChatbotService.php
  - AiChatbotService → app/Services/AiChatbotService.php

Livewire Components:
  - PrivateChatIndex → app/Livewire/Admin/AiAssistant/PrivateChat/PrivateChatIndex.php
  - ChatLogIndex → app/Livewire/Admin/AiAssistant/ChatLogs/ChatLogIndex.php

Controllers:
  - ChatbotController → app/Http/Controllers/ChatbotController.php (public chatbot API)

Public Components:
  - chatbot-widget → resources/views/components/chatbot-widget.blade.php (Alpine.js floating chat widget)

Database Tables:
  - ai_chat_conversations — private AI chat sessions per user
  - ai_chat_messages — messages within private AI conversations
  - chatbot_conversations — public visitor chatbot sessions (identified by UUID)
  - chatbot_messages — messages within public chatbot conversations

Sidebar: AI Assistant group with Private Chat, Chat Logs

---

### YouTube (module group: youtube)
Completed: 2026-04-01
Last updated: 2026-04-03 (Subscriptions feature: channels, video feed, saved videos)
Features: 6 (Content Calendar, Video Ideas Tracker, YouTube Stats, Subscriptions, Video Feed, Saved Videos)
Side: ADMIN

Routes:
  - GET /admin/youtube/content-calendar → admin.youtube.content-calendar.index
  - GET /admin/youtube/content-calendar/create → admin.youtube.content-calendar.create
  - GET /admin/youtube/content-calendar/{contentCalendarItem}/edit → admin.youtube.content-calendar.edit
  - GET /admin/youtube/video-ideas → admin.youtube.video-ideas.index
  - GET /admin/youtube/video-ideas/create → admin.youtube.video-ideas.create
  - GET /admin/youtube/video-ideas/{videoIdea}/edit → admin.youtube.video-ideas.edit
  - GET /admin/youtube/stats → admin.youtube.stats.index
  - GET /admin/youtube/subscriptions → admin.youtube.subscriptions.index
  - GET /admin/youtube/subscriptions/feed → admin.youtube.subscriptions.feed
  - GET /admin/youtube/subscriptions/saved → admin.youtube.subscriptions.saved

Models:
  - ContentCalendarItem → app/Models/ContentCalendarItem.php
  - VideoIdea → app/Models/VideoIdea.php
  - YouTubeChannelStat → app/Models/YouTube/YouTubeChannelStat.php
  - YouTubeVideo → app/Models/YouTube/YouTubeVideo.php
  - YouTubeWeeklySnapshot → app/Models/YouTube/YouTubeWeeklySnapshot.php
  - YouTubeSubscription → app/Models/YouTube/YouTubeSubscription.php
  - YouTubeSubscriptionVideo → app/Models/YouTube/YouTubeSubscriptionVideo.php
  - YouTubeSavedVideo → app/Models/YouTube/YouTubeSavedVideo.php

Services:
  - ContentCalendarService → app/Services/ContentCalendarService.php
  - VideoIdeaService → app/Services/VideoIdeaService.php
  - YouTubeStatsService → app/Services/YouTubeStatsService.php
  - YouTubeSubscriptionService → app/Services/YouTubeSubscriptionService.php

Livewire Components:
  - ContentCalendarIndex → app/Livewire/Admin/Youtube/ContentCalendar/ContentCalendarIndex.php
  - ContentCalendarForm → app/Livewire/Admin/Youtube/ContentCalendar/ContentCalendarForm.php
  - VideoIdeaIndex → app/Livewire/Admin/Youtube/VideoIdeas/VideoIdeaIndex.php
  - VideoIdeaForm → app/Livewire/Admin/Youtube/VideoIdeas/VideoIdeaForm.php
  - YouTubeStatsIndex → app/Livewire/Admin/Youtube/Stats/YouTubeStatsIndex.php
  - SubscriptionIndex → app/Livewire/Admin/Youtube/Subscriptions/SubscriptionIndex.php
  - VideoFeedIndex → app/Livewire/Admin/Youtube/Subscriptions/VideoFeedIndex.php
  - SavedVideoIndex → app/Livewire/Admin/Youtube/Subscriptions/SavedVideoIndex.php

Database Tables:
  - content_calendar_items — scheduled content with title, type (video/blog), planned date, status
  - video_ideas — video ideas with priority, status, optional link to content calendar
  - youtube_channel_stats — cached YouTube channel statistics (subscribers, views, watch time)
  - youtube_videos — cached recent video data (views, likes, comments, duration)
  - youtube_weekly_snapshots — weekly stat snapshots for week-over-week comparison
  - youtube_subscriptions — subscribed YouTube channels with cached metadata
  - youtube_subscription_videos — cached videos from subscribed channels with is_new flag
  - youtube_saved_videos — user's saved/favorite videos with personal notes

Sidebar: YouTube group with Content Calendar, Video Ideas, YouTube Stats, Subscriptions, Video Feed, Saved Videos

---

### Personal (module group: personal)
Completed: 2026-04-02
Features: 4 (Bookmarks, Expense Tracker, Goals Tracker, Notes Scratchpad)
Side: ADMIN

Routes:
  - GET /admin/personal/bookmarks → admin.personal.bookmarks.index
  - GET /admin/personal/expense-tracker → admin.personal.expense-tracker.index
  - GET /admin/personal/expense-tracker/create → admin.personal.expense-tracker.create
  - GET /admin/personal/expense-tracker/{expense}/edit → admin.personal.expense-tracker.edit
  - GET /admin/personal/expense-tracker/categories → admin.personal.expense-tracker.categories
  - GET /admin/personal/goals-tracker → admin.personal.goals-tracker.index
  - GET /admin/personal/goals-tracker/create → admin.personal.goals-tracker.create
  - GET /admin/personal/goals-tracker/{goal}/edit → admin.personal.goals-tracker.edit
  - GET /admin/personal/notes-scratchpad → admin.personal.notes-scratchpad.index
  - GET /admin/personal/notes-scratchpad/create → admin.personal.notes-scratchpad.create
  - GET /admin/personal/notes-scratchpad/{note}/edit → admin.personal.notes-scratchpad.edit

Models:
  - Bookmark → app/Models/Bookmark/Bookmark.php
  - BookmarkCategory → app/Models/Bookmark/BookmarkCategory.php
  - Expense → app/Models/Expense/Expense.php
  - ExpenseCategory → app/Models/Expense/ExpenseCategory.php
  - MonthlyBudget → app/Models/Expense/MonthlyBudget.php
  - Goal → app/Models/Goal.php
  - Note → app/Models/Note.php

Services:
  - BookmarkService → app/Services/BookmarkService.php
  - ExpenseService → app/Services/ExpenseService.php
  - ExpenseCategoryService → app/Services/ExpenseCategoryService.php
  - GoalService → app/Services/GoalService.php
  - NoteService → app/Services/NoteService.php

Livewire Components:
  - BookmarkIndex → app/Livewire/Admin/Personal/Bookmarks/BookmarkIndex.php
  - ExpenseIndex → app/Livewire/Admin/Personal/ExpenseTracker/ExpenseIndex.php
  - ExpenseForm → app/Livewire/Admin/Personal/ExpenseTracker/ExpenseForm.php
  - ExpenseCategoryIndex → app/Livewire/Admin/Personal/ExpenseTracker/ExpenseCategoryIndex.php
  - GoalIndex → app/Livewire/Admin/Personal/GoalsTracker/GoalIndex.php
  - GoalForm → app/Livewire/Admin/Personal/GoalsTracker/GoalForm.php
  - NotesScratchpadIndex → app/Livewire/Admin/Personal/NotesScratchpad/NotesScratchpadIndex.php
  - NotesScratchpadForm → app/Livewire/Admin/Personal/NotesScratchpad/NotesScratchpadForm.php

Database Tables:
  - bookmark_categories — bookmark grouping with name, slug, is_default flag
  - bookmarks — saved links with title, URL, description, FK to category
  - expense_categories — expense grouping with name, color, icon, is_default flag
  - expenses — daily expenses with amount, category, date, note
  - monthly_budgets — monthly budget targets by year/month
  - goals — personal goals with title, category, target date, progress percentage, status
  - notes — quick notes with title, content, pin support

Sidebar: Personal group with Bookmarks, Expense Tracker, Goals Tracker, Notes

---

### Home (module group: home)
Completed: 2026-04-03
Features: 1 (Daily Briefing)
Side: ADMIN

Routes:
  - GET /admin/home/daily-briefing → admin.home.daily-briefing

Models: None (aggregation dashboard — reads from existing modules)

Services:
  - DailyBriefingService → app/Services/DailyBriefingService.php

Livewire Components:
  - DailyBriefingIndex → app/Livewire/Admin/Home/DailyBriefing/DailyBriefingIndex.php

Database Tables: None (no new tables)

Sidebar: Home group with Daily Briefing
Landing page: /admin now redirects to Daily Briefing instead of Portfolio Dashboard

---

## Core Tables

- users, cache, jobs, files, sessions
