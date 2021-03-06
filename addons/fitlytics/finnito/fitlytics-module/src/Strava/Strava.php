<?php namespace Finnito\FitlyticsModule\Strava;

use Illuminate\Support\Facades\App;
use \Finnito\FitlyticsModule\StravaCredential\Contract\StravaCredentialRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class Strava
{
    public function getCredentials()
    {
        $credentialsRepository = App::make(StravaCredentialRepositoryInterface::class);
        $credentials = $credentialsRepository
            ->newQuery()
            ->orderBy("updated_at", "desc")
            ->first();

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
                var_dump($credentials);
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
        $credentials = $this->getCredentials();
        $ch = curl_init("https://www.strava.com/api/v3" . $route . "?" . http_build_query($parameters));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "Authorization: Bearer {$credentials->access_token}"
        ));
        $resp = json_decode(curl_exec($ch));
        curl_close($ch);
        return $resp;
    }

    public function put($route, $body)
    {
        $credentials = $this->getCredentials();
        $curl = curl_init();
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://www.strava.com/api/v3/' . $route,
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'PUT',
          CURLOPT_POSTFIELDS => $body,
          CURLOPT_HTTPHEADER => array("Authorization: Bearer {$credentials->access_token}"),
        ));
        $response = json_decode(curl_exec($curl));
        curl_close($curl);
        return $response;
    }
}
