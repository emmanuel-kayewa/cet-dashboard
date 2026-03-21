# GitHub Actions Self-Hosted Runner Setup Guide

## Overview

This document provides comprehensive instructions for setting up GitHub Actions self-hosted runners on Oracle Linux 8 servers for the ZESCO Executive Dashboard application. This approach is used because the servers are on a private network and cannot be accessed directly from GitHub's hosted runners.

## Architecture

- **Test Server**: Deploys automatically when code is pushed to `develop` branch
- **Production Server**: Deploys automatically when code is pushed to `main` branch
- **Runner Type**: Self-hosted runners installed directly on each server
- **Labels**: `test` for test server, `production` for production server

## Why Self-Hosted Runners?

Self-hosted runners are necessary when:
- Servers are on a private network (not publicly accessible)
- You want to avoid SSH/firewall configuration complexity
- You need direct access to server resources
- GitHub's hosted runners can't reach your infrastructure

## Prerequisites

### Software Requirements (on each server)

Ensure the following are installed:
- Git
- PHP 8.1 or higher
- Composer
- Node.js and NPM (v18+)
- MySQL/MariaDB client
- Web server (Apache/Nginx)

### Network Requirements

- Server must have outbound internet access to reach GitHub
- No inbound access required from GitHub

## Step-by-Step Setup

### Part 1: Prepare the Server

#### 1. Create Dedicated User

**IMPORTANT**: Never run the runner as root for security reasons.

```bash
# As root, create a dedicated user
useradd -m -s /bin/bash github-runner

# Set a password (optional but recommended)
passwd github-runner

# Optional: Add to wheel group if sudo access needed for specific tasks
usermod -aG wheel github-runner
```

#### 2. Identify PHP-FPM User (IMPORTANT!)

**CRITICAL**: Laravel needs write access to `storage/` and `bootstrap/cache/`. The user that needs this access is **PHP-FPM**, not necessarily nginx/apache.

```bash
# As root, check which user PHP-FPM runs as
ps aux | grep php-fpm | grep -v grep | head -2

# Look for the "pool www" processes, NOT the master process
# Common users: apache, nginx, www-data, php-fpm
```

**Example output:**
```
root      12345  ... php-fpm: master process
apache    12346  ... php-fpm: pool www          <- THIS is the user you need!
apache    12347  ... php-fpm: pool www
```

In this example, PHP-FPM runs as `apache`, so files should be owned by `github-runner:apache`.

#### 3. Set Application Directory Permissions

The `github-runner` user needs read/write access to your application directory.

**IMPORTANT**: Use the PHP-FPM user identified in the previous step as the group owner!

**Option A: Runner owns the application** (Simplest, but may cause PHP-FPM issues)
```bash
# As root
chown -R github-runner:github-runner /var/www/zesco-dashboard-test
chmod -R 755 /var/www/zesco-dashboard-test

# Storage and cache need more permissions
chmod -R 775 /var/www/zesco-dashboard-test/storage
chmod -R 775 /var/www/zesco-dashboard-test/bootstrap/cache
```

**Option B: Shared ownership with PHP-FPM user** (RECOMMENDED)
```bash
# As root - Replace 'apache' with your PHP-FPM user from step 2
chown -R github-runner:apache /var/www/zesco-dashboard-test
chmod -R 775 /var/www/zesco-dashboard-test

# Web server/PHP-FPM writable directories
chmod -R 775 /var/www/zesco-dashboard-test/storage
chmod -R 775 /var/www/zesco-dashboard-test/bootstrap/cache
chmod -R 775 /var/www/zesco-dashboard-test/public

# Make new files inherit group ownership (critical for shared access)
chmod g+s /var/www/zesco-dashboard-test/storage
chmod g+s /var/www/zesco-dashboard-test/bootstrap/cache
find /var/www/zesco-dashboard-test/storage -type d -exec chmod g+s {} +
find /var/www/zesco-dashboard-test/bootstrap/cache -type d -exec chmod g+s {} +

# Add github-runner to PHP-FPM user's group
usermod -aG apache github-runner

# Fix SELinux context for PHP-FPM write access
chcon -R -t httpd_sys_rw_content_t /var/www/zesco-dashboard-test/storage
chcon -R -t httpd_sys_rw_content_t /var/www/zesco-dashboard-test/bootstrap/cache
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/zesco-dashboard-test/storage(/.*)?"  
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/zesco-dashboard-test/bootstrap/cache(/.*)?"  
```

