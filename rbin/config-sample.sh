##
## This is a sample config shell, make this your own
##

## Either pass the site name as an argument
site=$1

##
## These are the mysql credentials that will be used for the new site
## being installed.
##
dbname=wp_${site}
dbuser={{WP_DBUSER}}
dbpasswd={{WP_DBPASSWD}}

## This is where the newsite will installed
sitedir={{DOCROOT}}/${site}

##
## The new site will be setup as a sub-domain of the staging site domain
## mainly because I use DesktopPress a lot and DesktopPress really only
## works out with out manual intervention if you export to a domain or 
## sub-domain.  e.g.
##
##		site.staging.com
##
## In otherwords ServerPress does NOT automatically work with subfolders,
## ! e.g.:
##
##  	staging.com/site
##
siteurl=${site}.{{STAGING_DOMAIN}}
sitetitle='Site created newsite.sh'

##
## These mysql credentials are used to log into mysql and create a new
## database.  In otherwords these are the mysql super user credentials
##
admin_user={{SU_DBUSER}}
admin_password={{SU_DBPASSWD}}

## The WP Admain email
admin_email={{WP_ADMIN_EMAIL}}
