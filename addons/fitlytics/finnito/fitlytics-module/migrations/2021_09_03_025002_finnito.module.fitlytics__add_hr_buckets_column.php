<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsAddHrBucketsColumn extends Migration
{

    public function up()
    {
        if (!Schema::hasColumn('fitlytics_activities', 'hr_buckets')) {
            Schema::table('fitlytics_activities', function (Blueprint $table) {
                $table->text('hr_buckets')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('fitlytics_activities', 'hr_buckets')) {
            Schema::table('fitlytics_activities', function (Blueprint $table) {
                $table->dropColumn('hr_buckets');
            });
        }
    }
}
