<?php

namespace Webkul\RestApi\Http\Controllers\V1\Admin\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;

class AccountController extends UserController
{
    /**
     * Get the details for current logged in user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function get(Request $request)
    {
        $admin = $request->user();

        return $admin;
    }

    /**
     * Update the details for current logged in user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $user = $request->user();

        $isPasswordChanged = false;

        $data = $request->validate([
            'name'             => 'required',
            'email'            => 'email|unique:users,email,' . $user->id,
            'password'         => 'nullable|min:6|confirmed',
            'current_password' => 'nullable|required|min:6',
        ]);

        if (! Hash::check($data['current_password'], $user->password)) {
            return response([
                'message' => __('admin::app.users.users.password-match'),
            ], 400);
        }

        if (isset($data['password'])) {
            $isPasswordChanged = true;

            $data['password'] = bcrypt($data['password']);
        }

        $user->update($data);

        if ($isPasswordChanged) {
            Event::dispatch('user.admin.update-password', $user);
        }

        return response([
            'data'    => $user,
            'message' => __('admin::app.users.users.account-save'),
        ]);
    }
}
