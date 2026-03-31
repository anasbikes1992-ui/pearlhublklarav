$repo = "D:\Pearl La\pearlhublklarav"
git -C $repo add -A
git -C $repo commit -m "fix: resolve ESLint errors for Vercel build"
git -C $repo push origin main
Set-Location "$repo\web-nextjs"
npx vercel --prod --yes
