#!/bin/sh
# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

# remove course if it exists allready
COURSE=`sudo -u www-data ../moodlectl.php  list-courses | grep TEST123 | awk '{print $1}'`
if [ -n "$COURSE" ]; then
  echo "deleteing test course first: $COURSE"
  sudo -u www-data ../moodlectl.php --delete-course --course-id=$COURSE
fi

# backup the course
FILE=`sudo -u www-data ../moodlectl.php backup-course --course-id=1 --course-files --blogs --messages --users`
echo "Backup file is: $FILE"

# use the backup in a restore
sudo -u www-data ../moodlectl.php --create-course-from-backup --category='1' --shortname='TEST123' \
                                  --fullname='Test 103 fullname' --summary='This is the test summary for the course Test 123' \
                                  --startdate='+2' --enrollable=2 --enrolstartdate='+2' --enrolenddate='+3 days' \
                                  --from-file=$FILE --messages --course-files --blogs --site-files --metacourse --users

#file-roller $FILE &
