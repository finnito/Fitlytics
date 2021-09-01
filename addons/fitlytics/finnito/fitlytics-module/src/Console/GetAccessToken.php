<?php namespace Finnito\FitlyticsModule\Console;

use Illuminate\Console\Command;
use Finnito\FitlyticsModule\Strava\Strava;

class GetAccessToken extends Command
{
    protected $name = "strava:token";
    protected $description = "Gets latest access token.";

    public function handle()
    {
        $strava = new Strava();
        $credentials = $strava->getCredentials();
        $expires_at = \Carbon\Carbon::createFromTimestamp($credentials->expires_at)->timezone("Pacific/Auckland")->toDayDateTimeString();
        printf("Token expires at " . $expires_at . "\n");
        printf($credentials->access_token);
    }
}
