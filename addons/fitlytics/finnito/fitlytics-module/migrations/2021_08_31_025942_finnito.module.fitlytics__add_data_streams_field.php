<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsAddDataStreamsField extends Migration
{

    public function up()
    {
        if (!Schema::hasColumn('fitlytics_activities', 'data_streams')) {
            Schema::table('fitlytics_activities', function (Blueprint $table) {
                $table->text('data_streams')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('fitlytics_activities', 'data_streams')) {
            Schema::table('fitlytics_activities', function (Blueprint $table) {
                $table->dropColumn('data_streams');
            });
        }
    }
}
