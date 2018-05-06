<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDetalleVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('detalle_ventas', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('id_venta')->unsigned();
            $table->foreign('id_venta')->references('id')->on('ventas')->onDelete('cascade');

            $table->integer('id_articulo')->unsigned();
            $table->foreign('id_articulo')->references('id')->on('articulos');

            $table->integer('cantidad');
            $table->decimal('precio', 11, 2);
            $table->decimal('descuento', 11, 2);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('detalle_ventas');
    }
}
