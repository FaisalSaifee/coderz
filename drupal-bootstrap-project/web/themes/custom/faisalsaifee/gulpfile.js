const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const browserSync = require('browser-sync').create();

// Compile SCSS to CSS
gulp.task('sass', function () {
  return gulp.src('scss/**/*.scss') // Adjust path if needed
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('css'))
    .pipe(browserSync.stream());
});

// Watch and Serve
gulp.task('serve', function () {
  browserSync.init({
    proxy: "https://drupal-bootstrap-project.ddev.site"
  });

  gulp.watch('scss/**/*.scss', gulp.series('sass'));
  gulp.watch('templates/**/*.twig').on('change', browserSync.reload);
});

// Default task
gulp.task('default', gulp.series('sass', 'serve'));
