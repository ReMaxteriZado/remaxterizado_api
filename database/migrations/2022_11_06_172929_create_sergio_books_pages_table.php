<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSergioBooksPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('books_pages', function (Blueprint $table) {
            $table->id();
            $table->string("image");
            $table->unsignedBigInteger("book_id");
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('sergio_books')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('books_pages');
    }
}
