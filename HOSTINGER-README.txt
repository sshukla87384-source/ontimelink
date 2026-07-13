==============================================================
 ONE-TIME LINK — HOSTINGER SHARED HOSTING QUICK DEPLOY
==============================================================
This package is COMPLETE: vendor/ (all PHP dependencies) is
included. No Composer, Node, SSH or terminal is required for
the basic setup — only hPanel and the File Manager.

--------------------------------------------------------------
 STEP 1 — PHP version
--------------------------------------------------------------
hPanel -> Advanced -> PHP Configuration -> select PHP 8.3.

--------------------------------------------------------------
 STEP 2 — Database
--------------------------------------------------------------
hPanel -> Databases -> MySQL Databases -> create a database
and user. Note: database name, username, password.
(Host is: localhost)

--------------------------------------------------------------
 STEP 3 — Upload & extract
--------------------------------------------------------------
1. hPanel -> Files -> File Manager -> open public_html
2. DELETE the default files inside public_html (if any).
3. Upload onetimelink-hostinger.zip INTO public_html.
4. Right-click the zip -> Extract -> extract HERE
   (files must land directly in public_html, so that
   public_html/artisan and public_html/public/index.php exist).
5. Delete the zip after extraction.

This package uses the "whole project in public_html" layout:
the included root .htaccess routes all traffic into public/
and blocks direct access to .env, composer files and artisan.

--------------------------------------------------------------
 STEP 4 — Configure .env
--------------------------------------------------------------
1. In File Manager, rename  .env.hostinger  ->  .env
2. Edit .env and fill in:
      APP_URL=https://yourdomain.com
      DB_DATABASE=...   DB_USERNAME=...   DB_PASSWORD=...
      MAIL_USERNAME / MAIL_PASSWORD  (create a mailbox first:
        hPanel -> Emails, e.g. no-reply@yourdomain.com)
      ADMIN_EMAIL=you@yourdomain.com
      ADMIN_PASSWORD=choose-a-strong-password
   APP_KEY is already generated for this package. If you ever
   need a new one and have SSH: php artisan key:generate
   (WARNING: a new key breaks all previously created links).

--------------------------------------------------------------
 STEP 5 — Install the database (one-time, in the browser)
--------------------------------------------------------------
Open:   https://yourdomain.com/setup.php?key=YOUR_SETUP_KEY
(The exact key is inside setup.php - open it in File Manager
 and read the SETUP_KEY line, or set your own.)

The installer runs the migrations, creates your admin account
from ADMIN_EMAIL / ADMIN_PASSWORD, builds the storage link and
caches. When it reports SUCCESS:

   >>> DELETE setup.php IMMEDIATELY <<<

--------------------------------------------------------------
 STEP 6 — Cron job
--------------------------------------------------------------
hPanel -> Advanced -> Cron Jobs -> Add, every minute:

  cd /home/USERNAME/domains/YOURDOMAIN/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1

(Adjust the path shown at the top of the Cron Jobs page.)

--------------------------------------------------------------
 STEP 7 — SSL
--------------------------------------------------------------
hPanel -> Security -> SSL -> install free certificate and
enable "Force HTTPS". The app requires HTTPS (secure cookies).

--------------------------------------------------------------
 DONE — smoke test
--------------------------------------------------------------
* Visit /            -> create a guest link
* Open the link once -> it redirects; open again -> Already
                        Redeemed
* Log in with ADMIN_EMAIL / ADMIN_PASSWORD -> /admin works

Full documentation: docs/ folder
(INSTALL.md, DEPLOYMENT_HOSTINGER.md, SECURITY.md,
 OPERATIONS.md)
==============================================================