#### 4. Ensure Application is a Git Repository

```bash
# Switch to github-runner user
su - github-runner

# Navigate to application directory
cd /var/www/zesco-dashboard-test

# Verify git repository
git status

# Checkout the correct branch
# For test server: develop
# For production server: main
git checkout develop
```

#### 5. Set Up GitHub Access (SSH)

The runner needs to pull from GitHub, so configure SSH access:

```bash
# Still as github-runner user
ssh-keygen -t ed25519 -C "github-runner@test-server" -N ""

# Display the public key
cat ~/.ssh/id_ed25519.pub
```

**Add this public key to GitHub:**
1. Go to your repository on GitHub
2. Navigate to **Settings** → **Deploy keys** → **Add deploy key**
3. Paste the public key
4. Give it a title like "Test Server Runner"
5. Check "Allow write access" if you need the runner to push (usually not needed)
6. Click **Add key**

Test the connection:
```bash
ssh -T git@github.com
# Should see: "Hi username! You've successfully authenticated..."

# Test pull
git pull origin develop
```

### Part 2: Install GitHub Actions Runner

#### 1. Get Runner Download Link and Token

1. Go to your GitHub repository
2. Navigate to **Settings** → **Actions** → **Runners**
3. Click **New self-hosted runner**
4. Select **Linux** as the OS
5. Keep this page open - you'll need the token

#### 2. Download and Extract Runner (as github-runner user)

```bash
# Switch to github-runner user if not already
su - github-runner

# Create directory for the runner
mkdir -p ~/actions-runner && cd ~/actions-runner

# Download the latest runner package
# Check GitHub's page for the latest version URL
curl -o actions-runner-linux-x64-2.313.0.tar.gz -L https://github.com/actions/runner/releases/download/v2.313.0/actions-runner-linux-x64-2.313.0.tar.gz

# Optional: Validate the hash (shown on GitHub's setup page)
# echo "HASH_FROM_GITHUB *actions-runner-linux-x64-2.313.0.tar.gz" | shasum -a 256 -c

# Extract the installer
tar xzf ./actions-runner-linux-x64-2.313.0.tar.gz
```

#### 3. Configure the Runner (as github-runner user)

**CRITICAL**: This step MUST be done as the `github-runner` user, NOT as root.

```bash
# Still as github-runner user
./config.sh --url https://github.com/YOUR_USERNAME/YOUR_REPO --token YOUR_TOKEN_FROM_GITHUB
```

**Configuration Prompts:**

1. **Enter the name of the runner group to add this runner to: [press Enter for Default]**
   - Press **Enter** (use Default group)

2. **Enter the name of runner:**
   - For test server: `test-server`
   - For production server: `production-server`

3. **Enter any additional labels (ex. label-1,label-2): [press Enter to skip]**
   - For test server: `test`
   - For production server: `production`

4. **Enter name of work folder: [press Enter for _work]**
   - Press **Enter** (use default `_work`)

You should see:
```
✓ Runner successfully added
✓ Runner connection is good
```

#### 4. Install as Service (as root)

**Now** switch back to root to install the systemd service:

```bash
# Exit back to root
exit

# As root, navigate to the runner directory
cd /home/github-runner/actions-runner

# Install the service (CRITICAL: specify the username)
./svc.sh install github-runner

# You should see:
# Run as user: github-runner
# Run as uid: 54323 (or similar, NOT 0)
```

### Part 3: Fix SELinux (Oracle Linux 8)

Oracle Linux 8 has SELinux enabled by default, which will block the runner from executing.

#### Symptoms of SELinux Issue

If you try to start the service without fixing SELinux:
```bash
./svc.sh start
# Shows: Active: failed (Result: exit-code) status=203/EXEC
```

#### Solution: Configure SELinux Properly

