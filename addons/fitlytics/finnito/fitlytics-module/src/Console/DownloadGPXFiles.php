<?php namespace Finnito\FitlyticsModule\Console;

use Illuminate\Console\Command;

class DownloadGPXFiles extends Command
{
    protected $name = "strava:download";
    protected $description = "Downloads missing GPX files from Strava.";
    
    public function handle()
    {
        return "Hello!";
    }
}
