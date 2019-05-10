<?php

namespace App\Api\V1\Controllers;

use Auth;
use App\User;
use JWTAuth;
use Response;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\MiniAuthorizationRequest;
use App\Http\Requests\Api\MiniGetCodeRequest;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Exceptions\JWTException;

class MiniController extends Controller
{
    //
    public function register(MiniGetCodeRequest $request){
          
        $miniProgram = \EasyWeChat::miniProgram();
        $data = $miniProgram->auth->session($request->code);

        if (isset($data['errcode'])) {
             return response()->json([
                 'error' => 'invalid code'
              ], 401);
         }

        $user = User::where('wechat_openId', $data['openid'])->first();

        if( !$user) {
            $user = User::Create([
                'name' => 'wechat',
                'email' => $data['openid'].'@wechat.com',
                'password' => Hash::make(str_random(8)),
                'wechat_openId' => $data['openid'],
                'wechat_session_key' => $data['session_key']
              ]);
         } else {
              $user->update([
                'wechat_session_key' => $data['session_key']
            ]);
         
        }
        

         try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = Auth::guard('api')->fromUser($user)) {
                return Response::json(['error' => 'invalid_credentials'], 401);
            }

          } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return Response::json(['error' => 'could_not_create_token'], 500);
          }

        return $this->respondWithToken($token)->setStatusCode(200);

     }


    //store 
    public function store(MiniAuthorizationRequest $request){
        
        $encrypted_data = $request->encrypted_data;
        $iv = $request->iv;
        $token_user = Auth::guard('api')->getUser(); 
        $user = User::where('wechat_openId', $token_user->wechat_openId)->first();

        $miniProgram = \EasyWeChat::miniProgram(); 

        try {
          $r = $miniProgram->encryptor->decryptData($user->wechat_session_key, $iv, $encrypted_data);
        } catch (\Exception $e) {
          return resonse()->json(['error' => 'decrypt user data failed'], 500);
        }

        $user->update([
          'name'            =>  $r['nickName'],
          'wechat_gender'   =>  $r['gender'],
          'wechat_city'     =>  $r['city'],
          'wechat_province' =>  $r['province'],
          'wechat_country'  =>  $r['country'],
          'wechat_unionId'  =>  $r['unionId'] ?? NULL ,
          'avatar'          =>  $r['avatarUrl']
        ]);
      
        return response()->json(['message' => 'user profile update successfully']);
   }

    
    //update token
    public function refresh()
    {
        $token = Auth::guard('api')->refresh();
        return $this->respondWithToken($token);
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json(Auth::guard('api')->getUser());
    }

  
    // destroy token
    public function destroy()
    {
        Auth::guard('api')->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    //return standard format
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60
        ]);
    }

}
