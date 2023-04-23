### Installation:
1. cd docker && docker-compose up -d
2. docker exec subscription-reminder-php composer install
3. docker exec subscription-reminder-php php seed.php

### Cron setup:

1. "* * * * * /usr/local/bin/php /var/www/html/handlers/jobs_handler.php email_sender"
2. "* * * * * /usr/local/bin/php /var/www/html/handlers/jobs_handler.php subscription_reminder"

### Usage

There are two main scripts:
1. `email_sender.php` checks emails validation, sends notifications before subscriptions will be expired.
   `check_email` function will be called only before an attempt to send an email. It guaranties minimum usage of this chargeable function
2. `subscription_reminder.php` sends reminders on valid emails about expired subscriptions

They can be executed whether via CLI or via HTTP requests on pages

HTTP calls:
1. http://localhost/email_sender.php
2. http://localhost/subscription_reminder.php

CLI calls:
1. docker exec subscription-reminder-php php public/email_sender.php
2. docker exec subscription-reminder-php php public/subscription_reminder.php

Cron setup:
1. "* * * * * /usr/local/bin/php /var/www/html/public/subscription_reminder.php"
2. "* * * * * /usr/local/bin/php /var/www/html/public/email_sender.php"

### Config && Logs

1. It is possible to set a config at `config.php` file.
There are settings of jobs that can be used to send emails and check validation of emails in parallel.
2. The app collects logs in `logs` folder about all major operations in `info.log` file, errors in `errors.log`, 
job errors in `jobs-errors.log`