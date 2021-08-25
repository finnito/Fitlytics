<?php namespace Finnito\FitlyticsModule\Http\Controller\Webhook;

use Illuminate\Http\Request;
use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\WebhookStrava\WebhookStravaModel;

class Strava extends PublicController
{
    public function index(WebhookStravaModel $model, Request $request)
    {
        /**
         * Handle the validation request
         **/
        if ($request->input("hub.mode") == "subscribe") {
            return response()->json(['hub.challenge' => $request->input("hub.challenge")]);
        }

        /**
         * Handle a regular request
         **/
        else {
            $model->create(["content" => $request->all()]);
            return response(null, 200);
        }
    }
}
