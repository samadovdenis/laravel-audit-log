<?php

namespace Jeylabs\AuditLog;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Jeylabs\AuditLog\Models\AuditLog;
use Jeylabs\AuditLog\Exceptions\InvalidConfiguration;

class AuditLogServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/laravel-audit-log.php' => config_path('laravel-audit-log.php'),
        ], 'config');

        $this->mergeConfigFrom(__DIR__.'/../config/laravel-audit-log.php', 'laravel-audit-log');

        if (! class_exists('CreateAuditLogTable')) {
            $timestamp = date('Y_m_d_His', time());

            $this->publishes([
                __DIR__.'/../migrations/create_audit_log_table.php' => database_path("/migrations/{$timestamp}_create_audit_log_table.php"),
            ], 'migrations');
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->bind('command.auditlog:clean', CleanAuditLogCommand::class);

        $this->commands([
            'command.auditlog:clean',
        ]);
    }

    public static function determineAuditLogModel(): string
    {
        $activityModel = config('laravel-audit-log.activity_model') ?? AuditLog::class;

        if (! is_a($activityModel, AuditLog::class, true)) {
            throw InvalidConfiguration::modelIsNotValid($activityModel);
        }

        return $activityModel;
    }

    public static function getActivityModelInstance(): Model
    {
        $activityModelClassName = self::determineAuditLogModel();

        return new $activityModelClassName();
    }
}