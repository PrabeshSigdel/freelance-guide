# Plugin + Theme Output Check

Use this checklist every time you start a new trekking website.

## 1. Activation Check
1. Activate plugin: `AA Trek Framework`.
2. Activate theme: `AATF Expedition Base`.
3. Open `Settings -> Permalinks`, click `Save Changes` once.

Expected:
- No PHP warnings/fatal errors.
- New admin menus visible under `Trek Framework`.

## 2. Core Structure Check
Open pages and verify these exist:
- Homepage
- Trek Listing
- Destination Page
- About
- Blog
- Contact
- Booking System

Expected:
- Homepage set as static front page.
- Blog page set as posts page.

## 3. Design System Check
Go to `Trek Framework -> Design System`.
- Change `Primary` color to red (`#d72626`), save.
- Open frontend and refresh.

Expected:
- Header/CTA/buttons/badges reflect new color globally.

## 4. Content Model Check
Create one Trek with:
- Duration, Price, Group Size
- Difficulty taxonomy
- Region taxonomy
- Season taxonomy
- Overview and itinerary rows
- Gallery images via media uploader

Expected:
- Save succeeds.
- Gallery preview works in admin.
- No validation or JS errors.

## 5. Frontend Template Check
Visit these URLs:
- `/` (front page)
- `/treks/` (archive)
- `/treks/{single-trek-slug}/` (single)

Expected:
- Global header and footer render.
- Trek cards show price/difficulty badges and CTA button.
- Single trek shows key meta and booking CTA.

## 6. Relationship Check
Create one Departure and one FAQ.
- Link both to the Trek via `Linked Trek` meta box.

Expected:
- Linked Trek column visible in admin lists.
- Correct trek title appears in that column.

## 7. Capability Check
Create user with role `Trek Manager`.

Expected:
- User can manage framework CPTs.
- User cannot access admin-only settings unrelated to role.

## 8. Final QA Pass
- Mobile check on home/archive/single pages.
- Confirm contact details in header/footer.
- Confirm colors and typography match client brand.

If all checks pass, the site is ready for client content population and delivery.
