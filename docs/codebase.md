# Frequently asked questions - what you can find here
* [The architecture is not clear to me. Could you explain the basic layout?](#basic-layout)
* [How do you start?](#getting-started)
* [What are our technical constraints? (such as server, storage. memory and communication interfaces)](#technical-constraints)
* [Are there visual guidelines? (User interface, colors, buttons, etc.)](#visual-guidelines)
* [What are the security parameters? (i.e. how the website will ensure secure access control and transactions)](#security-parameters)
* [So if we break the homepage down into its parts - how is it structured?](#structure)
* [What is the relationship between karrot and foodsharing currently?](#karrot)
* [it-tasks lists a number of tech stuff we use on the page. How is their relationship to each other / what do we use them for?](#our-tech)
* [Who can I ask for help with what? Who is part of the team with which focus and which skills? (volunteer list)](#helping-hands)

# Basic layout
Q: The architecture is not clear to me. Could you explain the basic layout?

A: The main architecture goals came from this book https://leanpub.com/mlaphp "Modernizing Legacy Applications In PHP", although we deviate in some place

A few current architecture goals would be:

* only 1 php entrypoint (index.php), remove xhr|xhrapp.php/etc
* use symfony approach for handling all requests (some controllers do this already, others use the old way), in index.php the $isUsingResponse switches behaviour based on that
* create REST controllers for all API use, remove all other API stuff
* use a symfony router or similar (might also involve changing page urls to nice paths instead of get parameters + adding redirects)
* have simple/general/default kind of symfony index.phpfor templates/frontend stuff
* remove all html/js in php strings
* use vue for all dynamic kind of templates
* remove global eval stuff  ... goes with the only REST API endpoints ...
* modernize a lot more of the frontend code
* replace years long outdated flourish lib

So the preferred approach would be *Model to *Gateway classes, see here: https://gitlab.com/foodsharing-dev/foodsharing/issues/9

# Getting started
Q: I would really love to do anything, but when I look at the repo I cant't even find a thing I could change just for testing! How do you start?

A: One technique is to 

1) find some text that is clearly visible on the page, and 
2) search the codebase for it, which might point to a translation string, then 
3) search for that translation key, 
4) repeat until finding it. 

In foodsharing some of the longer content comes from the database, so you won't find that in the the codebase (except for maybe in an sql seed file), so try things more like buttons, or menu items...

# Technical constraints
Q: What are our technical constraints? (such as server, storage. memory and communication interfaces)

A: The server has 32G of memory, currently. Server stats:

* munin https://dragonfruit-admin.foodsharing.network/munin/system-week.html
* grafana https://grafana.yunity.org/?orgId=1

The current email load is high, we get spam-flagged a lot, so in the future we'll work towards sending less email. 
(E-mail handling: we have a server for .de-mails (old server), .network (new), and a third one ... more or less? Ask Matthias. :-) )
When we use a third party service, we sometimes run into problems (photon, map tiles).

# visual guidelines
Q: Are there visual guidelines? (User interface, colors, buttons, etc.)

A: There are guidelines. Basically, we use common sense.

The frontend needs rewriting as well and currently we're mostly working on & refactoring the backend.

... it could look like this: https://storybook.karrot.world/?path=/story/pickupitem--join

# security parameters
Q: What are the security parameters? (i.e. how the website will ensure secure access control and transactions)

A: There were a lot of SQL injections. Now we use prepared statements, which is clearer.
Vue helps against XSS attacks. (there might be legacy stuff lurking)

Moving (from XHR) to API made it safer as well.

We're aiming for a point, where you get only the data you're requesting (Currently a lot of code is structured "if you're this or that, you get to see xy")

# structure
Q: So if we break the homepage down into its parts - how is it structured?

A: func.php (got replaced and splitted into separate classes by Peter Tönnies working remotely during 2019 hackweek ) ... was full of random functions, that dealt with pickups, stores, etc.

Book: https://leanpub.com/mlaphp

There is also a great 1h video describing rough details about what the book is talking about: https://www.youtube.com/watch?v=65NrzJ_5j58

We still have some of the functions that bit by bit get replaced during refactoring. We hope at some point, we can take away the 'shell' and enjoy the fresh independent functions. (Without the 'you changed something, now my button is gone'-support tickets.)

# karrot
Q: What is the relationship between karrot and foodsharing currently?

A: karrot enables communities to do foodsaving - foodsharing is basically in german and has a lot of structures that some countries didn't want (thus e.g. minimizing the use of admins in the groups) ... the german-speaking organizational structures are kinda rooted in the code of foodsharing.

foodsharing is ALSO an organization.

karrot is a software project, the people who use it are their own organization, in a way.

Chandi showed how karrot and foodsharing might work together on the code base. (https://yunity.slack.com/archives/C1T1ZM9A4/p1577146381053600) For now, we're focusing on modernizing the codebase.

# our tech
Q: https://devdocs.foodsharing.network/it-tasks_EN.html lists a number of tech stuff we use on the page. How is their relationship to each other / what do we use them for? What do we want to remove from our codebase?

A: We use ...
* PHP (Symfony 4) ... we also use non-symfony-php, that we want to refactor
* JavaScript (Webpack, Vue.js) ... we're also modernizing some old JavaScript. Vue impacts mainly just one thing. CSS mostly has an effect on other stuff as well. (see below)
* HTML (Twig) ... there is also the old way with string contatination.  Twig is the new way. We're moving more towards vue.
* CSS (Bootstrap) ... move from global CSS to vue components
* MariaDB - stays! but try and get a more strict config (MariaDB is basically MySQL, but super relaxed with a lot of default values)
* Redis - stays! Very low maintenancy. It's a cache, where the session is stored.
* socket.io nodejs server - the chat server. Low maintenance.
* RESTful APIs - We also have old ones but are moving towards REST.
* the Docker Compose development environment
* Codeception for Unit-, API-, and Acceptance testing (with Selenium) - 
* Git
* GitLab CI for tests and automatic deployment (with php deployer) - this is a nice, stable setup through which multiple people can deploy stuff. (Not bottle-necking through one person.)

# helping hands
Q: Who can I ask for help with what? Who is part of the team with which focus and which skills? (volunteer list)

A: partly the answer is here: https://gitlab.com/foodsharing-dev/foodsharing/-/wikis/responsibilities

This means that there currently is no one head of the foodsharing IT. We decide with votes & vetos. Therefore currently there is no roadmap. The responsibles have lately said, that cleaning up old code and closing open Merge Requests has priority over new features. But basically if you like an idea and are willing to work on your code - you're welcome to join. :-)