<?php

namespace App\Http\Controllers\User;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\ApiController;
use App\Mail\UserCreated;
use App\Transformers\UserTransformer;
use Symfony\Component\HttpFoundation\Response;

class UserController extends ApiController
{
    public function __construct() {
        $this->middleware('client.credentials')->only(['store', 'resend']);
        $this->middleware('auth:api')->except(['store', 'verify', 'resend']);
        $this->middleware('transform.input:' . UserTransformer::class)->only(['store', 'update']);
        $this->middleware('can:view,user')->only(['show']);
        $this->middleware('can:update,user')->only(['update']);
        $this->middleware('can:delete,user')->only(['destroy']);

    }

    public function index() {
        
        $this->allowedAdminAction();

        $users = User::all();
        return $this->showAll($users);
        
    }

    public function store(Request $request) {
        $rules = [
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed'
        ];

        $this->validate($request, $rules);

        $data = $request->all();
        $data['password'] = bcrypt($request->password);
        $data['verified'] = User::UNVERIFIED_USER;
        $data['verification_token'] = User::generateVerificationCode();
        $data['admin'] = User::REGULAR_USER;

        $user = User::create($data);

        return $this->showOne($user, 'Success!', Response::HTTP_CREATED);

    }

    public function show(User $user) {
        return $this->showOne($user);
    }

    public function update(Request $request, User $user) {

        $rules = [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:8|confirmed'
        ];

        $this->validate($request, $rules);

        if($request->has('name'))  {
            $user->name = $request->name;
        }

        if($request->has('email') && $user->email != $request->email) {
            $user->verified = User::UNVERIFIED_USER;
            $user->verification_token = User::generateVerificationCode();
            $user->email = $request->email;
        }

        if($request->has('password')) {
            $user->password = bcrypt($request->password);
        }

        if($request->has('admin')) {

            $this->allowedAdminAction();
            
            if($user->verified == User::UNVERIFIED_USER) {
                return $this->errorResponse('Error! Only verified users can modify the admin field', Response::HTTP_CONFLICT);
            }

            $user->admin = $request->admin;
        }

        if($user->isClean()) {
            return $this->errorResponse('You need to specify a different value to update!', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user->save();

        return $this->showOne($user);


    }
    
    public function destroy(User $user) {

        $user->delete();

        return $this->deleteResponse($user);

    }

    public function verify($token) {
        $user = User::where('verification_token', $token)->firstOrFail();

        $user->verified = User::VERIFIED_USER;
        $user->verification_token = NULL;
        $user->save();

        return $this->showMessage('The account has been verified successfully!');
    }

    public function resend(User $user) {

        if($user->verified == User::VERIFIED_USER) {
            return $this->errorResponse('This user is already verified!', Response::HTTP_CONFLICT);
        }

        Mail::to($user)->send(new UserCreated($user));

        return $this->showMessage('The verification email has been resent!');
    }
}
