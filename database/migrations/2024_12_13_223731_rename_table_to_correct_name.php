<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::rename('resturant_user', 'restaurant_user');
    }
    
    public function down()
    {
        Schema::rename('restaurant_user', 'resturant_user');
    }
};
