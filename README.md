# Add OPML to Yarns
Adds OPML import and export to the [Yarns Microsub server](https://wordpress.org/plugins/yarns-microsub-server/).

Install, activate, and visit Tools > Yarns: Import OPML to import subscriptions from an OPML file. Or to download an OPML file with your current Yarns subscriptions.

Moreover, a public OPML endpoint will be created at `wp-json/yarns-opml/v1/export`.

## Remarks
I don't think Yarns supports storing feed names and formats (RSS, Atom, mf2, etc.), yet. Because of this, the URL of [mf2 feeds](https://indieweb.org/h-feed), which are HTML, will end up in `xmlUrl` rather than `htmlUrl`. Simply can't tell them apart. (This doesn't have to be an issue, though.)

Yes, I'm using WordPress's JSON API to output **XML**. Thought it was just easier this way. (Alternatively, I _could've_ defined a new rewrite rule, and map that to a query parameter, and look for that by means of a `parse_request` callback function. And flush rewrite rules when the plugin's first activated. But I chose not to.)
