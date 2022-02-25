#!/bin/bash

chown -hR www-data:www-data /data/app/website_new/

exec supervisord -n