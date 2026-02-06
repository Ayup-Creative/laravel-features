# Laravel Features

A simple, yet powerful feature flag system for Laravel, utilising PHP Attributes for clean and declarative feature enforcement at both object and method levels.

## Features

- **Declarative Enforcement**: Use PHP Attributes to control access to controllers and jobs.
- **Granular Control**: Apply feature gates at the class level or individual method level.
- **Queue Integration**: Automatically prevent jobs from being dispatched or executed if their associated features are disabled.
- **Console Commands**: Easily manage your feature flags via the artisan CLI.
- **Caching**: Performance-optimised with built-in cache support.
- **Flexible Actions**: Define how your application should behave when a feature is disabled (abort, skip, fail, or delay).

## Installation

You can install the package via Composer:

```bash
composer require ayup-creative/features
```

The package will automatically register its service provider and facade.

### Run Migrations

The package requires a database table to store the state of your feature flags. Run the migrations to create the `feature_flags` table:

```bash
php artisan migrate
```

### Publish Configuration (Optional)

You can publish the configuration file to customise the default state and cache settings:

```bash
php artisan vendor:publish --tag="features-config"
```

## Configuration

The published configuration file `config/features.php` allows you to define:

- `default`: The default state (true/false) for features not found in the database.
- `cache_ttl`: How long (in seconds) the feature states should be cached.

## Usage

### Basic Usage

You can check if a feature is enabled using the `Features` facade or the `features()` helper:

```php
use AyupCreative\Features\Facades\Features;

if (Features::enabled('new-dashboard')) {
    // Show the new dashboard
}

// Or using the helper
if (features()->enabled('new-dashboard')) {
    // ...
}
```

To enable or disable features programmatically:

```php
features()->enable('new-dashboard');
features()->disable('old-legacy-module');
```

### Console Commands

Manage your features directly from the terminal:

```bash
# List all features and their current status
php artisan features:list

# Enable a feature
php artisan features:enable my-feature

# Enable all features
php artisan features:enable --all

# Disable a feature
php artisan features:disable my-feature

#Disable all features
php artisan features:disable --all
```

## Attribute-based Enforcement

The true power of this package lies in its use of PHP Attributes to declaratively enforce feature gates.

### Controllers (HTTP Middleware)

To protect your routes, first register the middleware alias (if not already handled by the service provider) and apply it to your routes. The package automatically registers the `features` alias.

```php
Route::get('/experimental', [ExperimentalController::class, 'index'])
    ->middleware('features');
```

Then, use the `#[Feature]` attribute on your controller or its methods:

```php
use AyupCreative\Features\Attributes\Feature;

#[Feature('premium-stats')]
class ExperimentalController extends Controller
{
    public function index()
    {
        return view('stats.experimental');
    }

    #[Feature('beta-export')]
    public function export()
    {
        // This method requires both 'premium-stats' AND 'beta-export' to be enabled
    }
}
```

### Background Jobs (Queue)

You can prevent jobs from being dispatched or executed based on feature states.

#### Dispatch-time Enforcement
Prevent a job from even being sent to the queue:

```php
use AyupCreative\Features\Attributes\DispatchWhenFeatureEnabled;

#[DispatchWhenFeatureEnabled('email-notifications')]
class SendNotificationJob implements ShouldQueue
{
    // ...
}
```

#### Execution-time Enforcement
Prevent a job from running when it is processed by a worker:

```php
use AyupCreative\Features\Attributes\Feature;
use AyupCreative\Features\Attributes\Semantic\DelayWhenFeatureDisabled;

#[Feature('data-processing')]
#[DelayWhenFeatureDisabled]
class ProcessDataJob implements ShouldQueue
{
    // ...
}
```

## Available Attributes

### Core Attributes

- `#[Feature('name')]`: Requires the specified feature to be enabled.
- `#[FeatureAll(['feat1', 'feat2'])]`: Requires **all** listed features to be enabled.
- `#[FeatureAny(['feat1', 'feat2'])]`: Requires **at least one** of the listed features to be enabled.
- `#[WhenFeatureDisabled(action: 'abort')]`: Defines the behaviour when a feature is disabled. 
    - Actions for HTTP: `abort` (returns 404).
    - Actions for Jobs: `skip` (ignores job), `fail` (fails job), `delay` (releases job back to queue).

### Semantic Attributes

For cleaner code, you can use these semantic wrappers for `WhenFeatureDisabled`:

- `#[SkipWhenFeatureDisabled]`: Equivalent to `action: 'skip'`.
- `#[FailWhenFeatureDisabled]`: Equivalent to `action: 'fail'`.
- `#[DelayWhenFeatureDisabled]`: Equivalent to `action: 'delay'`.

### Metadata Attributes

These attributes are available for documentation and can be accessed via reflection, though they are not enforced by the default evaluator:

- `#[FeatureDeprecated(key: 'old-feat', removeBy: '2024-12-31')]`
- `#[LimitFeatureUsage(max: 100, perSeconds: 3600)]`
- `#[FeatureContext(context: 'user')]`

## Testing

The package includes a comprehensive test suite. To run the tests, use:

```bash
vendor/bin/phpunit
```

You can also run the tests via compsoer using:

```bash
composer test
```

## Contributing

If you wish to contribute to this project, please feel free to submit a pull request or open an issue on the [GitHub repository](https://github.com/ayup-creative/laravel-features). We welcome contributions that improve the codebase, add new features, or enhance the documentation.

## Credits

Developed by Ayup Creative.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
