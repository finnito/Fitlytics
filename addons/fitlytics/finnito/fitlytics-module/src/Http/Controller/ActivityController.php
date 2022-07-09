<?php namespace Finnito\FitlyticsModule\Http\Controller;

use Finnito\FitlyticsModule\Activity\Contract\ActivityRepositoryInterface;
use Anomaly\Streams\Platform\Http\Controller\AdminController;
use Anomaly\Streams\Platform\View\ViewTemplate;

class ActivityController extends AdminController
{
	// Add some required classes to the controller.
	public function __construct(
		ActivityRepositoryInterface $activityRepository,
		ViewTemplate $template
	) {
		$this->activityRepository = $activityRepository;
		$this->template = $template;
	}

	// Display the activity
	public function index($id)
	{
		if (!$activity = $this->activityRepository->newQuery()
			->where("id", $id)->first()
		) {
			abort(404);
		}

		$this->template->set("meta_title", $activity->name);
		
		return view(
			"finnito.module.fitlytics::pages/activity",
			[
				"activity" => $activity,
			]
		);
	}

}