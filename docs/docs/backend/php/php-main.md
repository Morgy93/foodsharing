# Main entry point

The main entry point for the web is `/index.php`.
That means that `/index.php` gets called whenever a `https://foodsharing.de`-request gets sent to the website.

Another entry point is `xhr.php`, which is used for routes starting with `https://foodsharing.de/xhr.php`. These are
used for our legacy API (see [Xhr](../../deployment/requests#xhr)). The same applies for `xhrapp.php`.

These three entry points are now just standard Symfony entry points.
The relevant (custom) code has been moved to `src/Entrypoint`, where three respective controllers do the actual work.
For the `IndexController`, a large part of the code has been moved to `src/EventSubscriber/RenderControllerSetupSubscriber.php`.

The fourth entry point is `restApi.php`, which will be used whenever a URL starting with `https://foodsharing.de/api` is
requested. This is the route to our modern API following REST principles (see [REST-API](../api/introduction)).
It is also a standard Symfony entry point.
