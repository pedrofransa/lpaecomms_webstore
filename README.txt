LPA eComms Webstore - Student Project

How to run:
1. Install Wampserver.
2. Start Apache and MySQL.
3. Copy this folder into C:\wamp64\www.
4. Open phpMyAdmin and import database.sql.
5. Open browser and go to: http://localhost/lpaecomms_webstore

Main files:
- index.php = home page
- catalog.php = product catalog and add to cart
- register.php = customer registration
- login.php = customer login using bcrypt password verification
- cart.php = checkout cart page using cookie
- payment.php = checkout payment page
- complete.php = saves invoice and clears cart
- mashup.php = mission statement, YouTube, Facebook placeholder, Google map
- manager/login.php = protected manager login and product administration
- log/lpalog.log = log file created automatically

Manager setup:
1. Open http://localhost/lpaecomms_webstore/manager/login.php
2. On the first visit, create the first manager account.
3. Sign in to create, edit, enable or disable products and upload product images.
