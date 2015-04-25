var gulp          = require('gulp'),
    sass          = require('gulp-sass'),
    autoprefixer  = require('gulp-autoprefixer'),
    livereload    = require('gulp-livereload'),
    sourcemaps    = require('gulp-sourcemaps'),
    plumber       = require('gulp-plumber'),
    jshint        = require('gulp-jshint');

gulp.task('sass', function () {
  gulp.src('public/scss/main.scss')
  .pipe(plumber())
  .pipe(sourcemaps.init())
  .pipe(sass())
  .pipe(autoprefixer({
    browsers: ['last 2 versions'],
    cascade: true
  }))
  .pipe(sourcemaps.write())
  .pipe(gulp.dest('public/css'))
  .pipe(livereload());
});

gulp.task('scripts', function () {
  return gulp.src('public/app/**/*.js')
    .pipe(jshint())
    .pipe(jshint.reporter('jshint-stylish'))
    .pipe(livereload());
});

gulp.task('default', ['sass', 'scripts'], function(){
  livereload.listen();
  gulp.watch('public/**/*.scss', ['sass']);
  gulp.watch('public/app/**/*.js', ['scripts']);
});
