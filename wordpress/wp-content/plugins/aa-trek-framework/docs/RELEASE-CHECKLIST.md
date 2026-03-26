# Release Checklist (Quick)

Run this before every release.

## 1. Lint custom PHP code

From project root:

```powershell
powershell -ExecutionPolicy Bypass -File "wordpress/wp-content/plugins/aa-trek-framework/scripts/lint-custom-php.ps1"
```

Expected:
- No syntax errors in custom plugin/theme PHP files.

## 2. Activation check

In WordPress admin:
- Activate `AA Trek Framework`.
- Activate `AATF Expedition Base`.
- Open `Settings -> Permalinks` and click `Save Changes` once.

Expected:
- No white screen/fatal error.
- `Trek Framework` admin menu is visible.

## 3. CPT create/edit check

Create and update at least one item in each:
- Trek
- Destination
- Departure
- Testimonial
- FAQ

Expected:
- Save works without errors.
- Meta fields persist after reload.

## 4. Frontpage render check

Open homepage (`/`) and verify:
- Header/menu renders correctly.
- Trek cards section renders.
- Testimonials section renders with stars and text.

Expected:
- No layout break or PHP error.
