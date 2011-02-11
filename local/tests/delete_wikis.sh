#!/bin/sh
# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

if [ -z "$1" ]; then
  echo "Must provide starting number for range"
  exit 1;
fi

if [ -z "$2" ]; then
  echo "Must provide ending number for range"
  exit 1;
fi

echo "deleting wikis from $1 to $2"
for i in `perl -e "print join(' ', ($1..$2))"`
do
sudo -u www-data  ../moodlectl.php --delete-wiki --module-id=$i
done
