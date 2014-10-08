<?php

namespace FluxBB\Server;

use Illuminate\Support\ServiceProvider as Base;
use FluxBB\Core\ActionFactory;

class ServiceProvider extends Base
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('fluxbb.server.core', function ($app) {
            $server = new Server(new ActionFactory($app));
            $this->registerActions($server);

            return $server;
        });

        $this->app->singleton('FluxBB\Server\ServerInterface', function ($app) {
            return $app->make('fluxbb.server.core');
        });

        $this->app->extend('FluxBB\Server\ServerInterface', function ($server, $app) {
            $validator = new RequestValidator($app, $server);
            $this->registerValidators($validator);

            return $validator;
        });
    }

    /**
     * Register the actions with the server.
     *
     * @param \FluxBB\Server\Server $server
     * @return void
     */
    protected function registerActions(Server $server)
    {
        $server->registerAction('index', 'FluxBB\Actions\Home');
        $server->registerAction('category', 'FluxBB\Actions\ViewCategory');
        $server->registerAction('conversation', 'FluxBB\Actions\ViewConversation');
        $server->registerAction('viewpost', 'FluxBB\Actions\ViewPost');
        $server->registerAction('register', 'FluxBB\Actions\RegisterPage');
        $server->registerAction('handle_registration', 'FluxBB\Actions\Register');
        $server->registerAction('login', 'FluxBB\Actions\LoginPage');
        $server->registerAction('handle_login', 'FluxBB\Actions\Login');
        $server->registerAction('logout', 'FluxBB\Actions\Logout');
        $server->registerAction('reset_password', 'FluxBB\Actions\PasswordResetPage');
        $server->registerAction('profile', 'FluxBB\Actions\ProfilePage');
        $server->registerAction('userlist', 'FluxBB\Actions\UsersPage');
        $server->registerAction('rules', 'FluxBB\Actions\Rules');
        $server->registerAction('search', 'FluxBB\Actions\SearchPage');
        $server->registerAction('post_edit', 'FluxBB\Actions\EditPostPage');
        $server->registerAction('post_edit_handler', 'FluxBB\Actions\EditPost');
        $server->registerAction('reply_handler', 'FluxBB\Actions\Reply');
        $server->registerAction('topic_subscribe', 'FluxBB\Actions\SubscribeTopic');
        $server->registerAction('topic_unsubscribe', 'FluxBB\Actions\UnsubscribeTopic');
        $server->registerAction('new_topic', 'FluxBB\Actions\NewTopicPage');
        $server->registerAction('new_topic_handler', 'FluxBB\Actions\NewTopic');
        $server->registerAction('admin.index', 'FluxBB\Actions\Admin\DashboardPage');
        $server->registerAction('admin.settings.global', 'FluxBB\Actions\Admin\GlobalSettingsPage');
        $server->registerAction('admin.settings.email', 'FluxBB\Actions\Admin\EmailSettingsPage');
        $server->registerAction('admin.settings.maintenance', 'FluxBB\Actions\Admin\MaintenanceSettingsPage');
        $server->registerAction('admin.dashboard.updates', 'FluxBB\Actions\Admin\UpdatesPage');
        $server->registerAction('admin.dashboard.stats', 'FluxBB\Actions\Admin\StatsPage');
        $server->registerAction('admin.dashboard.reports', 'FluxBB\Actions\Admin\ReportsPage');
        $server->registerAction('admin.options.set', 'FluxBB\Actions\Admin\SetOptions');
        $server->registerAction('admin.categories.index', 'FluxBB\Actions\Admin\CategoriesList');
    }

    /**
     * Register all validators actions with the request validator.
     *
     * @param \FluxBB\Server\RequestValidator $validator
     * @return void
     */
    protected function registerValidators(RequestValidator $validator)
    {
        $validator->registerValidator('post_edit_handler', 'FluxBB\Validator\PostValidator');
        $validator->registerValidator('reply_handler', 'FluxBB\Validator\PostValidator');
        $validator->registerValidator('new_topic_handler', 'FluxBB\Validator\PostValidator');
        $validator->registerValidator('admin.options.set', 'FluxBB\Validator\OptionsValidator');
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['FluxBB\Server\ServerInterface'];
    }
}
