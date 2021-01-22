<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization,Origin, Content-Type, X-Auth-Token, X-XSRF-TOKEN');
Route::post('login', 'API\UserController@login');
Route::post('signup', 'API\UserController@signup');
Route::post('find', 'API\UserController@request_passport');
Route::put('find/{token}', 'API\UserController@reset_password');
Route::get('user/activate/{token}', 'API\UserController@signupActivate');

Route::group(['middleware' => 'auth:api'], function () {
    Route::get('details', 'API\UserController@user');
    Route::get('usuario', 'API\UserController@index');
    Route::get('usuario-rol', 'API\UserController@usuario_rol');
    Route::get('usuario-conectado', 'API\UserController@usuarios_conectados');
    Route::delete('usuario-conectado/{id}', 'API\UserController@desconectar_usuario');


    Route::delete('usuario/{id}', 'API\UserController@delete_user');
    Route::put('usuario/{id}', 'API\UserController@cambiar_estado');


    Route::get('logout', 'API\UserController@logout');
    Route::post('buscar_usuario', 'API\UserController@buscar_usuario');
    /**@api
     * Crud Básico Rol
     * Url:setup/rol/
     */
    //Route::post('details', 'API\UserController@user');
    Route::get('empresa', 'Setup\EmpEmpresaController@empresa');
    Route::post('empresa-certificado', 'Setup\EmpEmpresaController@subir_certificado_digital');
    Route::put('empresa/{id}', 'Setup\EmpEmpresaController@actualizar_empresa');


    Route::get('rol', 'Setup\RolController@index');
    Route::post('rol', 'Setup\RolController@create');
    Route::get('rol/{id}', 'Setup\RolController@detail');
    Route::delete('rol/{id}', 'Setup\RolController@destroy');
    Route::put('rol/{id}', 'Setup\RolController@update');

    Route::get('distribuidor', 'Distribuidor\AlmDistribuidorController@listar_distribuidores');
    Route::post('distribuidor', 'Distribuidor\AlmDistribuidorController@insertar_distribuidor');

    Route::get('distribuidor/{id}', 'Distribuidor\AlmDistribuidorController@listar_distribuidor');
    Route::delete('distribuidor/{id}', 'Distribuidor\AlmDistribuidorController@eliminar_distribuidor');

    Route::put('distribuidor/{id}', 'Distribuidor\AlmDistribuidorController@editar_distribuidor');
    Route::get('distribuidor-activo', 'Distribuidor\AlmDistribuidorController@listar_distribuidores_activos');


    /**@api
     * Asigna Roles a un usuario
     * Url:setup/rol-usuario/
     * Params:{"user_id":"1","seg_rol_id":["CO15392960965741170","CO15392961296420198"]}
     */
    Route::post('rol-usuario', 'Setup\SegRolUsuarioController@asignar_roles_usuario');
    /**@api
     * Filtra el menu segun los roles y permisos de cada usuario
     * Url:api/rol-usuario/1
     */
    Route::get('rol-usuario/{userid}', 'Setup\SegRolUsuarioController@listar_roles_asignados_usuario');

    /**@api
     * Url:api/rol/modulo
     * Params:{"seg_modulo_id":["2"],"seg_rol_id":"00001C0001","Parent_seg_modulo_id":"1"}
     */
    Route::post('rol-modulo', 'Setup\SegRolModuloController@asignar_modulos_rol');
    Route::get('mudulo-padre', 'Setup\SegModuloController@listar_modulos_padre');
    /**@api
     * el filtro es por params url
     *Url:/api/rol-modulo?seg_rol_id=CO15392961296420198&seg_modulo_id=000001
     */
    Route::get('rol-modulo', 'Setup\SegRolModuloController@listar_modulo_rol');

    /**@api
     * Filtra el menu segun los roles y permisos de cada usuario
     * Url:api/menu/1
     */
    Route::get('menu', 'Setup\SegMenuController@listar_menu');


    Route::get('modulo', 'Setup\SegModuloController@listar_modulos');
    Route::post('modulo', 'Setup\SegModuloController@registrar_modulo');

    Route::get('departamento', 'Setup\ConfUbigeoController@listar_departamentos');
    Route::get('departamento/{id}', 'Setup\ConfUbigeoController@listar_provincias');
    Route::get('provincia/{id}', 'Setup\ConfUbigeoController@listar_distritos');


    Route::get('persona', 'Setup\SegPersonaController@index');
    Route::get('persona/{dni}', 'Setup\SegPersonaController@buscar_por_dni');
    Route::post('persona', 'Setup\SegPersonaController@crear_persona');
    Route::put('persona/{idPerson}', 'Setup\SegPersonaController@actualizar_persona');

    Route::post('buscar-persona', 'Setup\SegPersonaController@buscar_persona_por_nombres');


    Route::get('asiento-contable', 'Setup\ContCuentaContableController@listar_asientos_contables');
    Route::post('asiento-contable', 'Setup\ContCuentaContableController@registrar_asiento_contable');
    Route::get('asiento-contable/{id}', 'Setup\ContCuentaContableController@listar_cuenta');

    Route::put('asiento-contable/{id}', 'Setup\ContCuentaContableController@editar_cuenta');

    Route::get('asiento-contable-60', 'Setup\ContCuentaContableController@listar_cuenta_60');

    Route::get('asiento-contable-70', 'Setup\ContCuentaContableController@listar_cuenta_70');


    Route::get('periodo-activo', 'Setup\SegPeriodoController@listar_periodo_activo');


    Route::get('tipo-comprobante', 'Setup\TipoComprobanteController@listar_tipo_comprobantes');
    Route::post('tipo-comprobante', 'Setup\TipoComprobanteController@insertar_tipo_comprovante');
    Route::get('tipo-comprobante/{id}', 'Setup\TipoComprobanteController@listar_tipo_comprobante');
    Route::delete('tipo-comprobante/{id}', 'Setup\TipoComprobanteController@eliminar_tipo_comprobante');
    Route::put('tipo-comprobante/{id}', 'Setup\TipoComprobanteController@actualizar_tipo_comprobante');

    Route::get('almacen-genera-serie/{id}', 'Setup\ConfSerieController@ver_series');
    Route::put('almacen-genera-serie/{id}', 'Setup\ConfSerieController@genera_serie');
    Route::get('almacen-serie-asignado/{id}', 'Setup\ConfSerieController@ver_series_asignados_usuario');
    Route::post('almacen-serie-asignado', 'Setup\ConfSerieController@asignar_serie_usuario');
    Route::get('almacen-serie-asignado', 'Setup\ConfSerieController@ver_serie_asignado_usuario');
    Route::delete('almacen-genera-serie/{nroCajero}', 'Setup\ConfSerieController@eliminar_series');


    Route::get('tipo-pago', 'Pago\TipoPagosController@listar_tipo_pagos');

    Route::put('proveedor/{id}', 'Almacen\AlmProveedorController@actualizar_proveedor');
    Route::delete('proveedor/{id}', 'Almacen\AlmProveedorController@eliminar_proveedor');


    /*Route::get('producto', 'Almacen\AlmProductoController@listar_productos_arbol');
    Route::get('producto/{id}', 'Almacen\AlmProductoController@detalle_categoria_producto');
    Route::post('producto', 'Almacen\AlmProductoController@guardar_categoria_producto');
    Route::put('producto/{id}', 'Almacen\AlmProductoController@editar_categoria_producto');
    Route::delete('producto/{id}', 'Almacen\AlmProductoController@eliminar_producto');*/


    // Route::post('producto-buscar', 'Almacen\AlmProductoController@buscar_producto_compra');
    // Route::post('producto-buscar-venta', 'Almacen\AlmProductoController@buscar_producto_venta');

    //  Route::post('producto-buscar-venta-codigo', 'Almacen\AlmProductoController@buscar_producto_venta_codigo_barras_qr');

    Route::get('articulo', 'Almacen\AlmProductoController@productos');
    Route::get('articulo/{id}', 'Almacen\AlmProductoController@producto');
    Route::post('articulo', 'Almacen\AlmProductoController@insertar_producto');
    Route::put('articulo/{id}', 'Almacen\AlmProductoController@editar_producto');
    Route::delete('articulo/{id}', 'Almacen\AlmProductoController@eliminar_producto');
    Route::post('articulo-importar-exel', 'Almacen\AlmProductoController@importar_producto_exel');
    Route::get('articulo-autocomplete', 'Almacen\AlmProductoController@buscar_producto_autocomplete');
    Route::get('articulo-autocomplete-venta', 'Almacen\AlmProductoController@buscar_producto_autocomplete_venta');
    Route::get('articulo-autocomplete-venta-codigo', 'Almacen\AlmProductoController@buscar_producto_autocomplete_venta_codigo');
    Route::get('articulo-autocomplete-compra-codigo', 'Almacen\AlmProductoController@buscar_producto_autocomplete_codigo');


    Route::get('articulo-garantia-serie', 'Almacen\AlmProductoController@listar_gararantias_por_serie');
    Route::get('articulo-stock-almacenes', 'Almacen\AlmProductoController@listar_stock_por_almacenes');



    Route::get('articulo-stock', 'Almacen\AlmProductoController@productos_stock');
    Route::get('articulo-kardex', 'Almacen\AlmProductoController@producto_kardex');
    Route::get('articulo-kardex-todo', 'Almacen\AlmProductoController@producto_kardex_todo');
    Route::post('articulo-barras_qr', 'Almacen\AlmProductoController@generar_codigo_barras_qr');
    Route::get('articulo-serie', 'Almacen\AlmProductoController@producto_series_almacen');
    Route::get('articulo-marca', 'Almacen\AlmProductoController@listar_marcas');


    Route::get('lista-precio', 'Almacen\AlmListaPrecioController@listar_lista_precios');
    Route::get('lista-precio/{id}', 'Almacen\AlmListaPrecioController@listar_lista_precio');
    Route::post('lista-precio', 'Almacen\AlmListaPrecioController@insertar_lista_precio');
    Route::put('lista-precio/{id}', 'Almacen\AlmListaPrecioController@editar_lista_precio');
    Route::delete('lista-precio/{id}', 'Almacen\AlmListaPrecioController@eliminar_lista_precio');

    Route::get('lista-precio-detalle', 'Almacen\AlmListaPrecioDetalleController@listar_lista_precios_detalle');
    Route::post('lista-precio-detalle', 'Almacen\AlmListaPrecioDetalleController@registrar_lista_precios_detalle');


    // Route::post('productos-movimiento', 'Almacen\AlmProductoController@movimiento_producto');
    // Route::post('producto-codigo-cantidad', 'Almacen\AlmProductoController@listar_pdf_producto_codigo_cantidad');

    // Route::get('kardex/{almacenId}', 'Almacen\AlmProductoController@listar_kardex_general');
    // Route::get('cardex-exel/{almacenId}', 'Almacen\AlmProductoController@kardex_general_exel');


    // Route::get('productos/{id}', 'Almacen\AlmProductoController@listar_producto');

    //  Route::get('productos-barras-qr/{id}', 'Almacen\AlmProductoController@producto_reporte_barras_qr');

    Route::get('almacen', 'Almacen\AlmAlmacenController@listar_almacenes');
    Route::get('almacen/{id}', 'Almacen\AlmAlmacenController@listar_almacen');
    Route::post('almacen', 'Almacen\AlmAlmacenController@insertar_almacen');
    Route::delete('almacen/{id}', 'Almacen\AlmAlmacenController@eliminar_almacen');
    Route::put('almacen/{id}', 'Almacen\AlmAlmacenController@editar_almacen');
    Route::post('movimiento-almacen-salida', 'Almacen\AlmMovSalidaController@insertar_almacen_salida');
    Route::get('movimiento-almacen-salida', 'Almacen\AlmMovSalidaController@listar_almacen_salida');
    Route::post('movimiento-almacen-ingreso', 'Almacen\AlmMovIngresoController@insertar_almacen_ingreso');
    Route::get('movimiento-almacen-ingreso', 'Almacen\AlmMovIngresoController@listar_almacen_ingreso');
    Route::post('movimiento-almacen-tranferencia', 'Almacen\AlmMovIngresoController@movimiento_almacen_tranferencia');


    Route::get('unidad-medida', 'Almacen\AlmUnidadMedidaController@listar_unidades_medidas');
    Route::get('unidad-medida/{id}', 'Almacen\AlmUnidadMedidaController@listar_unidad_pedida');
    Route::post('unidad-medida', 'Almacen\AlmUnidadMedidaController@registrar_unidad_medida');
    Route::put('unidad-medida/{id}', 'Almacen\AlmUnidadMedidaController@actualizar_unidad_medida');
    Route::delete('unidad-medida/{id}', 'Almacen\AlmUnidadMedidaController@eliminar_unidad_medida');
    Route::get('unidad-medida-activo', 'Almacen\AlmUnidadMedidaController@listar_unidad_medida_activo');


    Route::get('categoria', 'Almacen\AlmCategoriaController@listar_categorias');
    Route::post('categoria', 'Almacen\AlmCategoriaController@registrar_categoria');
    Route::get('categoria/{id}', 'Almacen\AlmCategoriaController@listar_categoria');
    Route::delete('categoria/{id}', 'Almacen\AlmCategoriaController@eliminar_categoria');
    Route::put('categoria/{id}', 'Almacen\AlmCategoriaController@editar_categoria');

    Route::get('catalogo', 'Almacen\AlmCatalogoController@listar_catalogos');
    Route::get('catalogo/{id}', 'Almacen\AlmCatalogoController@listar_catalogo');
    Route::post('catalogo', 'Almacen\AlmCatalogoController@registrar_catalogo');
    Route::put('catalogo/{id}', 'Almacen\AlmCatalogoController@ractuliazar_catalogo');
    Route::get('producto-precios/{productoId}', 'Almacen\AlmCatalogoController@listar_precios_por_producto');
    Route::post('producto-precios', 'Almacen\AlmCatalogoController@registrar_precios_por_producto');


    Route::get('proveedor', 'Almacen\AlmProveedorController@listar_proveedores');
    Route::post('proveedor', 'Almacen\AlmProveedorController@registrar_proveedor');
    Route::get('proveedor-buscar/{ruc}', 'Almacen\AlmProveedorController@buscar_proveedor_por_ruc');
    Route::get('proveedor/{id}', 'Almacen\AlmProveedorController@listar_proveedor');
    Route::put('proveedor/{id}', 'Almacen\AlmProveedorController@actualizar_proveedor');
    Route::get('proveedor-autocomplete', 'Almacen\AlmProveedorController@listar_proveedor_autocomplete');

    Route::post('compra', 'Compras\CompComprasController@registrar_compra');
    Route::get('compra', 'Compras\CompComprasController@listar_compras');
    Route::get('compra/{id}', 'Compras\CompComprasController@listar_compra');
    Route::put('compra/{id}', 'Compras\CompComprasController@editar_compra');
    Route::delete('compra/{id}', 'Compras\CompComprasController@eliminar_compra');
    Route::get('compra-reporte', 'Compras\CompComprasController@reporte_compras');

    Route::post('requerimiento', 'Compras\CompRequerimientosCompraController@registrar_compra');
    Route::get('requerimiento', 'Compras\CompRequerimientosCompraController@listar_compras');
    Route::get('requerimiento/{id}', 'Compras\CompRequerimientosCompraController@listar_compra');
    Route::put('requerimiento/{id}', 'Compras\CompRequerimientosCompraController@editar_compra');
    Route::delete('requerimiento/{id}', 'Compras\CompRequerimientosCompraController@eliminar_compra');



    Route::post('recetas', 'Compras\ComRecetasController@registrar_compra');
    Route::get('recetas', 'Compras\ComRecetasController@listar_compras');
    Route::get('recetas/{id}', 'Compras\ComRecetasController@listar_compra');
    Route::put('recetas/{id}', 'Compras\ComRecetasController@editar_compra');
    Route::delete('recetas/{id}', 'Compras\ComRecetasController@eliminar_compra');


    Route::post('expedientetecnico', 'Compras\CompExpedienteTecnicoCompraController@registrar_compra');
    Route::get('expedientetecnico', 'Compras\CompExpedienteTecnicoCompraController@listar_compras');
    Route::get('expedientetecnico/{id}', 'Compras\CompExpedienteTecnicoCompraController@listar_compra');
    Route::put('expedientetecnico/{id}', 'Compras\CompExpedienteTecnicoCompraController@editar_compra');
    Route::delete('expedientetecnico/{id}', 'Compras\CompExpedienteTecnicoCompraController@eliminar_compra');
    Route::get('expedientetecnico-avance/{almacen_id}', 'Compras\CompExpedienteTecnicoCompraController@listar_expediente_tecnico_avance');


    Route::post('importacion', 'Compras\CompImportacionController@registrar_compra');
    Route::get('importacion', 'Compras\CompImportacionController@listar_compras');
    Route::get('importacion/{id}', 'Compras\CompImportacionController@listar_compra');
    Route::put('importacion/{id}', 'Compras\CompImportacionController@editar_compra');
    Route::delete('importacion/{id}', 'Compras\CompImportacionController@eliminar_compra');
    Route::get('importacion-reporte', 'Compras\CompImportacionController@reporte_compras');
    Route::post('importacion-importar-exel', 'Almacen\AlmProductoController@importar_importacion');


    // Route::get('compra-reporte', 'Compras\CompComprasController@reporte_compras');


    Route::post('cotizacion', 'Compras\CompCotizacionCompraController@registrar_compra');
    Route::get('cotizacion', 'Compras\CompCotizacionCompraController@listar_compras');
    Route::get('cotizacion-aprobados-administrador', 'Compras\CompCotizacionCompraController@listar_compras_aprobados_administrador');
    Route::get('cotizacion-aprobados-gerencia', 'Compras\CompCotizacionCompraController@listar_compras_aprobados_gerencia');


    Route::get('cotizacion/{id}', 'Compras\CompCotizacionCompraController@listar_compra');
    Route::put('cotizacion/{id}', 'Compras\CompCotizacionCompraController@editar_compra');
    Route::delete('cotizacion/{id}', 'Compras\CompCotizacionCompraController@eliminar_compra');
    Route::get('cotizacion-serie-numero-ref', 'Compras\CompCotizacionCompraController@listar_compra_serie_numero_referencia');
    Route::post('cotizacion-imagen', 'Compras\CompCotizacionCompraController@subir_imagen');
    Route::put('cotizacion-cambiar-estado/{id}', 'Compras\CompCotizacionCompraController@cambiar_estados_cotizacion');

    // Route::get('compra-reporte', 'Compras\CompComprasController@reporte_compras');


    Route::get('compra-pagar', 'Compras\CompPagoProveedoresController@listar_compras_por_pagar');
    Route::get('compra-pagar/{id}', 'Compras\CompPagoProveedoresController@listar_compra_por_pagar');
    Route::post('compra-pagar', 'Compras\CompPagoProveedoresController@registrar_pago_proveedor');


    Route::post('compra-importar-exel', 'Compras\CompComprasController@importar_compra');

    Route::post('movimiento-almacen', 'Almacen\AlmAlmacenMoviminetoController@registrar_movimiento');
    Route::get('movimiento-almacen', 'Almacen\AlmAlmacenMoviminetoController@listar_movimientos');
    Route::get('movimiento-almacen/{id}', 'Almacen\AlmAlmacenMoviminetoController@listar_movimiento');
    Route::put('movimiento-almacen/{id}', 'Almacen\AlmAlmacenMoviminetoController@editar_movimiento');
    Route::post('movimiento-almacen-importar-exel', 'Almacen\AlmAlmacenMoviminetoController@importar_ingreso');
    Route::delete('movimiento-almacen/{id}', 'Almacen\AlmAlmacenMoviminetoController@eliminar_movimineto');


    Route::get('deudas-pagar', 'Compras\CompPagoProveedoresController@listar_deudas_a_pagar_proveedores');
    Route::post('deudas-pagar', 'Compras\CompPagoProveedoresController@pagar_deudas_a_pagar_proveedores');

    Route::get('venta', 'Ventas\VentVentasController@listar_ventas');
    Route::get('venta/{id}', 'Ventas\VentVentasController@listar_venta');
    Route::post('venta', 'Ventas\VentVentasController@registrar_venta');
    Route::put('venta/{id}', 'Ventas\VentVentasController@actualizar_venta');

    Route::get('proforma', 'Proforma\ProformaController@listar_proformas');
    Route::get('proforma/{id}', 'Proforma\ProformaController@listar_proforma');
    Route::post('proforma', 'Proforma\ProformaController@registrar_proforma');
    Route::put('proforma/{id}', 'Proforma\ProformaController@actualizar_proforma');


    Route::get('venta-comision/{id}', 'Ventas\VentVentasController@listar_ventas_por_comision');
    Route::post('venta-comision', 'Ventas\VentVentasController@registrar_ventas_por_comision');

    Route::get('venta-usuario-serie', 'Ventas\VentUsuarioSerieController@listar_usuario_serie');
    Route::get('venta-buscar', 'Ventas\VentVentasController@buscar_venta_por_serie_numero');


    Route::get('venta/{fecha}', 'Ventas\VentVentasController@listar_ventas');
    Route::get('venta-reporte', 'Ventas\VentVentasController@reporte_ventas');
    Route::get('venta-impresion/{id}', 'Ventas\VentVentasController@venta_data_impresion');
    Route::get('venta-avance', 'Ventas\VentVentasController@listar_ventas_avance');
    Route::get('venta-utilidad', 'Ventas\VentVentasController@listar_venta_utilidad');


    Route::get('venta-cobrar', 'Ventas\VentCobroClientesController@listar_ventas_al_credito_sin_cancelar');

    Route::get('venta-cobrar/{id}', 'Ventas\VentCobroClientesController@listar_venta_al_credito_sin_cancelar');
    Route::get('venta-cobrar-calendario', 'Ventas\VentCobroClientesController@listar_ventas_al_credito_calendario');
    Route::post('venta-cobrar', 'Ventas\VentCobroClientesController@registrar_pago_ventas_al_credito');
    Route::put('venta-cobrar/{id}', 'Ventas\VentCobroClientesController@actualizarComentario');


    //Route::post('venta-general', 'Ventas\VentVentasController@listar_ventas_general_por_dia');
    Route::get('venta-usuario', 'Ventas\VentVentasController@listar_ventas_usario');

    Route::delete('venta/{id}', 'Ventas\VentVentasController@anular_venta');




    Route::get('venta-enviar-sunat/{idVenta}', 'Ventas\VentVentasController@venta_enviar_sunat');


    Route::post('pedido', 'Pedidos\PedPedidosController@registrar_pedido');
    Route::get('pedido', 'Pedidos\PedPedidosController@listar_pedidos');
    Route::get('pedido-cobrar', 'Pedidos\PedPedidosController@listar_pedidos_por_cobrar');
    Route::get('pedido-cobrar/{id}', 'Pedidos\PedPedidosController@listar_pedido_por_cobrar');
    Route::post('pedido-cobrar', 'Pedidos\PedPedidosController@registrar_pago_pedido_al_credito');


    Route::get('pedido/{id}', 'Pedidos\PedPedidosController@listar_pedido');
    Route::put('pedido/{id}', 'Pedidos\PedPedidosController@actualizar_pedido');
    Route::delete('pedido/{id}', 'Pedidos\PedPedidosController@eliminar_pedido');


    Route::post('pedido-pdf', 'Pedidos\PedPedidosController@documento_pedido');

    Route::post('deposito', 'Pedidos\PedPedidosController@registrar_deposito_pedido');

    Route::get('caja-movimiento', 'Caja\CajaMovimientoController@lista_caja_movimientos');
    Route::get('caja-movimiento/{id}', 'Caja\CajaMovimientoController@lista_caja_movimiento');
    Route::post('caja-movimiento', 'Caja\CajaMovimientoController@registrar_caja_movimientos');
    Route::put('caja-movimiento/{id}', 'Caja\CajaMovimientoController@editar_caja_movimiento');
    Route::delete('caja-movimiento/{id}', 'Caja\CajaMovimientoController@eliminar_caja_movimiento');

    Route::get('caja-movimiento-reporte-dia', 'Caja\CajaMovimientoController@listar_movimiento_por_fecha');
    Route::get('caja-movimiento-reporte-dia-total', 'Caja\CajaMovimientoController@listar_movimiento_por_fecha_total');

    Route::get('caja-movimiento-reporte-dia-usuario', 'Caja\CajaMovimientoController@listar_movimiento_por_fecha_usuario');
    Route::get('caja-movimiento-reporte-dia-total-usuario', 'Caja\CajaMovimientoController@listar_movimiento_por_fecha_total_usuario');

    Route::get('caja-movimiento-reporte-mes', 'Caja\CajaMovimientoController@listar_movimiento_por_mes');
    Route::get('caja-movimiento-reporte-mes-total', 'Caja\CajaMovimientoController@listar_movimiento_por_mes_total');

    Route::get('caja-movimiento-reporte-mes-usuario', 'Caja\CajaMovimientoController@listar_movimiento_por_mes_usuario');
    Route::get('caja-movimiento-reporte-mes-total-usuario', 'Caja\CajaMovimientoController@listar_movimiento_por_mes_total_usuario');

    Route::post('utilidad-mes', 'Caja\CajaMovimientoController@listar_utilidad');
    Route::post('utilidad-mes-excel', 'Caja\CajaMovimientoController@exportar_exel_utilidad_mensual');
    Route::get('buscar_por_dni/{dni}', 'Setup\SegPersonaController@buscar_persona_reniec');

    Route::get('venta-descarga-xml/{idVenta}', 'Ventas\VentVentasController@descargar_xml');
    Route::get('venta-descarga-cdr/{idVenta}', 'Ventas\VentVentasController@descargar_cdr');
    Route::get('venta-descarga-pdf/{idVenta}', 'Ventas\VentVentasController@descargar_pdf');
    Route::post('venta-fise', 'Ventas\VentVentasController@listar_ventas_con_fise_por_fecha_dia');

    Route::get('venta-avance-dia', 'Ventas\VentVentasController@avance_ventas_por_dia');
    Route::get('venta-avance-compras-pedidos-ranking', 'Ventas\VentVentasController@listar_ranking_ventas_compras_pedidos');

    Route::get('venta-avance-mes', 'Ventas\VentVentasController@avance_ventas_por_mes');
    Route::get('venta-avance-compras-pedidos-ranking-mes', 'Ventas\VentVentasController@listar_ranking_ventas_compras_pedidos_mes');
    Route::get('venta-avance-meses', 'Ventas\VentVentasController@avance_ventas_por_meses');
    Route::get('venta-avance-compras-pedidos-ranking-meses', 'Ventas\VentVentasController@listar_ranking_ventas_compras_pedidos_meses');

    Route::post('proforma-venta', 'Proforma\ProformaController@generar_venta');


    Route::get('periodo', 'Setup\SegPeriodoController@listar_periodos');
    Route::get('periodo/{id}', 'Setup\SegPeriodoController@listar_periodo');
    Route::post('periodo', 'Setup\SegPeriodoController@insertar_periodo');
    Route::delete('periodo/{id}', 'Setup\SegPeriodoController@eliminar_periodo');
    Route::put('periodo/{id}', 'Setup\SegPeriodoController@editar_periodo');

    Route::post('facturacion-elect', 'FacturacionElectronica\Controllers\procesar_data@procesar_data');
    Route::get('convertir/{id}', 'Setup\RolController@convertir_a_letras');

    Route::post('insertar-proforma', 'Proforma\ProformaController@insertar_proforma');

    Route::get('guia-remision-sunat/{id}', 'GuiaRemision\GuiaRemisionController@enviar_guia_remicion');
    Route::get('guia-remision-xml/{id}', 'GuiaRemision\GuiaRemisionController@descargar_guia_xml');
    Route::get('guia-remision-cdr/{id}', 'GuiaRemision\GuiaRemisionController@descargar_guia_cdr');

    Route::get('guia-remision/{id}', 'GuiaRemision\GuiaRemisionController@listar_guia_remision');
    Route::post('guia-remision', 'GuiaRemision\GuiaRemisionController@registrar_guia_remision');
    Route::get('guia-remision', 'GuiaRemision\GuiaRemisionController@listar_guias_remision');
    Route::put('guia-remision/{id}', 'GuiaRemision\GuiaRemisionController@actualizar_guia_remision');
    Route::delete('guia-remision/{id}', 'GuiaRemision\GuiaRemisionController@eliminar_guia_remision');

    Route::get('guia-remision-pdf/{id}', 'GuiaRemision\GuiaRemisionController@guia_remision_pdf');
    Route::get('venta-guia-remision', 'GuiaRemision\GuiaRemisionController@buscar_venta_para_guia_remision');


    Route::get('cliente', 'Ventas\VentClienteController@listar_clientes');
    Route::get('cliente/{id}', 'Ventas\VentClienteController@listar_cliente');
    Route::put('cliente/{id}', 'Ventas\VentClienteController@actualizar_cliente');
    Route::post('cliente', 'Ventas\VentClienteController@registrar_cliente');
    Route::get('cliente-buscar/{numero}', 'Ventas\VentClienteController@buscar_cliente');
    Route::delete('cliente/{id}', 'Ventas\VentClienteController@eliminar_cliente');
    Route::get('cliente-autocomplete', 'Ventas\VentClienteController@listar_cliente_autocomplete');


});

Route::post('exel-ventas-general', 'Ventas\VentVentasController@exportar_exel_lista_ventas_general');


Route::get('venta-consulta-web', 'Ventas\VentVentasController@consulta_web');
Route::get('venta-elimina-zip', 'Ventas\VentVentasController@borrar_zip');
