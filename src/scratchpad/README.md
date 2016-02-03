scratchpad
==========

Scratchpad is an easy way to put up a website for those that are confortable navigating the filesystem and working with a text editor.

Scratchpad can process and display the following types of files ( er..  languages rather ):

* Text or Markdown ( Just keep it simple )
* HTML and CSS ( hope to have SaSS soon ! )
* PHP and Javascript ( of course )


### Simple ( and fast ) ##

Scratchpad runs as a simple PHP script, reads files directly from the filesystem, _processes_ them if necessary to produce an HTML file.

### URLs Map To Files

Every URL passed to scratchpad will be mapped to a file in the document directory.  That document is then scanned for optional "meta data".

URLs can be mapped in the config.php, think WP permalinks.  If a URL mapper does not exist, then the url will be the actuall name of the file.  For example hello.html will be the _source_ file .html in the directory.

### Meta data can be used to associate styles

Meta data may be stored in a comment in the header of any source file.  any section of html comments surronded by: 'meta start' and 'meta end' will be processed as 'meta data'.

Currently, the only meta data I have defined are for stylesheets, just making a shortcut to call stylesheets.

<pre>
\<\!\-\- [[ stylesheet: css/custom.css ]] --\>
</pre>

### Markdown Supported

You can author pages in Markdown, how easy is that?

The file will be passed through markdown processor that will in turn spew out our final html.

## What About Processing?

The 

The _processing_ is basicall

Scratchpad will basically read a URL, process the URL and produce an HTML file.  The _processing_ can be as simple as


framework to build webpages in html.  This is for people that are comfortable using a text editor and one or more of these following "languages"

Who Is Scratchpad For?
======================

* You are building a _small_ website that won't change that much, but want it to be fast to optimize user experience.

* You are developing custom pages on WordPress but prefer to work directly with the HTML / CSS / JS / PHP in a more controlled enviornment and you want to move it over.


## Website Structure

By default ( at the simplest ) SP will create links to each *.{html,txt,md} file it finds, as well as each directory.  Hences you could easily create a website with the following strcuture:

* home.html
* about.html
* contact.html
* services/
  ** services/fix-leaky-roofs.html
  ** services/roof-inspections.html
  ** services/high-end-roofs.html
  ** services/roofs-for-normal-people.hml


The index file will create links for directories, it will scan the
directory, when it encounters files that end with .md or .txt it will
translate them as markdown files.
