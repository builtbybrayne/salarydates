php-truepath
============

Replace PHP's extremely buggy realpath()

All credit goes to [Christian](http://stackoverflow.com/users/314056/christian) from [this StackOverflow question](http://stackoverflow.com/questions/4049856/replace-phps-realpath). I'm just putting this up on some repos for easier access.

## Usage

	$truepath = truepath(<some possible path>)

## Notes

Unlike PHP's realpath, this function does not return false on error; it returns a path which is as far as it could to resolving these quirks.

This does not work on network resources including UNC and URLs. It works for the local file system only.