<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMailboxesColumnToWebhooksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('webhooks', function (Blueprint $table) {
            // Mailbox IDs in JSON format.
            $table->text('mailboxes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('webhooks', function (Blueprint $table) {
            $table->dropColumn('mailboxes');
        });
    }
}
