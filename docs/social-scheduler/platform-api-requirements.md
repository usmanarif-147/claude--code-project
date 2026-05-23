# Platform API Requirements — LinkedIn

> Short reference. Everything you need to set up developer access and post programmatically to your **LinkedIn personal profile**. Companion doc to `social-scheduler-plan.md`.

You already have a personal LinkedIn account. You **do not** need a new account — you just enable a developer console using that same account.

---

## 1. Where to request API access
- **Developer console:** https://developer.linkedin.com
- Sign in with your **existing personal LinkedIn account**.
- Click **"Create app"**.
- App name + logo (300×300 PNG min).
- **Associated LinkedIn Page:** LinkedIn forces you to associate the app with a **Company Page**, even though you'll only be posting to your personal profile. Workaround: create a minimal Company Page from your profile in 2 minutes (just needs to exist; it doesn't need followers).
- Verify the page (LinkedIn sends a verification link to a Page admin — i.e. you).

## 2. Add Products (LinkedIn calls API features "Products")
Inside your app → **Products** tab → request access to:
- **"Sign In with LinkedIn using OpenID Connect"** — gives `openid profile email` scopes. **Auto-approved instantly.**
- **"Share on LinkedIn"** — gives `w_member_social` scope (post to your own feed). **Auto-approved instantly.**

That's it. **No formal review process** for posting to your own profile.

## 3. What you cannot do without review
| Capability | Product needed | Review? |
|---|---|---|
| Post to your own profile | Share on LinkedIn | ❌ No |
| Post to OTHER users' profiles | Marketing Developer Platform | ✅ Hard to get |
| Post to a Company Page | Community Management API | ✅ Hard to get |
| Read analytics | Marketing Developer Platform | ✅ Hard to get |

This plan only depends on the **first row** → no review needed.

## 4. Token lifespans
- **Access Token:** 60 days.
- **Refresh Token:** 365 days, returned alongside access token (use it to get a fresh 60-day token without prompting you again — but the refresh token itself eventually expires too).
- After ~365 days you must do the full OAuth dance again.
- The plan handles this with email reminders 7 days before expiry.

## 5. .env values
```env
LINKEDIN_CLIENT_ID=
LINKEDIN_CLIENT_SECRET=
LINKEDIN_REDIRECT_URI=https://usmaniqbal.dev/admin/social/oauth/linkedin/callback
LINKEDIN_API_VERSION=202401
```
Find Client ID + Secret at: developer console → your app → **Auth** tab.

**Stored in DB after first connect** (table `platform_accounts`):
- Member URN (e.g. `urn:li:person:abc123`)
- Access Token (encrypted, 60-day expiry)
- Refresh Token (encrypted, 365-day expiry)

## 6. Redirect URI gotcha
- Must be added under **Auth → Authorized redirect URLs** in the app dashboard.
- Must be **HTTPS** in production. LinkedIn allows `http://localhost` for development.
- Must match the URL in the OAuth request **exactly** — including trailing slash.

---

## 7. Pre-coding checklist

Do these before any implementation begins:

- [ ] Sign in to developer.linkedin.com with personal LinkedIn account
- [ ] Create app and note Client ID + Client Secret
- [ ] Associate the app with a Company Page (create a minimal one if you don't have one)
- [ ] Verify the associated Company Page
- [ ] Add "Sign In with LinkedIn using OpenID Connect" product
- [ ] Add "Share on LinkedIn" product
- [ ] Add `https://usmaniqbal.dev/admin/social/oauth/linkedin/callback` to Authorized redirect URLs
- [ ] Confirm Let's Encrypt HTTPS active on usmaniqbal.dev
- [ ] Drop the four `.env` values from §5 into `.env` (leave blank for now — fill after console setup)

**Setup time:** ~15 minutes total. No verification queue, no Meta-style review waiting period.

Once checked off, the OAuth + publishing code in `social-scheduler-plan.md` can be implemented with zero blockers.