```bash
# As root, in the runner directory
cd /home/github-runner/actions-runner

# Option 1: Temporarily disable SELinux to test (NOT for production)
setenforce 0
./svc.sh start
# If it works, you know it's SELinux

# Set back to enforcing
setenforce 1
./svc.sh stop

# Option 2: Fix SELinux properly (RECOMMENDED)

# Set correct SELinux contexts
chcon -R -t bin_t /home/github-runner/actions-runner
chcon -t bin_t /home/github-runner/actions-runner/*.sh
chcon -t bin_t /home/github-runner/actions-runner/bin/*

# Make the changes permanent (survive relabel)
semanage fcontext -a -t bin_t "/home/github-runner/actions-runner(/.*)?"
restorecon -R -v /home/github-runner/actions-runner

# Allow systemd to execute scripts from home directories
setsebool -P httpd_enable_homedirs 1

# If still having issues, create custom policy
ausearch -m avc -ts recent | audit2allow -M github-runner
semodule -i github-runner.pp
```

### Part 4: Start and Verify the Service

```bash
# As root
cd /home/github-runner/actions-runner

# Start the service
./svc.sh start

# Check status
./svc.sh status
```

**Expected output:**
```
● actions.runner.YOUR-REPO.test-server.service - GitHub Actions Runner (...)
   Loaded: loaded
   Active: active (running)
   ...
```

**Verify in GitHub:**
1. Go to your repository
2. Navigate to **Settings** → **Actions** → **Runners**
3. You should see your runner listed with status **Idle** (green dot)

### Part 5: Configure GitHub Secrets

Since the runner is on the server, you only need the application path as a secret.

**For Test Server:**
1. Go to **Settings** → **Secrets and variables** → **Actions**
2. Click **New repository secret**
3. Add:
   - Name: `TEST_APP_PATH`
   - Value: `/var/www/zesco-dashboard-test`

**For Production Server:**
1. Add:
   - Name: `PROD_APP_PATH`
   - Value: `/var/www/zesco-dashboard`

**Remove Old Secrets** (if they exist from previous SSH-based setup):
- `TEST_HOST`, `TEST_USERNAME`, `TEST_SSH_KEY`, `TEST_PORT`
- `PROD_HOST`, `PROD_USERNAME`, `PROD_SSH_KEY`, `PROD_PORT`

### Part 6: Test the Deployment

#### From Your Local Machine:

```bash
# Ensure workflows are pushed to GitHub
git add .github/
git commit -m "Add GitHub Actions workflows for self-hosted runners"
git push origin develop

# Make a test change
echo "# Test deployment" >> README.md
git add README.md
git commit -m "Test: Trigger deployment"
git push origin develop
```

#### Monitor in GitHub:

1. Go to **Actions** tab in your repository
2. You should see the workflow "Deploy to Test" running
3. Click on it to view live logs
4. The runner should pick up the job and execute all deployment steps

#### Check on Server:

```bash
# As github-runner or root
cd /var/www/zesco-dashboard-test
git log -1
# Should show your latest commit

# Check that dependencies were installed
ls -la vendor/
ls -la node_modules/
ls -la public/build/
```

## Common Issues and Solutions

### Issue 1: "Must not run with sudo"

**Problem:**
```
./config.sh --url ... --token ...
Must not run with sudo
```

**Solution:** The configuration step must be run as the `github-runner` user, not as root.
```bash
su - github-runner
cd ~/actions-runner
./config.sh --url ... --token ...
```

### Issue 2: Service Fails with "exit-code 203/EXEC"

**Problem:**
```
Active: failed (Result: exit-code) status=203/EXEC
```

**Cause:** SELinux is blocking execution.

**Solution:** See "Part 3: Fix SELinux" above. Quick fix:
```bash
# As root
setenforce 0  # Temporarily disable
./svc.sh start
# If it works, fix SELinux properly (see above)
setenforce 1
```

### Issue 3: Service Installs as Root (uid: 0)

**Problem:**
```
Run as user: 
Run as uid: 0
```

**Cause:** The username wasn't specified during service installation.

**Solution:**
```bash
# As root
./svc.sh uninstall
./svc.sh install github-runner  # Specify the username
```

### Issue 4: Permission Denied During Deployment

**Problem:** Deployment fails with permission errors when trying to run composer, npm, or git commands.

