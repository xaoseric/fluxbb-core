<?php

namespace FluxBB\Actions;

use FluxBB\Server\Request;
use Illuminate\Auth\AuthManager;

class Login extends Base
{
    /**
     * @var \Illuminate\Auth\AuthManager
     */
    protected $auth;


    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    public function run()
    {
        $credentials = [
            'username' => $this->request->get('req_username'),
            'password' => $this->request->get('req_password'),
        ];

        $remember = $this->request->get('remember');

        if (! $this->auth->attempt($credentials, $remember)) {
            $this->addError('Invalid username / password combination');
        }
    }

    protected function hasRedirect()
    {
        return true;
    }

    protected function nextRequest()
    {
        return new Request('index');
        // ->withMessage(trans('fluxbb::login.message_login'));
    }

    protected function errorRequest()
    {
        return new Request('login');
    }
}
