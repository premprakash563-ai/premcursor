# Campora — local Git sync (Windows)

Project GitHub pe hai. Zip mat download karo — **git pull** se update lo.

## Repo
- URL: https://github.com/premprakash563-ai/premcursor
- Branch: `cursor/school-coaching-saas-b90f`
- App folder: `edusphere`

## Option A — Fresh clone (best)

PowerShell / CMD:

```bat
cd C:\
git clone -b cursor/school-coaching-saas-b90f https://github.com/premprakash563-ai/premcursor.git
cd premcursor\edusphere
npm install
npm run dev
```

Browser: http://localhost:3000

## Option B — Existing `C:\edusphere` folder connect karo

Agar pehle zip se extract kiya tha:

```bat
cd C:\edusphere

git init
git remote remove origin
git remote add origin https://github.com/premprakash563-ai/premcursor.git
git fetch origin

REM sparse: only pull edusphere content from branch root layout
git checkout -f origin/cursor/school-coaching-saas-b90f -- edusphere
```

Simple way (recommended): parent folder mein clone karo, purana folder delete/rename:

```bat
cd C:\
ren edusphere edusphere-old
git clone -b cursor/school-coaching-saas-b90f https://github.com/premprakash563-ai/premcursor.git
cd premcursor\edusphere
npm install
npm run dev
```

## Har update ke baad (sirf yeh)

```bat
cd C:\premcursor
git pull origin cursor/school-coaching-saas-b90f
cd edusphere
npm install
npm run dev
```

Hard refresh browser: `Ctrl + Shift + R`
