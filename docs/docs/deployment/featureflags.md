# Featureflags
Feature toggles are a technique in software engineering to control the release of new features in an application. It is a switch function that allows new features to be temporarily enabled or disabled to control the impact on the application and user experience.

They can be used to test different variations of a feature to evaluate their effectiveness or popularity with users before they are fully rolled out. They can also be used to enable certain features only for certain groups of users, such as testers or early adopters.

In addition, feature flags can also be used to react quickly to problems or malfunctions by simply deactivating the affected feature while the problem is being fixed. Feature flags are thus a powerful tool to increase flexibility, control and security in software development and deployment.

:::info Use feature flags only for features that
- has a big impact on the current use of foodsharing
- there might be data inconsistencies when releasing for beta / prod
- replace larger features (to switch back in case of emergency)
:::

## We are using feature flags, if ..
- the feature is not yet fully tested and ready for release, it can be hidden behind a feature toggle until it is fully tested and ready.

- the feature is under development, and it has not yet been finally decided whether it should be permanently available in the application, it can be hidden behind a feature toggle to see how it is used by users before it is finally activated.

- the feature is available only on several environments like local dev / beta / production

:::info Later it should be possible to (not possible yet)
- made features available to a specific group of users, it can be hidden behind a feature toggle until it is activated for that group of users like beta-tester
- a/b testing
:::

## How to create feature flags
1. navigate to `config/feature_flags.yaml`
2. add your feature flag with a meaningful identifier, short description and default value, if the flag should be active or not

As example, i added a featureflag to show the newest design for our documentation:
```yaml title='/config/feature_flags.yaml'
flagception:
  features:
    always_true_for_testing_purposes: # feature flag identifier
      default: true
      description: This feature flag is default activated for testing purposes.
      
    always_false_for_testing_purposes:
      default: false
      description: This feature flag is default disabled for testing purposes.
    
    show_newest_design_for_documentation:
      default: true
      description: activates the newest design for our documentation
```

## How to use feature flags / check if feature flag is active
Let's see how we can check in our code, if a feature flag is active.
### PHP
1. Inject via [symfony autowiring](https://symfony.com/doc/current/service_container/autowiring.html) the interface `FeatureFlagChecker (Foodsharing\Modules\Development\FeatureFlags\DependencyInjection\FeatureFlagChecker)`
2. Use the method `isFeatureFlagActive($featureFlagIdentifier)`
```php title='FeatureFlagRestController.php'
final class FeatureFlagRestController extends AbstractFOSRestController
{
    public function __construct(
        private readonly FeatureFlagChecker $featureFlagChecker,
    ) {
    }

    public function isFeatureFlagActiveAction(): JsonResponse
    {
        $isFeatureFlagActive = $this->featureFlagChecker->isFeatureFlagActive('show_newest_design_for_documentation');
        ...
    }
}
```
### VueJS

### Twig
```twig
{% if isFeatureFlagActive('show_newest_design_for_documentation') %}
    <!-- Do something -->
{% endif %}
```
