# Add OPML to Yarns
So far, adds OPML **export** to the [Yarns Microsub server](https://wordpress.org/plugins/yarns-microsub-server/).

Install, activate, and visit `wp-json/yarns-opml/v1/export`.

## Remarks
I don't think Yarns supports feed names and formats (RSS, Atom, mf2, etc.), yet. I'm simply using the feed URL for everything.

Also, because of this, the URL of mf2 feeds, which are HTML, will end up in `xmlUrl` rather than `htmlUrl`. Simply can't tell them apart. (This doesn't have to be an issue, though. Most feed readers wouldn't know how to handle mf2 feeds anyway.)

Yes, I'm using WordPress's JSON API to output **XML**. Thought it was just easier this way. (Alternatively, I _could've_ defined a new rewrite rule, and map that to a query parameter, and look for that by means of a `parse_request` callback function. And flush rewrite rules when the plugin's first activated. But I chose not to.)
