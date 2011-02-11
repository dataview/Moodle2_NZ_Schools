#!/bin/sh
# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

# upload scorm package
sudo  ../moodlectl.php upload-file --file=./tests/test_scorm.zip --course-id=1 --destination='/'

# create the scorm activity in course 1
# you can actually find these if you go to http://{$CFG->webroot}/mod/scorm/index.php?id=1
ID=`sudo -u www-data ../moodlectl.php create-scorm --course-id=1 --reference=test_scorm.zip --name='a test' \
                                               --summary='this is the summary' | head -1 | awk '{print $2}'`
echo "SCORM id is: $ID"

# upload scorm package - again - would be good if it was different
sudo  ../moodlectl.php upload-file --file=./tests/test_scorm.zip --course-id=1 --destination='/'

# update the scorm package - if the md5 check sum changes then it will be unpacked
sudo -u www-data ../moodlectl.php change-scorm --scorm-id=$ID  --reference=test_scorm.zip

# delete the activity afterwards
sudo -u www-data ../moodlectl.php delete-scorm --scorm-id=$ID
echo "RC from delete is: $?"
