# Database Admin Tool Usage

## 🔐 Access the Tool

**URL:** `https://your-app.railway.app/dashboard/admin-db.php`

## 🚀 Setup (One-Time)

### Set Admin Password in Railway:

```bash
cd /Users/caseymeehan/Documents/base/work/other/code/Mess-o-Midi-Project/Mess-o-Midi
railway variables set ADMIN_DB_PASSWORD="your-secure-password-here"
```

**Or via Railway Dashboard:**
1. Go to your project on railway.app
2. Click on your service (mess-o-midi)
3. Go to "Variables" tab
4. Add new variable:
   - Key: `ADMIN_DB_PASSWORD`
   - Value: Your secure password

## 📥 Download Database Backup

1. Visit the admin tool URL
2. Login with your password
3. Click "Download Backup" button
4. Database file saves as `saas-backup-YYYY-MM-DD-HHMMSS.db`

## 📤 Restore Database from Backup

1. Visit the admin tool URL
2. Login with your password
3. Click "Choose File" and select your `.db` backup file
4. Click "Restore Database"
5. **Automatic backup** of existing database is created before restore

## 🔒 Security Features

- ✅ Password-protected (no database dependency)
- ✅ Session-based authentication
- ✅ Automatic backups before restore
- ✅ Database validation after upload
- ✅ Warns if using default password

## ⚠️ Important Notes

- The admin tool works independently of the main database, so even if your database is corrupted, you can still log in and restore it
- Always keep local backups of your database
- Use a strong, unique password
- The tool automatically validates that uploaded files are valid SQLite databases

## 🎯 Default Password (Development Only)

**Local development:** The default password is `CHANGE_ME_IMMEDIATELY`  
**Production:** MUST set `ADMIN_DB_PASSWORD` environment variable

---

**Questions?** This tool is part of your Mess o Midi codebase and deploys automatically with your app.

