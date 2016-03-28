<?php

namespace Jrean\UserVerification\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Jrean\UserVerification\Facades\UserVerification;
use Jrean\UserVerification\Exceptions\UserNotFoundException;
use Jrean\UserVerification\Exceptions\UserIsVerifiedException;

trait VerifiesUsers
{
    use RedirectsUsers;

    /**
     * Handle the user verification.
     *
     * @param  string  $token
     * @return Response
     */
    public function getVerification(Request $request, $token)
    {
        $this->validateRequest($request);

        try {
            UserVerification::process($request->input('email'), $token, $this->userTable());
        } catch (UserNotFoundException $e) {
            return redirect($this->redirectIfVerificationFails());
        } catch (UserIsVerifiedException $e) {
            return redirect($this->redirectIfVerified());
        }

        return redirect($this->redirectAfterVerification());
    }

    /**
     * Show the verification error view.
     *
     * @return Response
     */
    public function getVerificationError()
    {
        return view($this->verificationErrorView());
    }

    /**
     * Validate the verification link.
     *
     * @param  string  $token
     * @return Response
     */
    protected function validateRequest(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return redirect($this->redirectIfVerificationFails());
        }
    }

    /**
     * Get the verification error view name.
     *
     * @return string
     */
    protected function verificationErrorView()
    {
        return property_exists($this, 'verificationErrorView') ? $this->verificationErrorView : 'errors.user-verification';
    }

    /**
     * Get the user table name.
     *
     * @return string
     */
    protected function userTable()
    {
        return property_exists($this, 'userTable') ? $this->userTable : 'users';
    }
}