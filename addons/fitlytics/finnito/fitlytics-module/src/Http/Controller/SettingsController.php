<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Anomaly\Streams\Platform\Http\Controller\PublicController;
use Anomaly\Streams\Platform\Http\Controller\AdminController;
use Finnito\FitlyticsModule\Activity\ActivityModel;
use Finnito\FitlyticsModule\Activity\Contract\ActivityRepositoryInterface;
use Finnito\FitlyticsModule\Plan\Contract\PlanRepositoryInterface;
use Finnito\FitlyticsModule\Note\Contract\NoteRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Anomaly\Streams\Platform\View\ViewTemplate;
use \Anomaly\Streams\Platform\Message\MessageBag;
use Anomaly\UsersModule\User\Form\UserFormBuilder;
use Finnito\FitlyticsModule\User\Form\UserHRZonesFormBuilder;
use Anomaly\UsersModule\User\UserModel;




class SettingsController extends AdminController
{
    public function __construct(
        ViewTemplate $template,
        MessageBag $messages,
        ActivityRepositoryInterface $activitiesRepository
    ) {
        $this->template = $template;
        $this->messages = $messages;
        $this->activitiesRepository = $activitiesRepository;
    }

    public function index(UserFormBuilder $form)
    {
        return view("finnito.module.fitlytics::pages/settings");
    }

    public function hrZones(UserHRZonesFormBuilder $form)
    {
        return view(
            "finnito.module.fitlytics::pages/hr_zones",
            [
                "maxHR" => $this->activitiesRepository->maxHRThisYear(),
            ]
        );
    }
}
