Set-Location "D:\Pearl La\pearlhublklarav"
$msg = @"
feat: finalize PearlHub platform - all apps, migrations, docs, pitch deck

- Laravel: AdminController, ReviewController, UserController, EnsureAdminRole
- Laravel: 3 migrations (slug, reviews, is_active), factories, ListingSeeder
- Laravel: fixed route ordering, bootstrap middleware alias, is_active on User
- Flutter: pearl_core SDK fixed (setToken/clearToken/delete/patch)
- Flutter: provider app - models, service, 4 screens, main.dart
- Flutter: admin app - models, service, 4 screens, main.dart  
- Next.js: vercel.json, .env.local.example added
- Docs: comprehensive README rewrite, pitch-deck.html (9 slides)
- Config: .gitignore updated for Flutter build artifacts
"@
git commit -m $msg
