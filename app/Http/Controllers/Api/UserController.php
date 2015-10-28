<?php

namespace App\Http\Controllers\Api;

use App\Equipment;
use App\Establishment;
use App\Http\Controllers\Controller;
use App\Http\Requests;
use App\Http\Requests\UserRequest;
use App\Http\Transformers\EquipmentTransformer;
use App\Http\Transformers\EstablishmentTransformer;
use App\Http\Transformers\UserTransformer;
use App\Role;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $users = User::all();
        return $this->response->collection($users, new UserTransformer);
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param UserRequest|Request $request
     * @return Response
     */
    public function store(UserRequest $request)
    {

        $data = $request->all();

        if (isset($data['password'])) {
            $data['password'] = bcrypt($data['password']);
        }

        if ($user = User::create($data)) {
            return $this->response->item($user, new UserTransformer);
        } else {
            return $this->response->errorInternal();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->response->item($user, new UserTransformer);
    }

    /**
     * Update the specified resource in storage.
     * @param UserRequest|Request $request
     * @param  int $id
     * @return Response
     */
    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        $data = $request->all();

        if ($data['password'] != "" && $data['password'] != null) {
            $data['password'] = bcrypt($data['password']);
        } else {
            array_forget($data, 'password');
        }

        if ($user->update($data)) {
            return $this->response->noContent();
        } else {
            return $this->response->errorInternal();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return $this->response->deleted();
    }

    /**
     * Get the current authenticated user
     *
     * @return array
     */
    public function authenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return $this->response->array(['user_not_found'], 404);
            }
        } catch (TokenExpiredException $e) {
            return $this->response->array(['token_expired'], $e->getStatusCode());
        } catch (TokenInvalidException $e) {
            return $this->response->array(['token_invalid'], $e->getStatusCode());
        } catch (JWTException $e) {
            return $this->response->array(['token_absent'], $e->getStatusCode());
        }
        return $this->response->array(compact('user'));
    }


    /**
     * @return mixed
     */
    public function getSettings()
    {
        $user = JWTAuth::parseToken()->authenticate();
        return $this->response->array(unserialize($user->settings));
    }

    /**
     * @return mixed
     */
    public function setSettings()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $settings = unserialize($user->settings);

        foreach (Input::all() as $key => $setting) {
            $settings[$key] = $setting;
        }

        $user->settings = serialize($settings);
        $user->save();

        return $this->response->created();
    }
}
