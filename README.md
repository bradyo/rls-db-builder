Yeast Replicative Lifespan Database
===================================

Yeast Replicative Lifespan (RLS) database was developed by the Kaeberlein Lab,
University of Washington. It allows users to search compiled lifespan data across
yeast replicative lifespan experiments.

This database shows cell samples pooled by strain or genotype, and includes a 
number of useful fields such as mean lifespan and normalized genotypes that can 
be used in searches. Additionally, comparisons between cell samples are computed
for pre-defined experiment matched reference samples so users can view survival
curves and Ranksum p-values between lifespan samples.

[Kaeberlein Lab](http://kaeberleinlab.org/)
University of Washington


Requirements
------------

1. PHP (version 5.3)
2. MySQL
3. R
4. Apache Web server
5. phpunit (version 5.5+) to run tests


Installation
------------

1. Unzip the project directory onto your system.

2. Point your web server to the "web" directory in the project directory.

3. Run "php app/check.php" script to make sure your

4. Edit "app/config/parameters.ini" to set your database connection parameters 
   and R script execution path.

5. Run "php app/install.php" to create the default database.

6. Run unit tests with "phpunit -c app/"

6. Copy your input data files into "build/input" and run "php app/build.php" to
   compile your data files into the database. Any errors are written to 
   "data/logs/build.log"

7. You can use the web interface to access the build results.
