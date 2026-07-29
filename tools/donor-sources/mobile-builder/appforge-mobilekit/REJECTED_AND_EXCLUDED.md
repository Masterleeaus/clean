# Rejected and Excluded Material

## AppForge

Excluded:

- `.env`
- `dist/`
- `supabase/`
- authentication pages and contexts
- subscriptions, credits, payments and invoices
- donor admin/user/role authority
- donor database migrations and generated Supabase types
- public `.htaccess`
- marketing pages and testimonial content
- arbitrary CSS injector
- sample production outputs

## MobileKit

Excluded:

- `__MACOSX/`
- `.DS_Store`
- Sketch binary source
- sample photos, avatars and advertising images
- documentation website
- marketing assets not required by component code

The MobileKit runtime is placed under `adapt_required` because it uses global DOM mutation, `innerHTML`, localStorage flags, legacy service-worker caching and hard-coded manifest values.
