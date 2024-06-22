![Wally Logo](/assets/images/logo.png)

# Wally
## A self-hosted map to collect, curate and share map markers

Named after the famous cartographer Wally B. Feed, Wally is a self-hosted PHP application that allows you to store markers on a map and categorize them, rate them or put them on your wishlist, assign icons to them and save details like text notes and pictures attached to the marker.

Markers, categories, groups and the whole map can be shared with others.


## Own your data

The philosophy of [File over App](https://stephango.com/file-over-app) says that digital artifacts should be files you can control, in formats that are easy to retrieve and read.
While data in Wally is stored in a .sqlite file, I believe the schema is still simple enough to qualify for the above.

Currently I still use the Google Maps API as a Geocoder, simply because it is so much better than [Nominatim](https://nominatim.org/). If anyone has a good, free alternative -> let me know!


### Why PHP though? Where is my Docker/NPM/Build Pipeline

Despite its bad reputation, PHP matured IMO quite a bit and - most importantly I believe - it is available on every toaster.
(I dare you to give it PHP a try when last time you played around with it was around PHP5)

**Wide Availability of Hosting**
PHP is supported by virtually all web hosting providers, making it easy to find an affordable and reliable hosting option. In the edge case you don't have one already.
What good is self-hosted software if it's only available for people who know how to properly work with Docker, Kubernetes and Postgres? And are those really needed for something small or is it just premature overengineering?

**Ease of Deployment:**
Deployment is usually just copying some files from A to B. Why make that more complicated? Wally comes with all batteries included. If you miss your 2GB mode_modules folder, reach out to me and I'll send you a folder with some random files.

**Compatibility:**
Currently I am using SQLite because it is just one of the best pieces of software ever made. It is ubiquitous, fast, and simple. But upgrading/changing to something else in the future is effortless.
Also it does not matter what webserver you use, it's an art to find some that can't do basic PHP.


## Installation

Installation is as easy as copying a handful of files and you are done.
No matter what crappy shared hosting you have lying around, it will most likely just work out of the box.
No need for layers of devops. Keep it simple.


## Usage

Simply open the folder you uploaded Wally to in your browser. I would recommend putting it on a dedicated subdomain for easier use, but you do you.
At the first start, a setup wizard is asking for a few basic infos like an admin password and possibly a Google Maps API Key


## Todos

### 1.1

- [x] Make tags clickable and show their own view
- [x] Fix some UI inconsistencies on the Tags input

- [ ] Clean up SASS/CSS code
- [ ] Unify JS Functions into wally.js
- [ ] Clean up JS Functions, remove unused code from prototyping

- [ ] Move all drawers and map actions to a state machine
- [ ] Fix refreshing of Markers on changes

### 1.2

- [ ] Make the category list expendable to show all markers in a list
- [ ] Adding a view for all markers of a given category/rating

### 1.3

- [ ] Speaking URLs (via hx-push)
- [ ] Better routing of URLs
- [ ] Support for Hotkeys

#### Later

- [ ] Lightbox for Photos
- [ ] Clustering of Markers depending on the zoom
- [ ] Guided Setup process (Categories, Ratings, ...)
- [ ] Improve Offline Capabilities (viable?)

---
