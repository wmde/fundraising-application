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
    |     |- resources
    |        |- img [4]
    |        |- […]
    |- web
       |- skins
          |- mySkin
             |- js [x]
             |- css [y]
             |- img [z]

### Sources

The folder `/skins/mySkin/` acts as a single source of truth; all files of the skin are
kept and versioned here.

- `1` Application templates (e.g. `.twig`)
- `2` Javascript sources (e.g. nicely structured AMD)
- `3` CSS sources (e.g. SCSS files that have to be processes)
- `4` Resources / Assets (e.g. images. Keep them here so skins/mySkin/ is the single source of truth)

### Web-accessible distribution files

Only files in the `/web` folder are web-accessible.
Skin files that have to be web-accessible (e.g. CSS, in contras to e.g. the templates)
should be generated and explicitly copied to the `/web/skins/mySkin` folder
during the build process, using a build tool of your choice (e.g. Grunt).
Naturally, these derived sources should not be checked into version control.

- `x` Build result `2` (e.g. a minified javascript package)
- `y` Build result `3` (e.g. minified and auto-prefixed CSS)
- `z` Copy of `4` (e.g. background images that are referenced from the CSS)
