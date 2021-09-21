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

    public function call($route, $parameters = [], $method = "GET", $body = [])
    {
        dd($route, $parameters, $method, $body);
        $credentials = $this->getCredentials();

        if ($method == "GET") {
            $ch = curl_init("https://www.strava.com/api/v3" . $route . "?" . http_build_query($parameters));
        } else {
            $ch = curl_init("https://www.strava.com/api/v3" . $route);
        }
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$credentials->access_token}"
        ));

        if ($method == "PUT") {
            curl_setopt($ch, CURLOPT_PUT, 1);
        } elseif ($method == "POST") {
            curl_setopt($ch, CURLOPT_POST, 1);
        }

        if (in_array($method, ["POST", "PUT"])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $resp = json_decode(curl_exec($ch));
        dd($resp);
        return $resp;
    }
}
