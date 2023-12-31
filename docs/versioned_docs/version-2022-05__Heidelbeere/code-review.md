# Code quality and reviews

The main goal of your contribution to the foodsharing codebase should be to make the platform great for the users.
Still, a very important aspect of that is to make the codebase great for developers as well so others can help with making it great for the users.
That is why you should have a second goal with each commit: **Make the code a little bit nicer than it was before**.
Below is a list of things that should be kept in mind when touching code but also when reviewing.
When you think there is something touched that might break one of the points listed below, better delay the approval of the merge request or ask another person for a review.

## What you should care about specifically

### If you take responsibility it's okay to break master. Please try not to break it horribly :-D 
We welcome new and beginner developers to contribute to foodsharing and understand that part of that might involve accidentally breaking bits of the site.
And that is okay, as long as they stick around to fix what they broke.
Still, try to be aware of what you are touching:
  * Do not break things that affect non-beta users
    * Email notifications generated by actions of beta users are send to everybody
    * Modification of data, especially in stores, forums and walls, affect everybody as the content is shown on beta and production
    * An accidental loss of data is the worst case

### Do not introduce security issues again
  * Never write any new code using `Foodsharing\Lib\Db` class, always use `Foodsharing\Modules\Core\Database` with prepared statements
  * When refactoring, take one step at a time. A lot of old code uses `strip_tags` as a basic Cross Site Scripting prevention method, it is hidden behind `strval`. Keep it when moving code.
  * Always be aware what type of data is held in a variable: Plain text, HTML text, markdown? The old code does mostly not do this and is not even aware of the type when outputting it to the user. Still, when you want to change that behaviour, you must be aware of every single instance of that string used over the platform (e.g. it might be stored to the database or session and retrieved at other places). If in doubt, first try to leave that behaviour exactly as you found it and refactor as a separate step

### REST API Endpoints
In the [issue #511](https://gitlab.com/foodsharing-dev/foodsharing/issues/511) some rules for creation of REST API Endpoints are formulated.
For general explanation about REST, see [request types](./requests).

1. english only
1. use "normalizer" methods to transform gateway/db data into api responses
1. **camel case** for keys (`regionId` instead of `region_id`)
1. **prefixes** for booleans (`isPublic` instead of `public`)
1. `GET` requests should never change data
1. use *Permission* classes for permission checks
1. never use *Model* classes
1. regions and working groups are both 'groups'
1. name keys always as specific as possible (`createdAt` instead of `time`,  `author` instead of `user`)
1. integers should also be send as an integer, not as a string
1. Standardize date and time: [ISO 8601](https://en.wikipedia.org/wiki/ISO_8601). Use the `DATE_ATOM` PHP DateTime formatter.
1. Add a message to exceptions. (e.g. `throw new HttpException(404, 'This region with id ' . $regionId . ' does not exist.');`)

More not-yet-implemented ideas include:
1. Add API versioning (to allow introducing breaking api changes in the future without immediately breaking the apps) ([not yet](https://gitlab.com/foodsharing-dev/foodsharing/issues/511#note_173339753), hopefully coming at some point)
1. Standardize pagination (e.g. fixed query param names, return total number of items, either via envelope or header)
1. [Automatically generated documentation](https://gitlab.com/foodsharing-dev/foodsharing/issues/511#note_173339753) for REST API
