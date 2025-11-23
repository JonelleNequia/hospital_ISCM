# Project Cleanup & GitHub Prep Guide

This project contains dev, backup, and integration files that should NOT be pushed to GitHub.

## Steps to Prepare for GitHub

1. **Run the cleanup script:**
   ```sh
   bash dev/prepare_for_github.sh
   ```
   This will move large `.tar.gz` and other backup files to `archive/`.

2. **Check your `.gitignore`:**
   - Make sure `dev/`, `scripts/`, `database/patches/`, `*.tar.gz`, and `public/assets/animations/` are ignored.
   - Integration folders (`integration/billing/`, `Pharmacy system/`) are also ignored by default.

3. **Review your working directory:**
   - Only commit production code, config templates, and assets needed for the app to run.
   - Do NOT commit real `.env` files or database dumps.

4. **Optional: Remove large files from git history**
   - If you already committed large files, use [BFG Repo-Cleaner](https://rtyley.github.io/bfg-repo-cleaner/) or `git filter-branch` to remove them.

5. **Push to GitHub:**
   ```sh
   git add .
   git commit -m "Cleaned up for GitHub release"
   git push origin main
   ```

## Notes
- Keep `archive/` and `dev/` out of your repo for public releases.
- If you need to restore, move files back from `archive/` as needed.

---

For any issues, check the `.gitignore` and rerun the cleanup script.
