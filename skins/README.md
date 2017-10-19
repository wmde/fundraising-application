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
    |        |- […]
    |- web
       |- skins
          |- mySkin [z]
             |- js [x]
             |- css [y]
             |- img
             |- […]

### Sources

The folder `/skins/mySkin/` acts as a single source of truth; all files of the skin are
kept and versioned here.

- `1` Application templates (**must be** `.twig`, consumed by PHP code)
- `2` Javascript sources (e.g. nicely structured AMD)
- `3` CSS sources (e.g. SCSS files that have to be processed)
- `4` Folder containing all assets that will be web accessible (**must exist**)
- `5` Sample asset (e.g. images. Keep them here so `/skins/mySkin/` is the single source of truth)

### Skin build process

Use a build tool of your choice (e.g. Grunt) to prepare the folder `web/` (`4`).

### Web-accessible distribution files

Only files in the `/web` folder are web-accessible.

Skin files that have to be web-accessible (e.g. CSS, in contrast to templates)
should be generated and will be recursively copied from `/skins/mySkin/web` to `/web/skins/mySkin` during the build process.
Naturally, these derived sources are not checked into version control.

- `x` Build result `2` (e.g. a minified javascript package)
- `y` Build result `3` (e.g. minified and auto-prefixed CSS)
- `z` Copy of `4` (e.g. background images that are referenced from the CSS)
