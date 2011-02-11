#!/bin/sh
# export grades for a given course id
sudo  ../moodlectl.php export-gradebook --course-id=2

# list grade items for a give course id
sudo  ../moodlectl.php gradebook-list-items --course-id=2

# import a single grade item for a course/user
sudo  ../moodlectl.php import-gradebook --course-id=2 --grade-id=103 --user-id=3 --score=36 --feedback="jolly good"

# generate an import code - used to build up a list of imports to commit
sudo  ../moodlectl.php import-gradebook-code 
#1243206295

# add grade items to the import stack
sudo  ../moodlectl.php import-gradebook-add --import-code=1243206295 --course-id=2 --grade-id=103 --user-id=3 --score=13 --feedback="jolly good, show"
sudo  ../moodlectl.php import-gradebook-add --import-code=1243206295 --course-id=2 --grade-id=103 --user-id=14 --score=27 --feedback="jolly good, show"

# commit a stack of import added earlier
sudo  ../moodlectl.php import-gradebook-commit --import-code=1243206295 --course-id=2

