
TODO: Implement weekly HR zone summary graph

TODO: Implement multi-user data storage

TODO: Implement graphs page

TODO: Implement evening training summaries

TODO: Implement HR bucket static calculation

TODO: Implement HR zone calculation/input

FIX: [previous exception] [object] (Exception(code: 0): DateTime::__construct(): Failed to parse time string
 (.env) at position 1 (e): The timezone could not be found in the database at /srv/fitlytics.lesueur.nz
/vendor/nesbot/carbon/src/Carbon/Traits/Creator.php:86).
This is due to the URL routing `"{week?}" => []`. I need to change the URL scheme. Perhaps /week/{week}.