**Solution:** Ensure the `github-runner` user owns the application directory:
```bash
# As root
chown -R github-runner:github-runner /var/www/zesco-dashboard-test
```

**Note:** If you see permission errors on the **website** (not during deployment), see **Issue 9** below - that's a different problem related to PHP-FPM permissions.

### Issue 5: Git Pull Fails - Authentication Error

**Problem:** `git pull` fails with authentication error.

**Solution:** Set up SSH keys for the `github-runner` user and add to GitHub as deploy key (see Part 1, Step 4).

### Issue 6: Composer/NPM Not Found

**Problem:** Deployment fails with "composer: command not found" or "npm: command not found".

**Solution:** Install missing tools or ensure they're in PATH for the github-runner user:
```bash
# As github-runner user
which composer
which npm
which php

# If not found, install:
# Node.js and NPM
curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash -
sudo yum install -y nodejs

# Composer
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
```

### Issue 7: Runner Shows as Offline

**Problem:** Runner shows as "Offline" in GitHub.

**Solutions:**
```bash
# Check service status
sudo systemctl status actions.runner.*

# Restart the service
cd /home/github-runner/actions-runner
sudo ./svc.sh restart

# Check logs
sudo journalctl -u actions.runner.* -f

# Verify internet connectivity
ping github.com
```

### Issue 8: Deployment Runs But Code Doesn't Update

**Problem:** Workflow completes successfully but code on server is unchanged.

**Solution:** Check that the working directory is correct:
```bash
# In the workflow file, verify:
working-directory: ${{ secrets.TEST_APP_PATH }}

# Check the secret value in GitHub matches the actual path
# Check git status on server
cd /var/www/zesco-dashboard-test
git status
git log -1
```

### Issue 9: tempnam() Warning - "file created in the system's temporary directory"

**Problem:** Website shows error:
```
tempnam(): file created in the system's temporary directory
ErrorException in Filesystem.php
```

Or permission denied errors when trying to write to `storage/logs/laravel.log` or `storage/framework/views/`.

**Cause:** PHP-FPM runs as a different user (often `apache`) than nginx, and doesn't have write permissions to Laravel's storage directories.

**How to Identify:**
```bash
# Check which user PHP-FPM runs as
ps aux | grep php-fpm | grep -v grep | head -2
# Look at the "pool www" processes (NOT the master process)

# Check current ownership
ls -la /var/www/cet-dashboard/storage
ls -la /var/www/cet-dashboard/bootstrap/cache
```

**Solution:**
```bash
# As root
cd /var/www/cet-dashboard

# 1. Fix ownership - use PHP-FPM user as group (usually 'apache')
chown -R github-runner:apache storage bootstrap/cache

# 2. Set proper permissions
chmod -R 775 storage bootstrap/cache
chmod g+s storage bootstrap/cache

# 3. Fix SELinux context for PHP-FPM write access
chcon -R -t httpd_sys_rw_content_t storage
chcon -R -t httpd_sys_rw_content_t bootstrap/cache
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/cet-dashboard/storage(/.*)?"  
semanage fcontext -a -t httpd_sys_rw_content_t "/var/www/cet-dashboard/bootstrap/cache(/.*)?"  

# 4. Add github-runner to PHP-FPM user's group
usermod -aG apache github-runner

# 5. Clear caches
sudo -u github-runner php artisan cache:clear
sudo -u github-runner php artisan config:clear
sudo -u github-runner php artisan view:clear

# 6. Restart PHP-FPM
systemctl restart php-fpm
```

**Prevention:** Follow Step 2 & 3 in "Part 1: Prepare the Server" to identify the PHP-FPM user and set correct permissions from the start.

### Issue 10: Deployment Fails - "Failed to clear cache" During GitHub Actions

**Problem:** Deployment workflow fails during cache clearing step:
```
🧹 Clearing caches...
   INFO  Configuration cache cleared successfully.  
   ERROR  Failed to clear cache. Make sure you have the appropriate permissions.  
Error: Process completed with exit code 1.
```

**Cause:** PHP-FPM (running as `apache`) creates cache files owned by `apache:apache`. The `github-runner` user can't delete these files even with group permissions because they're owned by a different user.

