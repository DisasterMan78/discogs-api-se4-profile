# discogs-api-se4-profile
Add users' Discogs.com releases to Social Engine 4 public profiles

A very basic script which integrates via a few steps in Social Engine 4 admin.

##Upload

Add discogs.css, discogs.js and discogs.php files to webroot of the Social Engine 4 installation

##GMC Admin

###Settings > Profile Questions

Select first profile type to have the Discogs releases

Heading name is not optional, and ordering and name are important for the CSS to hide the question on the profile page, and for the Javascript to find the hidden ID to build the API request.

> Add Heading

- Heading name : Integration
- Show on browse members page? : Hide on Browse Members
- Show on member profiles? : Hide on Member Profiles
[Add Heading]


> Add Question

- Question Type : Integer
- Question Label : What is your discogs artist ID?
- Description :
```
If you have a discogs.com account, we can show the releases you have listed on discogs on your profile page.

To work out your artist ID, take a look at your discogs release page.

E.g:
http://www.discogs.com/artist/123456-Your-Name

Your artist ID would be 123456
- Required? Not Required
- Show on browse members page? : Hide on Browse Members
- Show on member profiles? : Show on Member Profiles
- Show on Signup/Creation? : Show on Signup/Creation
```
> Save Question


For each additional profile type you can 'Duplicate existing' after clicking Add Question. This works for headings too.

Ensure question and heading are ordered correctly (last two items, heading first)

###Layout > Layout Editor

Editing dropdown > Member Profile

Drag [HTML Block] from Available Blocks to bottom of Tab Container

Edit the block

```html
<link href="/discogs.css" rel="stylesheet">
<h2>Releases on Discogs</h2>
<div id="discogs"></div>
<script>
	if (typeof jQuery == 'undefined') {
		document.write(unescape("%3Cscript src='/application/modules/Photoviewer/externals/scripts/jquery-1.9.0.min.js'%3E%3C/script%3E"));
	}
	jQuery.noConflict();
</script>
<script src="/discogs.js"></script>
```

> Save Changes
