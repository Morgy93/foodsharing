# Authorization / Authentication

## Why do we need Auth?

In order to prevent unwanted access and to secure your data. Authorization is required.

## How does the Auth work?

The authorization for all API calls is managed with cookies.
On login the Backend creates a session (which can be identified with an ID, the so called PHPSESSID). Additionally a unique token, the CSRF_TOKEN, will be created.
The csrf-token is used to identify the client for the specific phpsession.
These two values will be provided as a cookies.

On Every Request the client does, both cookies will be sent with the request, so that the Backend can verify the request came from the correct client, and that the client is verified to do specific calls.
To do so the Backend will try to find a session with the provided PHPSESSID and then verifiy that the CSRF_TOKEN of this session matches the token provided by the user.

## Auth Lifetime

Both cookies will be provided with an 'Expires' flagg.
By default this will be set to 'Session' which means as soon as the user closes the browser these cookies will be deleted. So the user is no longer logged in.

On Login the user has the option to select the option 'Stay logged in for 1 day'. If this option is selected, the 'Expires' flagg of both tokens will be the a DateTimeStamp in 24 hours. These Cookies will stay in the browser storage even if the browser is closed, until the DateTimeStamp is reached.

## Logout

On Logout the current PHPSESSID token will be deleted from the browserstorage.

So all requests that will be done afterwards return a 403 Forbidden