**Solution 1: Fix Permissions on Server (Recommended)**
```bash
# As root
cd /var/www/cet-dashboard

# 1. Ensure proper ownership
chown -R github-runner:apache storage/framework/cache
chown -R github-runner:apache storage/framework/views
chown -R github-runner:apache storage/framework/sessions

# 2. Set group sticky bit so new files inherit the group
chmod g+s storage/framework/cache
chmod g+s storage/framework/views
chmod g+s storage/framework/sessions
find storage/framework/cache -type d -exec chmod g+s {} +
find storage/framework/views -type d -exec chmod g+s {} +
find storage/framework/sessions -type d -exec chmod g+s {} +

# 3. Set proper permissions (775 for directories, 664 for files)
find storage/framework/cache -type d -exec chmod 775 {} +
find storage/framework/cache -type f -exec chmod 664 {} +
find storage/framework/views -type d -exec chmod 775 {} +
find storage/framework/views -type f -exec chmod 664 {} +
find storage/framework/sessions -type d -exec chmod 775 {} +
find storage/framework/sessions -type f -exec chmod 664 {} +

# 4. Restart PHP-FPM
systemctl restart php-fpm
```

**Solution 2: Update Workflow to Handle Gracefully (Already Applied)**

The workflow files have been updated to continue on cache clear failures and fall back to manual deletion:
```yaml
# Clear cache with fallback to manual deletion
php artisan cache:clear || {
  echo "⚠️  Cache clear failed, manually removing cache files..."
  find storage/framework/cache/data -type f -delete 2>/dev/null || true
}
```

This allows deployment to complete even if cache clearing fails.

**Prevention:** 
- Apply proper permissions during initial setup (Step 3 in "Part 1: Prepare the Server")
- Ensure `chmod g+s` is set on cache directories so new files inherit group ownership
- Verify PHP-FPM is configured to respect group permissions

## Service Management Commands

### As Root:

```bash
# Navigate to runner directory
cd /home/github-runner/actions-runner

# Check status
sudo ./svc.sh status

# Start service
sudo ./svc.sh start

# Stop service
sudo ./svc.sh stop

# Restart service
sudo ./svc.sh restart

# Uninstall service
sudo ./svc.sh uninstall

# View logs
sudo journalctl -u actions.runner.* -f
```

### System-wide Service Commands:

```bash
# Using systemctl (if you know the full service name)
sudo systemctl status actions.runner.emmanuel-kayewa-cet-dashboard.test-server.service
sudo systemctl restart actions.runner.emmanuel-kayewa-cet-dashboard.test-server.service
sudo systemctl enable actions.runner.emmanuel-kayewa-cet-dashboard.test-server.service
```

## Production Server Setup

Repeat all steps above on the production server with these differences:

### Differences for Production:

1. **Runner name**: `production-server`
2. **Runner labels**: `production`
3. **Git branch**: `main` (instead of `develop`)
4. **Application path**: `/var/www/zesco-dashboard` (or your production path)
5. **GitHub Secret**: `PROD_APP_PATH` with production path value

### Production-Specific Considerations:

1. **Review deployment script** - Production may need additional steps:
   - Database backup before migration
   - Maintenance mode during deployment
   - Cache warming after deployment
   - Reverb/queue worker restart
   - Web server restart

2. **Test thoroughly** on test server first

3. **Plan deployment timing** - deploy during low-traffic periods

4. **Monitor logs** during first production deployment:
   ```bash
   sudo journalctl -u actions.runner.* -f
   tail -f /var/www/zesco-dashboard/storage/logs/laravel.log
   ```

## Workflow Files

The following workflow files are already set up in your repository:

### Test Server: `.github/workflows/deploy-test.yml`
- Triggers on push to `develop` branch
- Uses runner with label `[self-hosted, test]`
- Deploys to `${{ secrets.TEST_APP_PATH }}`

### Production Server: `.github/workflows/deploy-production.yml`
- Triggers on push to `main` branch
- Uses runner with label `[self-hosted, production]`
- Deploys to `${{ secrets.PROD_APP_PATH }}`

## Security Best Practices

