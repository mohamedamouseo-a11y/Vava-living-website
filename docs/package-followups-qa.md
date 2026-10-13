# VAVA Package Follow-up Booking — QA / Release Gates

## Implementation status
This branch adds an initial owner-authenticated appointment picker for package follow-ups, and server-side balance, payment, calendar-slot, and parent-child checks. **Not deployed. Not yet verified end-to-end.** No Google Calendar or Google Meet confirmation has been established for follow-up child bookings.

## Sandbox setup
Use a private staging copy with test users and mock notifications. Do not create customer-facing test bookings or initiate real payments.

## Test cases
1. Paid Sustainable Harmony package grants exactly 2 x 30m follow-ups, separate from original 90m appointment.
2. Pending, rejected, refunded or cancelled parent booking must not grant new entitlements (verify effective status behavior).
3. Account A cannot request, view or spend account B's follow-up credits.
4. First child booking decrements remaining balance 2 -> 1; second 1 -> 0, third blocked.
5. Multiple simultaneous booking requests with one remaining credit result in at most one child appointment.
6. Existing 30m and 90m reservations cannot be overlapped; capacity, working days, timezone and booking horizon are enforced.
7. Customer cancellation restores credit only when cancellation really succeeds; status rules need audit.
8. An existing customer booking cancellation or refund does not corrupt the parent order.
9. AR/EN, desktop/mobile, login, nonces, card filters, date form and email behavior.
10. Verify Google Calendar / Google Meet provider configuration and event creation independently; do not assume wp_mail triggers event creation.
11. Confirm no php syntax errors (`php -l`), runtime warnings, or booking-record ownership leakage.

## Required release checks
- Verify stable package identifier/configuration before production: current fallback recognizes the literal Arabic package name and an explicit post-meta override. Other packages require entitlement configuration.
- Inspect Google Calendar/Meet and email integration and adapt child booking lifecycle as needed.
- Test rollback/backups and migration of existing package purchases.
- Keep the branch unmerged and Production untouched until these gates pass.
