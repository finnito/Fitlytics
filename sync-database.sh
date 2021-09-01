#!/usr/bin/env bash

/usr/local/bin/terminal-notifier \
        -group "com.finnlesueur.fitlytics" \
        -title "Backup" \
        -message "Syncing fitlytics.sqlite" \
        -appIcon "https://seeklogo.com/images/L/linode-logo-0B22204438-seeklogo.com.png"

rsync -av root@172.105.169.195:/srv/fitlytics.lesueur.nz/database/fitlytics.sqlite /Users/finnlesueur/Git/fitlytics/database/

if [ $? -eq 0 ]
then
        /usr/local/bin/terminal-notifier \
                -group "com.finnlesueur.fitlytics" \
                -title "Backup" \
                -message "Synced fitlytics.sqlite" \
                -appIcon "https://seeklogo.com/images/L/linode-logo-0B22204438-seeklogo.com.png"
else
        /usr/local/bin/terminal-notifier \
                -group "com.finnlesueur.fitlytics" \
                -title "Backup" \
                -message "Synce Failed fitlytics.sqlite" \
                -appIcon "https://seeklogo.com/images/L/linode-logo-0B22204438-seeklogo.com.png"
        exit 1
fi