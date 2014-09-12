<?php

namespace FluxBB\Actions;

use FluxBB\Actions\Exception\ValidationException;
use FluxBB\Server\Request;
use FluxBB\Server\Response\Data;
use FluxBB\Server\Response\Error;
use FluxBB\Server\Response\Redirect;
use Illuminate\Support\Contracts\ArrayableInterface;
use Illuminate\Support\Contracts\MessageProviderInterface;
use Illuminate\Support\MessageBag;

abstract class Base implements MessageProviderInterface
{
    /**
     * All data generated by this action.
     *
     * @var array
     */
    protected $data = [];

    /**
     * All errors that occurred in this action.
     *
     * @var array
     */
    protected $errors = [];

    /**
     * All event handler callbacks.
     *
     * @var array
     */
    protected $handlers = [];

    /**
     * The request that led to this action.
     *
     * @var \FluxBB\Server\Request
     */
    protected $request;

    /**
     * The request to be executed when this action finishes.
     *
     * @var \FluxBB\Server\Request
     */
    protected $nextRequest;

    /**
     * A message to be sent along with the next request.
     *
     * @var string
     */
    protected $redirectMessage = '';

    /**
     * The request to be executed in case of an error.
     *
     * @var \FluxBB\Server\Request
     */
    protected $errorRequest;


    /**
     * Turn a request into a response.
     *
     * @param \FluxBB\Server\Request $request
     * @return \FluxBB\Server\Response\Response
     * @throws \Exception
     */
    public function handle(Request $request)
    {
        try {
            $this->request = $request;
            $this->callHandlers('before');

            $this->run();

            $response = $this->makeResponse();
        } catch (ValidationException $e) {
            $response = $this->makeErrorResponse($e->getErrors());
        } catch (\Exception $e) {
            throw $e;
        }

        $this->callHandlers('after');

        return $response;
    }

    /**
     * Run any desired actions.
     *
     * @return void
     */
    abstract protected function run();

    /**
     * Create a response based on the action's status.
     *
     * @return \FluxBB\Server\Response\Response
     * @throws \Exception
     */
    protected function makeResponse()
    {
        if ($this->hasErrors()) {
            return $this->makeErrorResponse($this->getErrors());
        } else if (isset($this->nextRequest)) {
            return new Redirect($this->nextRequest, $this->redirectMessage);
        }

        return new Data($this->data);
    }

    /**
     * Create an error response for the given errors.
     *
     * @param \Illuminate\Support\MessageBag $errors
     * @return \FluxBB\Server\Response\Error
     * @throws \Exception
     */
    protected function makeErrorResponse(MessageBag $errors)
    {
        if (! isset($this->errorRequest)) {
            throw new \Exception('Cannot handle error, no handler declared.');
        }

        return new Error($this->errorRequest, $errors);
    }

    /**
     * Set another request to be executed after this action.
     *
     * @param \FluxBB\Server\Request $next
     * @param string $message
     * @return void
     */
    protected function redirectTo(Request $next, $message = '')
    {
        $this->nextRequest = $next;
        $this->redirectMessage = $message;
    }

    /**
     * Set a request to be executed in case of an error.
     *
     * @param \FluxBB\Server\Request $next
     * @return void
     */
    protected function onErrorRedirectTo(Request $next)
    {
        $this->errorRequest = $next;
    }

    /**
     * Determine whether this action yielded any data.
     *
     * @return bool
     */
    protected function hasData()
    {
        return ! empty($this->data);
    }

    /**
     * Determine whether the action encountered any errors.
     *
     * @return bool
     */
    public function hasErrors()
    {
        return ! empty($this->errors);
    }

    /**
     * Add another error message.
     *
     * @param string $error
     * @return $this
     */
    protected function addError($error)
    {
        $this->errors[] = $error;

        return $this;
    }

    /**
     * Add the given list of error messages.
     *
     * @param \Illuminate\Support\Contracts\ArrayableInterface $errors
     * @return $this
     */
    protected function mergeErrors(ArrayableInterface $errors)
    {
        foreach ($errors->toArray() as $error) {
            $this->addError($error);
        }

        return $this;
    }

    /**
     * Get all error messages gathered in this action.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getErrors()
    {
        return new MessageBag($this->errors);
    }

    public function trigger($event, $arguments = [])
    {
        \Event::fire($event, $arguments);
    }

    /**
     * Register a callback to be executed before running the action.
     *
     * @param callable $callback
     * @return $this
     */
    public function before(callable $callback)
    {
        $this->registerHandler('before', $callback);
        return $this;
    }

    /**
     * Register a callback to be executed after running the action.
     *
     * @param callable $callback
     * @return $this
     */
    public function after(callable $callback)
    {
        $this->registerHandler('after', $callback);
        return $this;
    }

    /**
     * Register a callback to be executed if the action is successfully executed.
     *
     * @param callable $callback
     * @return $this
     */
    public function onSuccess(callable $callback)
    {
        $this->registerHandler('success', $callback);
        return $this;
    }

    /**
     * Register a callback to be executed in case of an error.
     *
     * @param callable $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        $this->registerHandler('error', $callback);
        return $this;
    }

    /**
     * Register a callback for a certain type of event.
     *
     * @param string $type
     * @param callable $callback
     * @return void
     */
    protected function registerHandler($type, callable $callback)
    {
        $this->handlers[$type][] = $callback;
    }

    /**
     * Execute all handlers of the given type.
     *
     * @param string $type
     * @return void
     */
    protected function callHandlers($type)
    {
        if (isset($this->handlers[$type])) {
            $arguments = func_get_args();
            $arguments[0] = $this;

            foreach ($this->handlers[$type] as $handler) {
                call_user_func_array($handler, $arguments);
            }
        }
    }

    /**
     * Get the messages for the instance.
     *
     * @return \Illuminate\Support\MessageBag
     */
    public function getMessageBag()
    {
        return $this->getErrors();
    }
}
