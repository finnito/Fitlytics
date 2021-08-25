<?php namespace Finnito\FitlyticsModule\WebhookStrava;

use Finnito\FitlyticsModule\WebhookStrava\Contract\WebhookStravaInterface;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsWebhookStravaEntryModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WebhookStravaModel extends FitlyticsWebhookStravaEntryModel implements WebhookStravaInterface
{
    use HasFactory;

    /**
     * @return WebhookStravaFactory
     */
    protected static function newFactory()
    {
        return WebhookStravaFactory::new();
    }
}
