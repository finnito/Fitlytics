<?php namespace Finnito\FitlyticsModule\StravaCredential;

use Finnito\FitlyticsModule\StravaCredential\Contract\StravaCredentialInterface;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsStravaCredentialsEntryModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Http;

class StravaCredentialModel extends FitlyticsStravaCredentialsEntryModel implements StravaCredentialInterface
{
    use HasFactory;

    // protected $fillable = true;
    // protected $countable = true;

    /**
     * @return StravaCredentialFactory
     */
    protected static function newFactory()
    {
        return StravaCredentialFactory::new();
    }

    public function authURL()
    {
        // $local_ip = 'localhost';
        // $local_port = '8000';
        // $local_redirect_uri = 'http://'.$local_ip.':'.$local_port.'/authorization-code/callback';
        $local_redirect_uri = 'http://'.env("APPLICATION_DOMAIN").'/authorization-code/callback';
        $strava_authorise_url = "https://www.strava.com/oauth/authorize" . '?' . http_build_query([
            'response_type' => 'code',
            'client_id' => env("STRAVA_CLIENT_ID"),
            'redirect_uri' => $local_redirect_uri,
            'scope' => "read_all,activity:read_all,profile:read_all,activity:write",
            'state' => bin2hex(random_bytes(5)),
        ]);

        return $strava_authorise_url;
    }

    public function viewSubscriptions()
    {
        $response = Http::get("https://www.strava.com/api/v3/push_subscriptions", [
            "client_id" => env("STRAVA_CLIENT_ID"),
            "client_secret" => env("STRAVA_CLIENT_SECRET"),
        ]);
        return $response->json();
    }
}
