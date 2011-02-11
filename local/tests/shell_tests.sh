#!/bin/sh
# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

# just command line options
sudo -u www-data ../moodlectl.php --create-wiki --course-id=1 --name='The new wiki' --summary='Just a summary'

# JSON
cat json.txt | sudo -u www-data ../moodlectl.php --create-wiki --json --name='overriding: json'

# PHP
cat php.txt | sudo -u www-data ../moodlectl.php --create-wiki --php --name='overriding: php'

# YAML
cat yaml.txt | sudo -u www-data ../moodlectl.php --create-wiki --yaml --name='overriding: yaml'

## JSON
cat batch_json.txt | sudo -u www-data ../moodlectl.php --batch --json

## PHP
cat batch_php.txt | sudo -u www-data ../moodlectl.php --batch --php

## YAML
cat batch_yaml.txt | sudo -u www-data ../moodlectl.php --batch --yaml
