<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Aws\SecretsManager\SecretsManagerClient;
use App\Http\Services\api\AwsService;

class AwsSecretsManagerServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('secretsmanager', function () {
            return new SecretsManagerClient([
                'version' => 'latest', // Specify the AWS SDK version you want to use
                'region'  => config('constant.AWS_DEFAULT_REGION'), // Get the AWS region from your configuration
                'credentials' => [
                    'key'    => config('constant.AWS_ACCESS_KEY_ID'), // Get the AWS access key from your configuration
                    'secret' => config('constant.AWS_SECRET_ACCESS_KEY'), // Get the AWS secret key from your configuration
                ],
            ]);
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
    }
}
