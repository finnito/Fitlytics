#!/usr/bin/env zsh

# To sync in the other direction
# rsync -av /Users/finnlesueur/Git/fitlytics/database/fitlytics.sqlite root@172.105.169.195:/srv/fitlytics.lesueur.nz/database/fitlytics.sqlite

cd /Users/finnlesueur/Git/fitlytics || exit

rsync \
	--archive \
	root@172.105.169.195:/srv/fitlytics.lesueur.nz/database/fitlytics.sqlite \
	/Users/finnlesueur/Git/fitlytics/database/

php artisan streams:compile
