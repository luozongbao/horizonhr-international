# TASK-041: Frontend Social OAuth Integration

**Phase:** 12 — Frontend: Integrations  
**Status:** Pending  
**Depends On:** TASK-022, TASK-005  
**Priority:** MEDIUM  

---

## Objective

Complete the social OAuth frontend integration: OAuth redirect buttons on login/register pages, OAuth callback handler page that processes the token from the URL, and social account link/unlink management in the student profile settings.

---

## Reference Documents

1. `DOCUMENTS/DESIGNS/API_DOCUMENTATION.md` — Section 2.3 (Social OAuth endpoints)
2. `DOCUMENTS/DESIGNS/visual-mockups/login.html` — Social login buttons mockup
3. `DOCUMENTS/REQUIREMENTS-EN.md` — Section IV.A.1 (Login: Google, Facebook, LinkedIn, WeChat)

---

## Social Providers

| Provider | Backend Route | Notes |
|----------|--------------|-------|
| Google | `/api/auth/google/redirect` | Standard OAuth2 |
| Facebook | `/api/auth/facebook/redirect` | Standard OAuth2 |
| LinkedIn | `/api/auth/linkedin/redirect` | Standard OAuth2 |
| WeChat | `/api/auth/wechat/redirect` | QR code scan on mobile |

---

## OAuth Flow

```
1. User clicks "Login with Google"
2. Browser navigates to /api/auth/google/redirect (full redirect, not AJAX)
3. Backend redirects to Google OAuth page
4. User authorizes → Google redirects to /api/auth/google/callback
5. Backend creates/finds user, generates token
6. Backend redirects to: /oauth/callback#token={token}&user={base64_user}
7. Frontend OAuthCallbackPage extracts token from URL hash
8. Set token in authStore, redirect to dashboard
```

---

## Deliverables

### Social Login Buttons Component
**`frontend/src/components/auth/SocialLoginButtons.vue`**

```vue
<template>
  <div class="social-login-buttons">
    <a :href="oauthUrl('google')" class="social-btn google">
      <GoogleIcon /> Continue with Google
    </a>
    <a :href="oauthUrl('facebook')" class="social-btn facebook">
      <FacebookIcon /> Continue with Facebook
    </a>
    <a :href="oauthUrl('linkedin')" class="social-btn linkedin">
      <LinkedInIcon /> Continue with LinkedIn
    </a>
    <a :href="oauthUrl('wechat')" class="social-btn wechat">
      <WeChatIcon /> Continue with WeChat
    </a>
  </div>
</template>

<script setup>
const oauthUrl = (provider) => `/api/auth/${provider}/redirect`
</script>
```

- Use `<a href="...">` (not `router-link`) — full browser redirect required
- Import social icons from SVGs or `@element-plus/icons-vue` (use generic icons if brand icons unavailable)
- Include in `LoginPage.vue` and both register pages (TASK-022 already has placeholder)

### OAuth Callback Page (Upgrade from TASK-022 placeholder)
**`frontend/src/views/auth/OAuthCallbackPage.vue`**

```js
onMounted(async () => {
  // 1. Extract from URL hash: #token=xxx&user=xxx
  const hash = window.location.hash.substring(1)
  const params = new URLSearchParams(hash)
  const token = params.get('token')
  const error = params.get('error')
  
  if (error) {
    errorMessage.value = decodeURIComponent(error)
    return
  }
  
  if (!token) {
    errorMessage.value = 'Authentication failed. No token received.'
    return
  }
  
  // 2. Set token in auth store
  authStore.setAuth(token)
  
  // 3. Fetch user profile to get role
  await authStore.fetchProfile()
  
  // 4. Redirect based on role
  const redirectMap = {
    admin: '/admin/dashboard',
    student: '/student/dashboard',
    enterprise: '/enterprise/dashboard',
  }
  router.push(redirectMap[authStore.user?.role] || '/')
})
```

Error states:
- OAuth cancelled by user: `?error=access_denied`
- Account already linked to another user: `?error=account_taken`
- Provider error: `?error=provider_error`

### Social Account Linking (Student Profile)
**Update `frontend/src/views/student/ProfilePage.vue`** — the "Social Accounts" section (placeholder from TASK-028):

Display social accounts in a list:
```
[Google icon] google@example.com  [Unlink]
[Facebook icon] Not linked        [Link Account →]
[LinkedIn icon] Not linked        [Link Account →]
[WeChat icon]   Not linked        [Link Account →]
```

**"Link Account" button:**
- Navigate to `/api/auth/{provider}/redirect?link=true` (full redirect)
- Backend creates `social_accounts` record linked to current user
- Callback: redirect back to profile page with `?linked=google`

**"Unlink" button:**
- Call `DELETE /api/auth/social/{provider}` 
- Refresh social accounts list
- Cannot unlink if it's the only login method AND no password is set

**API call for social accounts:**
```js
// GET /api/auth/social-accounts
// Returns: [{ provider: 'google', email: 'user@gmail.com' }, ...]
// DELETE /api/auth/social/{provider}
```

---

## i18n Keys to Add

```json
"oauth": {
  "continueWithGoogle": "Continue with Google",
  "continueWithFacebook": "Continue with Facebook",
  "continueWithLinkedIn": "Continue with LinkedIn",
  "continueWithWeChat": "Continue with WeChat",
  "orContinueWith": "Or continue with",
  "linking": "Linking account...",
  "linked": "Linked",
  "unlink": "Unlink",
  "linkAccount": "Link Account",
  "errors": {
    "accessDenied": "Authorization was cancelled.",
    "accountTaken": "This social account is already linked to another user.",
    "providerError": "Social login failed. Please try again.",
    "cannotUnlink": "Cannot unlink — this is your only login method."
  }
}
```

---

## Acceptance Criteria

- [ ] "Continue with Google" button navigates browser to `/api/auth/google/redirect`
- [ ] Same for Facebook, LinkedIn, WeChat
- [ ] OAuth callback page extracts token from URL hash
- [ ] Token stored in authStore and profile fetched
- [ ] Redirects to correct dashboard based on user role
- [ ] Error states displayed correctly (cancelled, account_taken, provider_error)
- [ ] Student profile shows linked/unlinked social accounts
- [ ] "Link Account" button initiates linking flow
- [ ] "Unlink" button removes social account link
- [ ] Cannot unlink last login method (error shown)
- [ ] All text via i18n

---

## Notes

- OAuth buttons are anchor tags (`<a>`), NOT Vue router links — the OAuth flow requires a real browser redirect to the backend
- WeChat on desktop: shows QR code; on mobile: opens WeChat app — handled entirely by backend/WeChat SDK
- Social icons: use inline SVGs or Unicode emoji fallback if icon package doesn't include brand icons
- `?link=true` parameter in redirect URL tells backend to link to the current authenticated user rather than create a new session — backend reads this from the callback state parameter
