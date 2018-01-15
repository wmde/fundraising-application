var gulp = require( 'gulp' );
var sass = require( 'gulp-sass' );
var browserify = require( 'browserify' );
var sourcemaps = require( 'gulp-sourcemaps' );
var autoprefixer = require( 'gulp-autoprefixer' );
var browserSync = require( 'browser-sync' ).create();
var uglify = require( 'gulp-uglify' );
var imagemin = require( 'gulp-imagemin' );
var gulpsync = require( 'gulp-sync' )( gulp );
var concat = require( 'gulp-concat' );
var source = require( 'vinyl-source-stream' );
var buffer = require( 'vinyl-buffer' );
var log = require( 'gulplog' );
var environments = require( 'gulp-environments' );
var cleanCSS = require('gulp-clean-css');

var dirs = {
	src: 'src',
	dist: 'web'
};

/* development tasks */

gulp.task( 'styles', function () {
	return gulp.src( dirs.src + '/sass/*.scss' )
		.pipe( sourcemaps.init() )
		.pipe( sass( {
			outputStyle: 'nested', // libsass doesn't support expanded yet
			precision: 10,
			includePaths: [ './node_modules' ]
		} ) )
		.pipe( autoprefixer( {
			browsers: [
				'last 2 versions',
				'android 4',
				'opera 12',
				'iOS >= 7'
			]
		} ) )
		.pipe( environments.production( cleanCSS( { compatibility: 'ie8' } ) ) )
		.pipe( sourcemaps.write( '.' ) )
		.pipe( gulp.dest( dirs.dist + '/css' ) )
		.pipe( browserSync.reload( {
			stream: true
		} ) )
} );

gulp.task( 'scripts', function ( cb ) {
	var buildDir = dirs.dist + '/scripts';
	var b = browserify( {
		entries: dirs.src + '/app/main.js',
		transform: [ 'envify' ],
		standalone: 'WMDE',
		debug: true
	});

	return b.bundle()
		.pipe( source( 'wmde.js' ) )
		.pipe( buffer() )
		.pipe( sourcemaps.init( { loadMaps: true } ) )
		// Add transformation tasks to the pipeline here.
			.pipe( environments.production( uglify() ) )
			.on( 'error', log.error )
		.pipe( sourcemaps.write( './' ) )
		.pipe( gulp.dest( buildDir  ) );

} );

gulp.task( 'browserSync', function () {
	browserSync.init( {
		server: {
			baseDir: dirs.src
		}
	} )
} );

gulp.task( 'watch', ['browserSync'], function () {
	gulp.watch( dirs.src + '/sass/**/*.scss', ['styles'] );
	gulp.watch( dirs.src + '/app/**/*.js', ['scripts', browserSync.reload] );
	gulp.watch( dirs.src + '/scripts/**/*.js', ['copies', browserSync.reload] );
} );

gulp.task( 'images', function () {
	return gulp.src( dirs.src + '/assets/images/**/*.{png,jpg,svg,ico}' )
		.pipe( environments.production( imagemin( [
			imagemin.jpegtran( {progressive: true} ),
			imagemin.gifsicle( {interlaced: true} ),
			imagemin.svgo( {plugins: [{removeUnknownsAndDefaults: false}, {cleanupIDs: false}]} )
		] ) ) )
		.pipe( gulp.dest( dirs.dist + '/assets/images' ) )
} );

gulp.task( 'copies', function () {
	gulp.src( dirs.src + '/assets/fonts/**/*.{ttf,woff,eof,svg,eot,woff2}' )
		.pipe( gulp.dest( dirs.dist + '/assets/fonts' ) );
	gulp.src( dirs.src + '/assets/favicons/*.*' )
		.pipe( gulp.dest( dirs.dist + '/assets/favicons' ) );
	gulp.src( dirs.src + '/assets/pdf/*.pdf' )
		.pipe( gulp.dest( dirs.dist + '/assets/pdf' ) );
	gulp.src( dirs.src + '/scripts/*.js' )
		.pipe( gulp.dest( dirs.dist + '/scripts' ) );

	gulp.src(
		[
			'node_modules/jquery/dist/jquery.js',
			'node_modules/jcf/dist/js/jcf.js',
			'node_modules/jcf/dist/js/jcf.select.js',
			'node_modules/jcf/dist/js/jcf.scrollable.js'
		]
		)
		.pipe( concat( { path: 'vendor.js', stat: { mode: 0666 } } ) )
		.pipe( environments.production( uglify() ) )
		.pipe( gulp.dest( dirs.dist + '/scripts' ) );
} );

gulp.task( 'default', gulpsync.sync( [['scripts', 'styles', 'images'], 'copies'] ) );
