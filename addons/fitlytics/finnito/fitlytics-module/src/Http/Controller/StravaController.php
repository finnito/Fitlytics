<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\StravaCredential\Contract\StravaCredentialRepositoryInterface;
use Finnito\FitlyticsModule\StravaCredential\StravaCredentialModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class StravaController extends PublicController
{

    public function connections(StravaCredentialModel $credentialModel, StravaCredentialRepositoryInterface $credentials)
    {
        $user = Auth::user();
        return view(
            'finnito.module.fitlytics::pages/strava',
            [
                "credentials" => $credentials->newQuery()->where("user_id", $user->id)->first(),
                "authURL" => $credentialModel->authURL(),
                "subscriptions" => $credentialModel->viewSubscriptions(),
            ]
        );
    }

    public function callback(Request $request, StravaCredentialModel $model, StravaCredentialRepositoryInterface $credentials)
    {
        $user = Auth::user();

        if ($request->input("error") == "access_denied") {
            return redirect('/strava');
        }

        $state = $request->input("state");
        $code = $request->input("code");
        $scope = $request->input("scope");

        if (!str_contains($scope, "activity:write")) {
            return redirect($model->authURL());
        }

        $user->strava_scopes = $scope;
        $user->save();

        $response = Http::post("https://www.strava.com/oauth/token", [
            "client_id" => env("STRAVA_CLIENT_ID"),
            "client_secret" => env("STRAVA_CLIENT_SECRET"),
            "code" => $code,
            "grant_type" => "authorization_code",
        ]);

        $responseJson = $response->json();

        StravaCredentialModel::updateOrInsert(
            ['access_token' => $responseJson["access_token"]],
            [
                "refresh_token" => $responseJson["refresh_token"],
                "expires_at" => $responseJson["expires_at"],
                "user_id" => $user->id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                "created_by_id" => $user->id,
                "updated_by_id" => $user->id,
            ]
        );

        return redirect("/");
    }

    public function create_subscription()
    {
        
    }
}
