<?php namespace Finnito\FitlyticsModule\Strava;

use Illuminate\Support\Facades\App;
use \Finnito\FitlyticsModule\StravaCredential\Contract\StravaCredentialRepositoryInterface;

class Strava
{
    public function getCredentials()
    {
        $credentialsRepository = App::make(StravaCredentialRepositoryInterface::class);
        $credentials = $credentialsRepository->first();

        if (time() >= $credentials->expires_at) {
            // echo "Token expired..\n";

            // echo "Requesting new token..\n";
            $ch = curl_init("https://www.strava.com/oauth/token");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
                'grant_type' => 'refresh_token',
                'refresh_token' => $credentials->refresh_token,
                'client_id' => env("STRAVA_CLIENT_ID"),
                'client_secret' => env("STRAVA_CLIENT_SECRET"),
            ]));
            $response = json_decode(curl_exec($ch));

            if (!isset($response->access_token)) {
                echo "Error fetching access token.\n";
                var_dump($response);
                exit(2);
            }

            $credentials->access_token = $response->access_token;
            $credentials->refresh_token = $response->refresh_token;
            $credentials->expires_at = $response->expires_at;
            $credentialsRepository->save($credentials);
            // echo "New token saved..\n";
        }

        return $credentials;
    }

    public function call($route, $parameters = [])
    {
        $credentials = $this->getCredentials();

        $ch = curl_init("https://www.strava.com/api/v3" . $route . "?" . http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$credentials->access_token}"
        ));
        return json_decode(curl_exec($ch));
    }
}
