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
