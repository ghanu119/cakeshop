# Implementation Plan – Admin & Frontend Fixes

## Phase 1: Admin – Double alerts & layout
- [ ] Remove duplicate `session('status')` from `kitchen/orders/index.blade.php` (layout already shows it).
- [ ] Ensure admin layout body uses neutral background: add explicit `bg-gray-50` and scope theme vars so admin never gets frontend theme (no pink).

## Phase 2: Admin – Blank pages
- [ ] **Profile**: Replace stub `admin/profile/edit.blade.php` with full view (extends layouts.admin, form for name/email/phone, password form).
- [ ] **Features create/edit**: Replace stubs with proper forms (title, description, icon text + icon file upload), extend layouts.admin.
- [ ] **Testimonials create/edit**: Replace stubs with proper forms, extend layouts.admin.

## Phase 3: Features – Icon file
- [ ] Add optional icon file upload: migration for `icon_path` or use existing `icon` to store path; store file in storage; FeatureService + request handle file; create/edit views file input.

## Phase 4: Kitchen – Today’s orders only
- [ ] Restrict admin Orders list to Admin only: middleware or route group `role:Admin` for `admin.orders.*`.
- [ ] Sidebar: show "Orders" only for Admin; show "Today's orders" for all (Admin + Kitchen). Kitchen users must not access admin/orders.

## Phase 5: Frontend – Header & footer
- [ ] Ensure no cart, profile, or "Order Now" in header (search theme partials / layout; remove any remaining).
- [ ] Add "Order history (by phone)" link in footer (Quick Links or new column).

## Phase 6: Order confirm – Product details & trashed
- [ ] Show product name (with link to product if not trashed); handle trashed/deleted product (show name from order or "Product no longer available", no link).

---

Execution order: 1 → 2 → 3 → 4 → 5 → 6.
