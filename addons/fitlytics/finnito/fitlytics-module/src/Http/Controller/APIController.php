<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Finnito\FitlyticsModule\Activity\ActivityRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class APIController extends PublicController
{

    public function __construct(Auth $auth, Request $request)
    {
        $this->auth = $auth;
        $this->request = $request;

        if ($request->has("week-of")) {
            $this->week_of = $request->query("week-of");
        } else {
            $this->week_of = "now";
        }
    }
}
