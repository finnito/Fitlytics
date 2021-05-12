<?php

use Anomaly\Streams\Platform\Database\Migration\Migration;

class FinnitoModuleFitlyticsUpdateActivityJsonConfig extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $field = $this->fields()->findBySlugAndNamespace('activity_json', 'fitlytics');
        $field->type = "visiosoft.field_type.json";
        $field->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $field = $this->fields()->findBySlugAndNamespace('activity_json', 'fitlytics');
        $field->type = "anomaly.field_type.textarea";
        $field->save();
    }
}
