'use strict';

var gulp = require('gulp'),
    watch = require('gulp-watch'),
    cssmin = require('gulp-minify-css'),
    prefixer = require('gulp-autoprefixer'),
    sourcemaps = require('gulp-sourcemaps'),
    sass = require('gulp-sass'),
    $ = require('gulp-load-plugins')({
        pattern: ['gulp-*', 'gulp.*'],
        replaceString: /\bgulp[\-.]/
    });

var settings = {
    path: './public_html/',
    sourcePath: './map',
    bootstrap: './node_modules/bootstrap-sass/',
    jquery: './node_modules/jquery/dist/jquery.js'
};
var sassOptions = {
    includePaths: [
        settings.bootstrap + 'assets/stylesheets'
    ]
};

gulp.task('js', function() {
   return gulp.src([
       settings.jquery,
       settings.bootstrap + 'assets/javascripts/bootstrap.js',
       settings.path + 'js/app/**/*.js'
   ])
       .pipe(sourcemaps.init())
       .pipe($.babel({
           presets: [
               ['es2015', { 'modules': false }],
               'babili'
           ]
       }))
       .pipe($.concat('build.js'))
       .pipe($.uglify())
       .pipe(sourcemaps.write(settings.sourcePath))
       .pipe(gulp.dest(settings.path + 'js'));
});

gulp.task('sass', function () {
    return gulp.src(settings.path + 'scss/**/*.scss')
        .pipe(sourcemaps.init())
        .pipe(sass(sassOptions).on('error', sass.logError))
        .pipe(prefixer())
        .pipe(cssmin())
        .pipe(sourcemaps.write(settings.sourcePath))
        .pipe(gulp.dest(settings.path + 'css'));
});

// copy bootstrap required fonts to public
gulp.task('fonts', function () {
    return gulp
        .src(settings.bootstrap + 'assets/fonts/**/*')
        .pipe(gulp.dest(settings.path + 'fonts'));
});

gulp.task('sass:watch', function () {
    gulp.watch(settings.path + 'scss/**/*.scss', ['sass']);
});

gulp.task('js:watch', function () {
    gulp.watch(settings.path + 'js/app/**/*.js', ['js']);
});

gulp.task('frontend', ['sass:watch', 'js:watch']);

gulp.task('default', ['js', 'sass']);