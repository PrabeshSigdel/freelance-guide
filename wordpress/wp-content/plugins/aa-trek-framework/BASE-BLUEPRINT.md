# AA Trek Framework Base Blueprint

## Goal
Build once, reuse for any trekking company WordPress site with minimal repeated work.

## Current Status
Phase 1 and Phase 2 foundation are implemented:
- Core architecture (CPTs, taxonomies, core pages, data model)
- Design system (global components, color system, admin color controls)

This base is now reusable, but productizing it requires process and documentation discipline.

## What To Do Next (Productization Plan)

### 1. Freeze Your Base Standard
Define what is always included in every client delivery:
- Plugin: `aa-trek-framework`
- Required pages and content model
- Required global components and color controls
- Default roles/capabilities

Do not add client-specific logic to this base.
Use optional extension plugins or child-theme overrides for client-specific features.

### 2. Document Install + Setup SOP
For each new client website:
1. Install WordPress
2. Install and activate `aa-trek-framework`
3. Set permalinks once
4. Add company identity (name, logo, contact)
5. Set design system colors from `Trek Framework -> Design System`
6. Import treks, destinations, departures, FAQs, testimonials
7. Assign menus and homepage sections
8. QA checklist pass
9. Handover

### 3. Create Starter Content Pack
Prepare reusable demo data:
- 6 to 12 treks
- 4 destinations
- 6 testimonials
- 12 departures
- 15 FAQs

Use this for rapid first build and client previews.

### 4. Lock Reusable Templates
Keep one standard for:
- Homepage sections
- Trek listing layout
- Single trek layout
- Booking page blocks

Only swap brand and content unless project scope explicitly includes custom UI.

### 5. Define Versioning + Release Rules
Use semantic versions:
- `MAJOR`: breaking changes in data model or templates
- `MINOR`: new non-breaking features
- `PATCH`: bug fixes

Maintain a `CHANGELOG.md` for every release.

### 6. Build Migration Safety
When fields evolve:
- Keep backward compatibility where possible
- Add migration routines for old meta formats
- Document migration steps

### 7. Package It For Repeat Use
Distribute as:
- ZIP plugin package
- Setup guide
- Demo content import file
- Quick branding checklist
- Client handover checklist

## Recommended File Structure For Documentation
Create these docs in plugin root:
- `README.md` -> user-facing setup and usage
- `CHANGELOG.md` -> release history
- `docs/SOP-NEW-CLIENT.md` -> new client onboarding process
- `docs/CONTENT-MODEL.md` -> all CPT/fields/taxonomies
- `docs/QA-CHECKLIST.md` -> pre-delivery checks

## Reusable Business Workflow
For every new trekking website:
1. Install base
2. Brand colors/logo/contact
3. Import content pack
4. Tune homepage sections
5. Connect forms/booking/payment if needed
6. QA and deliver

This is the repeatable engine that removes duplicated work.

## Immediate Next Actions
1. Write `README.md` from this blueprint.
2. Add `CHANGELOG.md` and start version discipline from now.
3. Create `docs/SOP-NEW-CLIENT.md` and `docs/QA-CHECKLIST.md`.
4. Prepare demo content export for one-click import.

## Definition of Done (Base Product)
Your base is truly product-ready when a developer can launch a new trekking company site in under 2 hours using only your docs and package.
