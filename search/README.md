Search plugin 0.6.7
===================
Full-text search. [See demo](https://developers.datenstrom.se/search/).

<p align="center"><img src="search-screenshot.png?raw=true" alt="Screenshot"></p>

## How do I install this?

1. [Download and install Yellow](https://github.com/datenstrom/yellow/).
2. [Download plugin](https://github.com/datenstrom/yellow-plugins/raw/master/zip/search.zip). If you are using Safari, right click and select 'Download file as'.
3. Copy `search.zip` into your `system/plugins` folder.

To uninstall delete the plugin files.

## How to use a search?

The search is available on your website as `http://website/search/`. It searches trough content of the entire website, only visible pages are included. You can use a [custom navigation](https://developers.datenstrom.se/help/yellow-templates#custom-navigation), open file `system/config/config.ini` and change `Navigation: navigation-search`. To show a search field add a `[search]` shortcut to a page. You can also add a link to the search somewhere on your website. See example below.

## How to configure a search?

The following settings can be configured in file `system/config/config.ini`:

`SearchLocation` = search location  
`SearchPaginationLimit` = number of entries to show per page  
`SearchPageLength` = maximum page length to show  

## Example

Footer snippet with search page:

    <div class="footer">
    <div class="siteinfo">
    <a href="<?php echo $yellow->page->base."/" ?>">&copy; 2016 <?php echo $yellow->page->getHtml("sitename") ?></a>.
    <a href="<?php echo $yellow->page->base."/search/" ?>">Search</a>.
    <a href="<?php echo $yellow->page->get("pageEdit") ?>">Edit</a>.
    <a href="<?php echo $yellow->text->get("yellowUrl") ?>">Made with Yellow</a>.
    </div>
    </div>
    </div>
    <?php echo $yellow->page->getExtra("footer") ?>
    </body>
    </html>

## Developer

Datenstrom
