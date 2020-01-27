'use strict';

const

  dir = {
    src         : 'src/',
    dist        : 'dist/commenter/',
    plugin      : '../../plugins/commenter/'
  },
  gulp          = require('gulp'),
  gutil         = require('gulp-util'),
  watch         = require('gulp-watch'),
  newer         = require('gulp-newer'),
  imagemin      = require('gulp-imagemin'),
  rename        = require("gulp-rename"),
  postcss       = require('gulp-postcss'),
  deporder      = require('gulp-deporder'),
  concat        = require('gulp-concat'),
  stripdebug    = require('gulp-strip-debug'),
  uglify        = require('gulp-uglify-es').default
;

var browsersync = false;

const php = {
  src           : dir.src + 'php/**/*.php',
  dist          : dir.dist,
  plugin        : dir.plugin
};

const img = {
  src           : dir.src + 'img/**/*',
  dist          : dir.dist + 'assets/img/',
  plugin        : dir.plugin + 'assets/img/'
};

const admin_js = {
  src         : dir.src + 'js/admin/**/*',
  dist        : dir.dist + 'assets/js/admin/',
  plugin      : dir.plugin + 'assets/js/admin/',
  filename    : 'commenter-admin.js'
};

const public_js = {
  src         : dir.src + 'js/public/**/*',
  dist        : dir.dist + 'assets/js/public/',
  plugin      : dir.plugin + 'assets/js/public/',
  filename    : 'commenter-public.js'
};

var admin_css = {
  src         : dir.src + 'css/admin/commenter-admin.css',
  watch       : dir.src + 'css/admin/**/*',
  dist        : dir.dist + 'assets/css/admin/',
  plugin      : dir.plugin + 'assets/css/admin/',
  processors: [
    require('postcss-assets')({
      loadPaths: ['images/'],
      basePath: dir.build,
      baseUrl: '/wp-content/plugins/commenter'
    })
  ]
};

var public_css = {
  src         : dir.src + 'css/public/commenter-public.css',
  watch       : dir.src + 'css/public/**/*',
  dist        : dir.dist + 'assets/css/public/',
  plugin      : dir.plugin + 'assets/css/public/',
  processors: [
    require('postcss-assets')({
      loadPaths: ['images/'],
      basePath: dir.build,
      baseUrl: '/wp-content/plugins/commenter'
    })
  ]
};

gulp.task('images', () => {
  return gulp.src(img.src)
    .pipe(gulp.dest(img.dist))
    .pipe(gulp.dest(img.plugin))
    .pipe(imagemin());
});

gulp.task('admin-css', gulp.series( 'images', () => {
  return gulp.src(admin_css.src)
    .pipe(gulp.dest(admin_css.dist))
    .pipe(gulp.dest(admin_css.plugin))
    .pipe(postcss(admin_css.processors))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(admin_css.plugin))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
} ) );

gulp.task('admin-js', () => {

  return gulp.src(admin_js.src)
    .pipe(deporder())
    .pipe(gulp.dest(admin_js.dist))
    .pipe(gulp.dest(admin_js.plugin))
    .pipe(concat(admin_js.filename))
    .pipe(stripdebug())
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(admin_js.dist))
    .pipe(gulp.dest(admin_js.plugin))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

});

gulp.task('public-css', gulp.series('images', () => {
  return gulp.src(public_css.src)
    .pipe(gulp.dest(public_css.dist))
    .pipe(postcss(public_css.processors))
    .pipe(gulp.dest(public_css.dist))
    .pipe(gulp.dest(public_css.plugin))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
} ) );

gulp.task('public-js', () => {

  return gulp.src(public_js.src)
    .pipe(gulp.dest(public_js.dist))
    .pipe(concat(public_js.filename))
    .pipe(gulp.dest(public_js.dist))
    .pipe(gulp.dest(public_js.plugin))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

});

gulp.task('public-min-css', gulp.series('images', () => {
  return gulp.src(public_css.src)
    .pipe(gulp.dest(public_css.dist))
    .pipe(postcss(public_css.processors))
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(public_css.dist))
    .pipe(gulp.dest(public_css.plugin))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());
} ) );

gulp.task('public-min-js', () => {

  return gulp.src(public_js.src)
    .pipe(gulp.dest(public_js.dist))
    .pipe(concat(public_js.filename))
    .pipe(stripdebug())
    .pipe(uglify())
    .pipe(rename({ suffix: '.min' }))
    .pipe(gulp.dest(public_js.dist))
    .pipe(gulp.dest(public_js.plugin))
    .pipe(browsersync ? browsersync.reload({ stream: true }) : gutil.noop());

});

gulp.task('php', () => {
  return gulp.src(php.src)
    .pipe(newer(php.dist))
    .pipe(gulp.dest(php.dist))
    .pipe(gulp.dest(php.plugin));
});

gulp.task('build', gulp.series( 'php', 'admin-css', 'admin-js', 'public-css', 'public-js') );

// Browsersync options
const syncOpts = {
  proxy       : 'localhost',
  files       : dir.src + '**/*',
  open        : false,
  notify      : false,
  ghostMode   : false,
  ui: {
    port: 8001
  }
};

gulp.task( 'watch', function() {

  if (browsersync === false) {
    browsersync = require('browser-sync').create();
    browsersync.init(syncOpts);
  }

  gulp.watch( php.src, gulp.series( 'php' ), browsersync ? browsersync.reload : {} );

  gulp.watch( img.src, gulp.series( 'images' ) );

  gulp.watch( admin_css.watch, gulp.series( 'admin-css' ) );

  gulp.watch( admin_js.src, gulp.series( 'admin-js' ) );

  gulp.watch( public_css.watch, gulp.series( 'public-css' ) );

  gulp.watch( public_js.src, gulp.series( 'public-js' ) );

  gulp.watch( public_css.watch, gulp.series( 'public-min-css' ) );

  gulp.watch( public_js.src, gulp.series( 'public-min-js' ) );


} );

gulp.task( 'default', gulp.series( 'build', 'watch' ) );