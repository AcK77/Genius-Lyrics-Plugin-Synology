Genius.com Lyrics Plugin for Synology Audio Station
=

How to Install
-------------
- Download **Ac_K_Genius-X.XX.aum** first.
- Open **Audio Station**, go to **Settings**, select **Lyrics Plugin** tab.
- Click on **Add** and select the downloaded plugin.
- Check the **Enabled** box in front of the added plugin.
- You're down!

Tips
-------------
- Featured Artist(s) must be in the ID3 Tag "Title" field like "Song Title Feat. Artists" or "Song Title (feat. Artists)"

How to Pack the AUM Module
-------------
- **Linux** :
> tar zcf mymodule.aum INFO lyric.php

- **Windows** :
> bsdtar zcf mymodule.aum INFO lyric.php

ChangeLog
-------------

**1.01**
- Initial Release

**1.02**
- Fix Genius Lyrics HTML Anchor
- Change the way to find the good song lyrics link

**1.03**
- Fix Genius Lyrics HTML Anchor

Credits
-------------
- Frank Lai for source code of https://bitbucket.org/franklai/synologylyric/
- https://global.download.synology.com/download/Document/DeveloperGuide/AS_Guide.pdf