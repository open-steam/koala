<?php
defined("WIKI_RSS") or define("WIKI_RSS", true);
defined("WIKI_FULL_HEADLINE") or define("WIKI_FULL_HEADLINE", false); // only works when wiki object is in group/course structure (will result in warnings/errors otherwise)
defined("MANAGE_GROUPS_MEMBERSHIP") or define("MANAGE_GROUPS_MEMBERSHIP", true);
defined("WIKI_MEDIATHEK") or define("WIKI_MEDIATHEK", true);
defined("WIKI_EDIT") or define("WIKI_EDIT", true);
defined("WIKI_DELETE") or define("WIKI_DELETE", true);
defined("WIKI_EXPORT") or define("WIKI_EXPORT", false); // works only in koala
defined("WIKI_WYSIWYG") or define("WIKI_WYSIWYG", true);
defined("ENABLED_SEARCH") or define ("ENABLED_SEARCH", false); // has to be false; does not work (yet)
defined("WIKI_SEARCH_ENABLED") or define("WIKI_SEARCH_ENABLED", false); // has to be false; does not work (yet)
defined("WIKI_SHOW_AUTHOR_TO_READER") or define("WIKI_SHOW_AUTHOR_TO_READER", true);
?>