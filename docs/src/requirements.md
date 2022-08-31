# Introduction

This document tries to describe the plattform parts for a technical realization.

# Parts

## User

Each user in the system is able part of the community. Depending on the expecience the user can do more in the platform.
The follow ing sections describe different aspects of the user.

## User Profile

The user profile contain the information of the user which are provided by the user, so that the plattform can be used completly.

### Information

 Information | Description | Self editable | Role with edit rights
-------------|-------------|---------------|-----------------------
name | First name of user | | orga, home region abassador | 
nachname | Last name of user | | orga, home region abassador |
anschrift | Address (street and house number) of user home | X | orga, home region abassador | 
plz | Zip code of user home | X | orga, home region abassador  |
stadt | City name of user home | X | orga, home region abassador |
lat | Geo-position latitude of user home | X | orga, home region abassador |
lon | Geo-position longitude of user home | X | orga, home region abassador |
telefon | phone number to contact user | X | orga, home region abassador |
handy | mobile phone number of contact user | X | orga, home region abassador |
email | E-Mail address which is used for registration and newsletter | X | orga, home region abassador |
homepage | URL to user own homepage | X | orga, home region abassador |
geschlecht | Gender of user ([@Foodsaver::Gender](https://gitlab.com/foodsharing-dev/foodsharing/-/tree/master/src/Modules/Core/DBConstants/Foodsaver/Gender.php)) | |  orga, home region abassador |
geb_datum | birthday of user  | |  orga, home region abassador |
photo | URL to user image ('/api/uploads' '/images/) Path is different depending on upload way | X | orga, home region abassador |
bezirk_id | Id of home region  | X | verified user, orga, home region abassador |
position | Free text description of position in foodsharing community (public visible) |  | orga, home region abassador |
about_me_public | User provided description text for public accessable parts. | X | orga, home region abassador |
about_me_intern | User provided description text for community internal accessable parts. | X | orga, home region abassador |
newsletter | User subscribes the newsletter | X | orga, home region abassador |
infomail_message | True if the user have subscription for info mails like chat notifications. | X | orga, home region abassador |


### Behavior

- UP-1. Each foodsharer can change own profile information, so that the user can correct the information, for example on moving to a different location, owns a new phone or e-mail address.
- UP-2. Each foodsaver can change own profile information (except of firstname, lastname, gender and birthday), so that the user can correct the information, for example on moving to a different location, owns a new phone or e-mail address. 
- UP-3. Each user in orga role can change all profile information (with name, gender, birthday), so that a correction of passport relevant information is possible.
- UP-4. Each home region ambassador of an user can change the all profile information, so that a a correction of passport relevant information is possible.

- UP-5. Other foodsharer and foodsaver of the platform can see information (except of foodsharing email, private email, handy, telefon, address, lot, lat) about the user.
- UP-6. Anonmy users can see the user first letter of firstname, first letter of lastname and a image.
- UP-7. Orga and home region ambassadors can see all information of the user.
- UP-8. Each modification in the Profile is documented in the change history, so that the user changes can be audited on problems with the user.


## Verification of user

The user need to be part of an region, this region is the home region. For an user which want using the complete platform is a verification by an ambassador required. 

- UV-1. An ambassador wants the possability to set the verification status of an user which is related to the region, so that a successful verification can be documentated and enables the user to use the platform.
- UV-2. An verified user wants to generate a passport, so that a verification in a store for pickups is possible.
- UV-3. An ambassador wants to remove the verification, so that an user with many bad reports can be blocked for use of the platform.
- UV-4. An ambassador wants to add notes for documentation of verification and reports, so that other ambassador with need to interact with this user can see the history of discussions.
- UV-5. An user which is verified is classified in the role of an "foodsaver".
- UV-5. An user which is not verified is classified in the role of an "foodsharer".

## Change user role

- UR-1. An user with orga role can change other users to orga role level.
        
        Signing an confidentiality clause with the foodsharing e.v. and the foodsharing e.v. is required. 
        ?? Is there a documentation of this done? If not, then there is no check in the technical realization and it is not part of the system.
- UR-2. An orga role user or ambassador welcome team member can set user into the ambassador role level, an successful ambassodor quiz is required before.


## Profile change history

- UPH-1. How can view this?
- UPH-2. What does the user see?