# Roles and Permissions

After setting up a local foodsharing instance, you will get to know many workflows and actions
that are not available to you on foodsharing itself, due to limited roles and permissions.

With access to both the seed data and the database, those limitations no longer exist!
Go and play around with some of the local users, or keep reading for a brief overview and explanation.

## Roles

Users have a "quiz role" which signifies the level to which they can help manage the foodsharing platform.
The most prominent promotion steps are
- from Foodsharer to Foodsaver (the "Foodsaver-Quiz"), which eventually allows you to pick up food in cooperating stores
- from Foodsaver to Store Manager (the "Betriebsverantwortlichen-Quiz"), which enables you to become responsible for cooperations
- from Store Manager to Ambassador (the "Botschafter\*innen-Quiz"), which enables you to become ambassador for regions (districts)

In addition to the above, there are also a few select users with global Orga permissions.

### Foodsharer

A Foodsharer can
- offer food baskets food baskets on the platform or pick them up
- post information to foodsharepoints (e.g. about current content)
- join or participate in events
- see some personal data for other users (pickup count, forum posts, public info, regions & workgroups, wall)
- edit their own profile and optionally include their address
- chat with other users and become buddies with them (*Ich kenne*)
- receive trust bananas, but not give them
- receive newsletter mail
- remove their own account

One becomes Foodsharer simply by creating an account on foodsharing.de or foodsharing.at or ... (depending upon country).
A foodsharer can not view or interact with stores.
They also cannot join a region, so interacting with the forums is not possible.

### Foodsaver

A Foodsaver can

- join and leave regions (the nightly script makes sure to also add them to parent regions)
- see stores and apply for membership in a store team, if the team is looking for help
- sign up to fetch food from stores with empty slots, and sign themself out of future pickup slots
- create events in regions or workgroups they are part of
- apply for workgroups and participate  
  (in theory they could even administrate the group without having the Store Manager Quiz)
- view and write in the forum, react to forum posts (large region forums on our platform are moderated)
- generate their personal business card (once verified), not to be confused with their passport
- be asked to manage any foodsharepoint in regions which they are part of
- give and receive trust bananas
- do a lot more that's not listed here :)

Foodsavers have:
1. passed the quiz for Foodsaver
2. signed the legal agreement
3. correct data (which is verified by the BOT via foodsaver ID)
4. had some introductory pick-ups (once verified)
5. a foodsharing passport (once verified, or soon after)

### Store Manager

- can be asked to manage any stores in regions which they are part of
- has accepted the privacy notice regarding sensitive user data (*Vertraulichkeitserklärung*)

The following assumes the user is store manager for at least one store, although you can
of course have passed the Store Manager Quiz and not be managing any store at the moment.

- is displayed at the top of "their" store-team list, for people to contact
- can add and remove store team members, can accept and reject store-team applications
- can move people to and from waiting list, chat with waiting list
- can edit the store properties, team status and what's displayed on the foodsharing map
- can edit the pickup times (repeated ones and one-time pickups)
- can confirm and reject pickup slots
- can see the pickup history (past slots)

### Ambassador

The following assumes the user is ambassador for at least one region, although you can
of course have passed the Ambassador Quiz and not be acting Ambassador at the moment.

- can manage the profiles of foodsharers and foodsavers with that region as home region (*Stammbezirk*)
- can manage the foodsaver passports of people from that region
- are displayed in the left sidebar of "their" region's forums, for people to contact
- can perform store manager actions on any stores in that region
- can manage any local workgroup in the region

### Orgateam
- basically allowed to see and do anything, but remember: with great power comes great responsibility ;-)
- just browse the pages in the gear-icon menu entry and discover e.g. the internal content management system

## Permissions

There are many different things you might manage, for example...
- a workgroup
- a store
- an event
- a foodsharepoint
- a region
- a foodsaver profile
- a newsletter
- a poll
- a foodsaver passport (*Ausweis*)
- a blogpost
- a food basket
- content in the internal CMS
- quiz questions

In each of those situations (and there are more!) you'll be allowed or not allowed to perform actions
based on the permissions for this context.
Hopefully over time, we can briefly outline what's what and which things to look out for!

Many of the computations have already been extracted, and you can find them as part of a
[Permissions class](../backend/php/php-transactions#permissions) for the respective module.
In several other cases though, the checks for what you are and aren't allowed to do are implicit,
meaning they are implemented inline throughout the code.
We're trying to extract them wherever possible when working on code nearby,
and only introduce "clean" permission handling in new code.

Recently, we have also started introducing special workgroups which can take over certain predefined duties
that previously belonged to ambassadors only (or did not exist separately at all).

This has been done with the project GOALS (goals@foodsharing.network).

It is possible to assign a workgroup function that grants certain administrative optimised
to the function task. The workgroup function affect the district, that the workgroup is appended to. 

| workgroup-Function   | description                                                                                                              |
|----------------------|--------------------------------------------------------------------------------------------------------------------------|
| Welcome              | administrators get a bell about entries of foodsavers into the district                                                  |
| FSP (foodsharepoint) | managing foodshare points of this region (regardless of who's listed as responsible for the point)                       |
| Store Coordination   | managing all stores from this region. If a store coordination group is set up it is prioritized over ambassadors         |
| Report               | managing reports for a region. Technical support for making reports                                                      |
| Mediation            | e-mail address is shown if requesting a mediation                                                                        |
| Arbitration          | managing reports with reports of report team                                                                             |
| FSManagement         | managing foodsaver in a region (work in progress)                                                                        |
| PR                   | no special functionality                                                                                                 |
| Moderation           | managing moderation of a district (activate threads). If a moderation group is set up it is prioritized over ambassadors |

Those groups are optional and are set up by writing to the "AG Creation" group.
They do show up in the forum sidebar, similar to the ambassador list.
