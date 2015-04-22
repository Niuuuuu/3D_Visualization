Capstone Project
================

Requirements
--------------------------------------
- WebGL compatible browser (Chrome is reccomended)
- MySQL, Apache, PHP
	- Windows: [WAMP download](http://www.wampserver.com/en/)
	- Mac: [MAMP download](http://www.mamp.info/en/index.html)


Setup
--------------------------------------
This guide assumes a local of MAMP/WAMP is running.

Navigate to your webservers directory and clone the github repo. This is the folder that Apache uses to serve webpages and can be found in your WAMP/MAMP config.

Copy and paste the excel data file to the parsing folder. Rename the file to something easy you can type, you will need this shortly.

Enter the cloned directory and navigate to the parsing folder. Execute the setup script by running:
```bash
php setup.php
```
You will be prompted for your SQL's username, password, config info and the excel file you just copied and pasted.


Edit config.php in the dashboard directory to match your server settings.

To view different open data.php and maxData.php and edit

```sql
WHERE kpi_id = 1
```

The values in data.php and maxData.php must be the same. If you are unsure set both to 1.
