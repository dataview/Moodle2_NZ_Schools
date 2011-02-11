#!/bin/sh
# protect against execution
if [ -n "$GATEWAY_INTERFACE" ]; then
  echo "Content-type: text/html"
  echo ""
  echo "<html><head><title>ERROR</title></head><body><h1>ERROR</h1></body></html>"
  exit 0
fi

cd unit_tests
if [ -d /tmp ]; then
    if [ -f /tmp/moodlectl_trace.log ]; then
        sudo -u www-data rm -f /tmp/moodlectl_trace.log
    fi 
    sudo -u www-data MOODLECTL_LOG=1 phpunit "RunAllTests"
 else
    sudo -u www-data phpunit "RunAllTests"
 fi

