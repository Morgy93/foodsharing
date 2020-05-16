# PHP

## Deprecated code structure

Since legacy code is still widespread through the repository it is important to understand it, too.

The (php) code is roughly structured with [Model - View - Controller](https://en.wikipedia.org/wiki/Model%E2%80%93view%E2%80%93controller).

The communication with the database is found in Model classes.
For example we can find `sql`-commands to manipulate a foodsaver in `/src/Modules/Foodsaver/FoodsaverModel.php`.
Those are executed with the functions inherited from the `Db` class (see `use Foodsharing\Lib\Db\Db;`, for example `$this->q(...)` where `q` stands for `query`.

## Newer code structure

Instead of model classes we move towards Gateway classes.
They inherit from `BaseGateway` (`/src/Modules/Core/BaseGateway.php`)

One main difference to models is that the functions to communicate with the database are not directly in the Gateway class by inheritance but encapsulated in the attribute `db` (`$this->db-><functioncall>`, defined in `/src/Modules/Core/Database.php`).

Often requesting information from the database uses `sql` calls via the functions at the end of the Database class, see 
 > // === methods that accept SQL statements ===

If possible, rather use the functions at the beginning that build the `sql` commands, see
 > // === high-level methods that build SQL internally ===

Those functions are well-documented in `/src/Modules/Core/Database.php`.

Currently, data is often represented differently. 
For further structuring  [Data Transfer Objects](https://en.wikipedia.org/wiki/Data_transfer_object) (DTO) can be used. An example can be found in the Bell module, currently in [merge request !1457](https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/1457). 

TODO: agree on naming conventions and add them here.

DTOs help with clearing up which parameters are expected when and what types they have. 

In addition to the above mentioned classes, Permission classes are used to organize what actions are allowed for which user.

## main entry point

The main entry point is `/index.php`.
That means that `/index.php` gets called whenever a `https://foodsharing.de`-request gets sent to the website.

## php dependencies

(Almost) no code comes without dependencies on other `php`-classes.
The dependencies are specified at the beginning of file `use`.
The following namespace, starting with `Foodsharing` gets interpreted by [Symfony](https://symfony.com/doc).
This is configured in `/config/services.yml` where `Foodsharing` gets mapped to `/src`
and in `/composer.json`.
<!-- TODO: what is configured where?, looks like the information "Foodsharing = /src" exists twice -->
<!-- TODO: mention psr4 with glossary entry and link -->
The following sub namespaces correspond to the directory structure.

`use` is not necessary for using classes in the same namespace (roughly: same directory).

Loading a depencency via [`require`](https://secure.php.net/manual/de/function.require.php) is just used in some top-level files like `index.php`.

The classes that are actually used are mentioned in constructors (`__construct`), e.g.
```
class ActivityXhr extends Control {
  private $mailboxGateway; /* attributes (member variables) */
  public function __construct(ActivityModel $model, MailboxGateway $mailboxGateway)
    { /* using arguments for setting attributes */ }
... }
```
Dependency injection (Symfony) makes sure that every time an object is used,
all necessary objects are given.
This works because we only need at most one object of every class.
(You could with `new` create further objects but we do not do that.)
Symfony config is in `/config/services.yml`.
<!-- TODO: when does [Symfony](https://symfony.com/doc) work? -->

## Syntax

Here we collect the parts of language [php](https://secure.php.net/docs.php) that are used in this project.
As I write this I already got some experiences in other languages and therefore only include short explanations of surprising syntax.

- Variables: all occurences of variables are starting with a Dollar (`$`) symbol:
```
$out = '<ul class="linklist baskets">';
```
- `=` is a definition
- `'string'` are strings. Variables inside strings are replaced by their values.
- `.` is the concatenation operator for strings (in other languages `+`)
- `.=` adds the string to the right to the variable on the left
- `$var->member` refers to a member variable of an object (in other languages `.`)
- [`$class::member`](https://secure.php.net/manual/de/language.oop5.paamayim-nekudotayim.php) refers to a (static) member variable or function of a _class_

## Conventions

- Do not use global variables (those indicated by the keyword `global`, can be found in legacy code)
- Indentation: we use one tabstop per indentation level
- Variables in strings: we do not use variables in strings but concatenate:
```
$list = 'bla'
$list .= $list . 'some text' . $someVariable . 'end text'
```
- When (re)writing php code add types in arguments and return types of functions. If you work with existing code it is not always trivial and is therefore not enforced. In general:
  -  as a parameter, something should be either the type (e.g. `string`, `int`) OR `null` if okay to not be set. `string $blub = null` works as a parameter definition to allow that.
  - as a return type, something should always ever be one type, if possible. Throw an exception otherwise. Especially an empty array `[]` is fine to say that there is no data when otherwise data would have been returned as an array.