1. ✅ **Use dedicated user** - Never run as root
2. ✅ **Keep SELinux enabled** - Configure it properly instead of disabling
3. ✅ **Use SSH keys** - For GitHub authentication
4. ✅ **Restrict permissions** - Only give runner minimum needed permissions
5. ✅ **Monitor logs** - Regularly check runner and application logs
6. ✅ **Update runner** - Keep the runner software updated
7. ✅ **Separate environments** - Use different runners for test and production
8. ✅ **Review workflows** - Audit what the workflows can do

## Maintenance

### Update Runner Software

```bash
# As github-runner user
cd ~/actions-runner

# Stop the service first (as root)
sudo ./svc.sh stop

# As github-runner, download new version
curl -o actions-runner-linux-x64-NEW_VERSION.tar.gz -L https://github.com/actions/runner/releases/download/vNEW_VERSION/actions-runner-linux-x64-NEW_VERSION.tar.gz

# Remove old binaries (keep config)
rm -rf bin externals

# Extract new version
tar xzf ./actions-runner-linux-x64-NEW_VERSION.tar.gz

# As root, restart service
sudo ./svc.sh start
```

### Backup Runner Configuration

```bash
# As root or github-runner
tar -czf ~/runner-backup-$(date +%Y%m%d).tar.gz \
  /home/github-runner/actions-runner/.runner \
  /home/github-runner/actions-runner/.credentials \
  /home/github-runner/actions-runner/.path
```

### Remove Runner

```bash
# As root
cd /home/github-runner/actions-runner
./svc.sh stop
./svc.sh uninstall

# As github-runner
./config.sh remove --token YOUR_REMOVAL_TOKEN

# Remove from GitHub UI
# Settings → Actions → Runners → Click on runner → Remove
```

## Troubleshooting Checklist

When deployment fails, check:

- [ ] Runner service is active: `sudo ./svc.sh status`
- [ ] Runner shows as "Idle" in GitHub UI
- [ ] GitHub secrets are configured correctly
- [ ] Application path is correct
- [ ] github-runner user has write permissions to app directory
- [ ] **PHP-FPM user has write access to storage/ and bootstrap/cache/**: `ls -la storage/` (should show `github-runner:apache` or similar)
- [ ] **Correct group ownership**: `ps aux | grep php-fpm` and match the group in `ls -la storage/`
- [ ] **Group sticky bit set on cache directories**: `ls -ld storage/framework/cache` (should show `drwxrwsr-x` - note the 's')
- [ ] **Cache files are group writable**: `ls -la storage/framework/cache/data/` (files should be `664` or `rw-rw-r--`)
- [ ] Git can pull from repository (SSH keys configured)
- [ ] Composer is installed and in PATH
- [ ] NPM is installed and in PATH
- [ ] PHP is installed and correct version
- [ ] SELinux is not blocking execution
- [ ] SELinux context is correct for storage: `ls -Z storage/` (should show `httpd_sys_rw_content_t`)
- [ ] Check workflow logs in GitHub Actions tab
- [ ] Check system logs: `sudo journalctl -u actions.runner.* -n 100`
- [ ] Check Laravel logs: `/var/www/zesco-dashboard-test/storage/logs/laravel.log`
- [ ] Check PHP-FPM logs: `/var/log/php-fpm/www-error.log`

## Additional Resources

- [GitHub Actions Self-Hosted Runners Documentation](https://docs.github.com/en/actions/hosting-your-own-runners)
- [SELinux Troubleshooting on RHEL/Oracle Linux](https://access.redhat.com/documentation/en-us/red_hat_enterprise_linux/8/html/using_selinux/troubleshooting-problems-related-to-selinux_using-selinux)
- [Laravel Deployment Best Practices](https://laravel.com/docs/deployment)

## Summary

This setup provides:
- ✅ Automated deployment on git push
- ✅ Works with private network servers
- ✅ Secure (runs as dedicated user with limited permissions)
- ✅ Persistent (survives server reboot)
- ✅ SELinux compliant
- ✅ Separate test and production environments

## Support

For issues or questions about the deployment setup, refer to this document and the main [DEPLOYMENT.md](.github/DEPLOYMENT.md) file.

---

**Document Version:** 1.0  
**Last Updated:** March 21, 2026  
**Server OS:** Oracle Linux 8  
**Application:** ZESCO Executive Dashboard (Laravel + Inertia + Vue)
