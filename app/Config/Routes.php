<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');
$routes->get('splashscreen', 'Splashscreen::getSplashscreen');
$routes->get('home', 'Home::home');

$routes->get('signout', 'Auth::signOut');
$routes->post('signin', 'Auth::signIn');
$routes->post('signup', 'Auth::signUp');
$routes->post('fcmregid', 'Auth::fcmRegId');
$routes->get('pushnotif', 'Pushnotif::index');

$routes->get('kategori', 'Kategori::index');

$routes->get('produk/(:segment)/(:segment)', 'Produk::detail/$1/$2');
$routes->get('produk/topprodukumkmdatastore', 'Produk::topProdukUmkmDataStore');
$routes->get('produk/produkdatastore', 'Produk::produkDataStore');
$routes->get('produk/produkumkmdatastore', 'Produk::produkDataStoreUmkm');
$routes->get('produk/produkkategoridatastore', 'Produk::produkKategoriDataStore');
$routes->get('produk/produkindukkategoridatastore', 'Produk::produkIndukKategoriDataStore');
$routes->get('produk/produkterkaitdatastore', 'Produk::produkTerkaitDataStore');

$routes->get('member', 'Member::index');
$routes->get('member/(:segment)/load', 'Member::load/$1');
$routes->get('member/(:segment)/delete', 'Member::delete/$1');
$routes->add('member/insert', 'Member::insert');
$routes->add('member/(:segment)/update', 'Member::update/$1');

$routes->get('mitra', 'Mitra::index');
$routes->get('mitra/pilihandatamitra', 'Mitra::pilihanDataMitra');
$routes->get('mitra/(:segment)/load', 'Mitra::load/$1');
$routes->get('mitra/(:segment)/delete', 'Mitra::delete/$1');
$routes->add('mitra/insert', 'Mitra::insert');
$routes->add('mitra/(:segment)/update', 'Mitra::update/$1');

$routes->get('cart/addproduktocart', 'Cart::addProdukToCart');
$routes->get('cart/cartheaderdatastore', 'Cart::getCartHeaderDataStore');
$routes->get('cart/cartdatastore', 'Cart::getCartDataStore');
$routes->add('cart/deletefromcart', 'Cart::deleteFromCart');

$routes->get('order', 'Order::index');
$routes->get('order/detailorder', 'Order::getDetailOrder');
$routes->get('order/cancelorder', 'Order::cancelOrder');

$routes->get('wishlist', 'Wishlist::index');
$routes->add('wishlist/insert', 'Wishlist::insert');
$routes->add('wishlist/delete', 'Wishlist::delete');

$routes->get('konfirmasibayar', 'KonfirmasiBayar::index');
$routes->get('youtube', 'Youtube::index');

$routes->get('informasi', 'Informasi::index');


/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
