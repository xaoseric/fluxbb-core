<?php

namespace FluxBB\Actions;

use FluxBB\Server\Request;
use Illuminate\Auth\AuthManager;

class Logout extends Base
{
    protected $auth;


    public function __construct(AuthManager $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Run the logout action.
     *
     * @return void
     */
    protected function run()
    {
        $this->auth->logout();
    }

    protected function hasRedirect()
    {
        return true;
    }

    protected function nextRequest()
    {
        return new Request('index');
        // ->withMessage(trans('fluxbb::login.message_logout'));
    }
}
