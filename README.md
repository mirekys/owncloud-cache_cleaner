# Cache cleaner

This ownCloud application installs a cron job, which periodically
cleans stray upload chunks from user's cache directory.

# Configuration

Following options could be customized in the `config.php`:

* **chunkgc.period** - How often should be cron job executed (in seconds, defaults to each 15min)
* **chunkgc.userlimit - Maximum number of users to be iterated in a single run (defaults to 100, should be <= your total number of users)
* **chunkgc.timelimit - Maximum amount of time to be spent by a single cron job run (in seconds, defaults to 60)
