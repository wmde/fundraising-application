Skins that the application can be used with.

## Folder structure

This is an example, how a skin's files could be organized.

    FundraisingFrontend
    |- […]
    |- skins
    |  |- mySkin
    |     |- templates [1]
    |     |- js [2]
    |     |- scss [3]
    |     |- web [4]
    |        |- img [5]
    |        |- js [w]
    |        |- css [x]
    |- web
       |- skins
          |- mySkin [z]

### Sources

The folder `/skins/mySkin/` acts as a single source of truth; all files of the skin are
kept and versioned here.

- `1` Application templates (**must be** `.twig`, consumed by PHP code)
- `2` Javascript sources (e.g. nicely structured AMD)
- `3` CSS sources (e.g. SCSS files that have to be processed)
- `4` Folder containing all files that will be web accessible (**must exist**)
- `5` Sample asset that does not need build process (e.g. images. Keep them here so `/skins/mySkin/` is the single source of truth)
- `w` Your build result of `2` (e.g. a minified javascript package)
- `x` Your build result of `3` (e.g. minified and auto-prefixed CSS)

### Skin build process

Use a build tool of your choice (e.g. Grunt) to prepare the folder `web/` (`4`).

The application build tool will invoke the following build targets, which **must exist**, on each skin

    npm install
    npm run cs
    npm run test
    npm run ci
    npm run build-assets

### Web-accessible distribution files

Only files in the application's `/web` folder are web-accessible.

Skin files that have to be web-accessible (e.g. CSS, in contrast to templates)
should be generated and will be recursively copied from `/skins/mySkin/web` to `/web/skins/mySkin` during the build process.
Naturally, these derived sources are not checked into version control.

In case your skin provides development features like file watching, instead of using the `copy-assets` command 
from the application build process (which does a hard copy into the `/web` folder), you could create a symlink that
maps `/skins/mySkin/web` (`4`) to `/web/skins/mySkin` (`z`).
