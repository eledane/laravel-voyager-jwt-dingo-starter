<?php

use Illuminate\Http\Request;

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
Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


/** @var Router $api */
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', ['prefix' => 'api/v1', 'namespace' => 'App\Api\V1\Controllers'], function ($api) {
  
    // test url
    $api->get('test', function(){
      return 'it is a test';
      });

    // lesson url
    $api->get('lesson', 'LessonsController@index');

    // wechat mini program related
    $api->group([
        'middleware' => 'api.throttle',
        'limit' => config('api.rate_limits.sign.limit'),
        'expires' => config('api.rate_limits.sign.expires'),
    ], function ($api) {
        
         // register or get token
        $api->post('mini/auth/register', 'MinisController@register');
        
        $api->group(['middleware' => 'auth:api'], function($api){
         
            // login
            $api->post('mini/auth', 'MinisController@Store');

            // me 
            $api->post('mini/me', 'MinisController@me');
 
            // refresh token
            $api->put('mini/refresh', 'MinisController@refresh');
        
            // delete token
            $api->delete('mini/destroy', 'MinisController@destroy');

        });
        
    }); //end of wechat mini

});
