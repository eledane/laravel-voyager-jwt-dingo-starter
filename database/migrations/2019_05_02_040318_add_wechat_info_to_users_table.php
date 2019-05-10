<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWechatInfoToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('wechat_openId')->unique()->nullable()->after('email');
            $table->string('wechat_unionId')->unique()->nullable()->after('wechat_openId');
            $table->string('wechat_session_key')->nullable()->after('wechat_unionId');
            $table->string('wechat_gender')->nullable()->after('wechat_session_key');
            $table->string('wechat_city')->nullable()->after('wechat_gender');
            $table->string('wechat_province')->nullable()->after('wechat_city');
            $table->string('wechat_country')->nullable()->after('wechat_province');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('wechat_openId');
            $table->dropColumn('wechat_unionId');
            $table->dropColumn('wechat_session_key');
            $table->dropColumn('wechat_gender');
            $table->dropColumn('wechat_city');
            $table->dropColumn('wechat_province');
            $table->dropColumn('wechat_country');
        });
    }
}
