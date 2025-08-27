# Gmail SMTP Setup Guide

## Problem
The error "Failed to authenticate on SMTP server" occurs because Gmail requires additional security measures for third-party applications.

## Solution Steps

### 1. Enable 2-Step Verification
1. Go to your Google Account settings: https://myaccount.google.com/security
2. Under "Signing in to Google," select **2-Step Verification**
3. Follow the steps to enable it

### 2. Generate App Password
1. After enabling 2-Step Verification, go to: https://myaccount.google.com/apppasswords
2. Select "Mail" as the app
3. Select "Other (Custom name)" and enter "RD Agent App"
4. Click "Generate"
5. Copy the 16-character app password (it will look like: `abcd efgh ijkl mnop`)

### 3. Update .env File
Replace the current password with the generated app password:

```env
MAIL_PASSWORD=your_generated_app_password_here
```

Remove any spaces from the app password when adding it to the .env file.

### 4. Clear Configuration Cache
Run these commands after updating the .env file:

```bash
php artisan config:clear
php artisan cache:clear
```

### 5. Test Email Configuration
You can test if the configuration works by creating a test route or using Tinker:

```bash
php artisan tinker
```

Then in Tinker:
```php
Mail::raw('Test email', function($message) {
    $message->to('your-email@example.com')->subject('Test Subject');
});
```

## Alternative Solution (If still not working)

If you continue to have issues, you can:

1. **Use a different email service** like Mailtrap for development
2. **Disable less secure apps** (not recommended as Google is phasing this out)
3. **Use a local mail server** like MailHog for development

## Mailtrap Alternative Setup

For development, you can use Mailtrap (free tier available):

1. Sign up at https://mailtrap.io/
2. Get your SMTP credentials from Mailtrap
3. Update .env with Mailtrap settings:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
```

This will prevent Gmail authentication issues during development.
