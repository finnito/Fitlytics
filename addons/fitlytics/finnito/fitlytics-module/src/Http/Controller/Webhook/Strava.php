<?php namespace Finnito\FitlyticsModule\Http\Controller\Webhook;

use Illuminate\Http\Request;
use Anomaly\Streams\Platform\Http\Controller\ResourceController;
use Finnito\FitlyticsModule\WebhookStrava\WebhookStravaModel;
use Finnito\FitlyticsModule\WebhookStrava\Job\ProcessWebhook;

class Strava extends ResourceController
{
    public function index(WebhookStravaModel $model, Request $request)
    {
        /**
         * Handle the validation request
         **/
        if ($request->has("hub_mode")) {
            if ($request->input("hub_mode") == "subscribe") {
                return response()->json(['hub.challenge' => $request->input("hub_challenge")]);
            }
        }

        /**
         * Handle a regular request
         **/
        else {
            $event = $model->create(["content" => json_encode($request->all())]);
            ProcessWebhook::dispatch($event);
            return response(null, 200);
        }
    }
}
