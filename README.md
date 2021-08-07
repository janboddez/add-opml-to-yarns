# Add OPML to Yarns
Add limited OPML support to the Yarns Microsub server.

So far, adds OPML **export** to Yarns.

Install, activate, and visit https://example.org/wp-json/yarns-opml/v1/export.

## Remarks
I don't think Yarns supports feed names and formats (RSS, Atom, mf2, etc.), yet. I'm simply using the feed URL for everything. Also, because of this, the URL of mf2 feeds, which are HTML, will end up in `xmlUrl` rather than `htmlUrl`. Simply can't tell them apart.

Yes, I'm using WordPress's JSON API to output XML. Thought it was just easier this way.
