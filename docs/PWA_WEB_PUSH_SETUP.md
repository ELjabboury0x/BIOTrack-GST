# PWA + Web Push Setup

## 1) Generate VAPID keys
Run:

```bash
php artisan push:vapid
```

Copy output values into `.env`:

- `VAPID_PUBLIC_KEY`
- `VAPID_PRIVATE_KEY`
- `VAPID_SUBJECT`

## 2) Run migration

```bash
php artisan migrate
```

This creates `push_subscriptions` table.

## 3) Clear cache

```bash
php artisan optimize:clear
```

## 4) Ensure HTTPS in production
Web Push needs secure context (`https://`) in production.

## 5) How it works
- Service worker: `public/sw.js`
- PWA manifest: `public/manifest.webmanifest`
- Frontend bootstrap: `public/js/pwa-push.js`
- Subscription API:
  - `POST /dashboard/push-subscriptions`
  - `DELETE /dashboard/push-subscriptions`
- Backend sender: `app/Services/WebPushNotificationService.php`
- Complaint trigger: `app/Listeners/SendComplaintCreatedNotification.php`

## 6) Test scenario
1. Login on phone/browser.
2. Accept notification permission.
3. Create a complaint (`ComplaintCreated` event).
4. Target users should receive push notification.

## Notes
- If permission is denied, browser will not receive push.
- Expired endpoints (HTTP 404/410) are auto-cleaned.
- Existing database notifications remain active.
