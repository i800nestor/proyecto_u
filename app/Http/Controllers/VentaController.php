<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Venta;
use App\DetalleVenta;

class VentaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $buscar = $request->buscar;
        $criterio = $request->criterio;

        if ($buscar == '')
            $ventas =  Venta::join('personas', 'ventas.id_cliente', '=', 'personas.id')
        	->join('users', 'ventas.id_usuario', '=', 'users.id')
        	->select('ventas.id', 'ventas.tipo_comprobante', 'ventas.serie_comprobante',
        		'ventas.num_comprobante', 'ventas.fecha_hora', 'ventas.impuesto',
        		'ventas.total', 'ventas.estado', 'personas.nombre', 'users.usuario')
        	->orderBy('ventas.id', 'desc')->paginate(5);
        else
            $ventas =  Venta::join('personas', 'ventas.id_cliente', '=', 'personas.id')
        	->join('users', 'ventas.id_usuario', '=', 'users.id')
        	->select('ventas.id', 'ventas.tipo_comprobante', 'ventas.serie_comprobante',
        		'ventas.num_comprobante', 'ventas.fecha_hora', 'ventas.impuesto',
        		'ventas.total', 'ventas.estado', 'personas.nombre', 'users.usuario')
        	->where('ventas.'.$criterio, 'like', '%'. $buscar . '%')
        	->orderBy('ventas.id', 'desc')->paginate(5);

        return [
            'pagination' => [
                'total' => $ventas->total(),
                'current_page' => $ventas->currentPage(),
                'per_page' => $ventas->perPage(),
                'last_page' => $ventas->lastPage(),
                'from' => $ventas->firstItem(),
                'to' => $ventas->lastItem()
            ],
            'ventas' => $ventas
        ];
    }

    public function obtenerCabecera(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $id = $request->id;

        $venta =  Venta::join('personas', 'ventas.id_cliente', '=', 'personas.id')
        ->join('users', 'ventas.id_usuario', '=', 'users.id')
        ->select('ventas.id', 'ventas.tipo_comprobante', 'ventas.serie_comprobante',
            'ventas.num_comprobante', 'ventas.fecha_hora', 'ventas.impuesto',
            'ventas.total', 'ventas.estado', 'personas.nombre', 'users.usuario')
        ->where('ventas.id', '=', $id)
        ->orderBy('ventas.id', 'desc')
        ->take(1)
        ->get();
        

        return $venta;
    }

    public function obtenerDetalles(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $id = $request->id;

        $detalles =  DetalleVenta::join('articulos', 'detalle_ventas.id_articulo', '=', 'articulos.id')
        ->select('detalle_ventas.cantidad', 'detalle_ventas.precio', 'detalle_ventas.descuento', 'articulos.nombre as articulo')
        ->where('detalle_ventas.id_venta', '=', $id)
        ->orderBy('detalle_ventas.id', 'desc')
        ->get();
        

        return $detalles;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        try {
        	
        	DB::beginTransaction();

        	$my_Time = Carbon::now('America/Caracas');

        	$venta = new Venta();
        	$venta->id_cliente = $request->id_cliente;
        	$venta->id_usuario = \Auth::user()->id;
        	$venta->tipo_comprobante = $request->tipo_comprobante;
        	$venta->serie_comprobante = $request->serie_comprobante;
        	$venta->num_comprobante = $request->num_comprobante;
        	$venta->fecha_hora = $my_Time->toDateString();
        	$venta->impuesto = $request->impuesto;
        	$venta->total = $request->total;
        	$venta->estado = 'Registrado';
	        $venta->save();

	        $detalles_venta = $request->data;//Array de detalles

	        foreach ($detalles_venta as $key => $detalle_venta) {
	        	
	        	$detalle = new DetalleVenta();
	        	$detalle->id_venta = $venta->id;
	        	$detalle->id_articulo = $detalle_venta['id_articulo'];
	        	$detalle->cantidad = $detalle_venta['cantidad'];
	        	$detalle->precio = $detalle_venta['precio_venta'];
	        	$detalle->descuento = $detalle_venta['descuento'];
	        	$detalle->save();

	        }

            DB::commit();

        } catch (Exception $e) {

        	DB::rollBack();
        	
        }
        
    }

    public function desactivar(Request $request)
    {
        if (!$request->ajax()) return redirect('/');

        $venta = Venta::findOrFail($request->id);
        $venta->estado = 'Anulado';
        $venta->save();
    }
}
