<?php namespace Finnito\FitlyticsModule\Note;

use Finnito\FitlyticsModule\Note\Contract\NoteInterface;
use Anomaly\Streams\Platform\Model\Fitlytics\FitlyticsNotesEntryModel;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class NoteModel extends FitlyticsNotesEntryModel implements NoteInterface
{
    use HasFactory;

    /**
     * @return NoteFactory
     */
    protected static function newFactory()
    {
        return NoteFactory::new();
    }

    public function description()
    {
        if ($this->injured) {
            $injured = "Yes";
        } else {
            $injured = "No";
        }

        if ($this->sick) {
            $sick = "Yes";
        } else {
            $sick = "No";
        }

        return "{$this->note}<br>
        ---<br>
        Sick: {$sick}<br>
        Injured: {$injured}<br>
        Sleep Quality: {$this->sleep_quality}<br>
        Stress Level: {$this->stress_level}<br>
        Weight: {$this->weight}<br>";
    }
}
