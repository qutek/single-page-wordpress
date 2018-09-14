const gulp = require('gulp');
const strip = require('gulp-strip-comments');
const uglify = require('gulp-uglify');
const rename = require('gulp-rename');
const concat = require('gulp-concat');
const dest = './dist/';
 
gulp.task('scripts', function() {
  return gulp.src([
	  	'node_modules/turbolinks/dist/turbolinks.js', 
	  	'node_modules/onmount/index.js',
	  	'src/index.js'
  	])
  	.pipe(strip())
    .pipe(concat('spwp.js'))
  	.pipe(gulp.dest(dest))
    .pipe(uglify())
    .pipe(rename({ extname: '.min.js' }))
    .pipe(gulp.dest(dest));
});

// Watches JS
gulp.task('watch', function() {
	gulp.watch('src/**/**.js', ['scripts']);
});

// Default Task
gulp.task('default', ['scripts', 'watch']);