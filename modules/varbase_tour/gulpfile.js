let gulp = require('gulp'),
  sass = require('gulp-sass'),
  postcss = require('gulp-postcss'),
  csscomb = require('gulp-csscomb'),
  autoprefixer = require('autoprefixer'),
  browserSync = require('browser-sync').create()

const paths = {
  scss: {
    src: 'scss/**/*.scss',
    dest: 'css',
    watch: 'scss/**/*.scss'
  },
  js: {  }
}

// Compile sass into CSS & auto-inject into browsers.
function compile () {
  var sassOptions = {
    outputStyle: 'expanded',
    indentType: 'space',
    indentWidth: 2,
    linefeed: 'lf'
  };

  return gulp.src([paths.scss.src])
    .pipe(sass(sassOptions).on('error', sass.logError))
    .pipe(postcss([autoprefixer({
      browsers: [
        'Chrome >= 35',
        'Firefox >= 38',
        'Edge >= 12',
        'Explorer >= 10',
        'iOS >= 8',
        'Safari >= 8',
        'Android 2.3',
        'Android >= 4',
        'Opera >= 12']
    })]))
    .pipe(csscomb())
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream())
}

// Watching scss files.
function watch () {
  gulp.watch([paths.scss.watch], compile)
}

const build = gulp.series(compile, gulp.parallel(watch))

exports.compile = compile
exports.watch = watch

exports.default = build
