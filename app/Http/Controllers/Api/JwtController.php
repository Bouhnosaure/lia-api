<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\User;
use Exception;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\SigninRequest;
use App\Http\Requests\SignupRequest;
use App\Http\Transformers\JwtTransformer;


class JwtController extends Controller
{
    public function signup(SignupRequest $request)
    {
        $credentials = $request->only('name', 'email', 'password');
        $credentials['password'] = bcrypt($credentials['password']);

        try {
            if (User::where('email', '=', $credentials['email'])->count() < 1) {
                $user = User::create($credentials);
            } else {
                return $this->response->withError('User already exists.', 409);
            }
        } catch (Exception $e) {
            return $this->response->withError('User already exists.', 409);
        }

        $token = JWTAuth::fromUser($user);

        return $this->response->array(['token' => $token, 'user' => $user->toArray()]);
    }

    /**
     * @param SigninRequest $request
     * @return mixed
     */
    public function signin(SigninRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {

            return $this->response->withError('Combinaison email / mot de passe érronée', 401);
        }

        $user = Auth::user();

        // if no errors are encountered we can return a JWT
        return $this->response->array(['token' => $token, 'user' => $user->toArray()]);
    }

    public function refresh()
    {
        $oldtoken = JWTAuth::getToken();
        try {
            // attempt to refresh token for the user
            if (!$token = JWTAuth::refresh($oldtoken)) {
                return $this->response->array(['error' => 'invalid_token'], 401);
            }
        } catch (JWTException $e) {
            return $this->response->array(['error' => 'could_not_refresh_token'], 500);
        }
        return $this->response->array(compact('token'));
    }
}