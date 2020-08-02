var gulp = require('gulp'),
    less = require('gulp-less'),
    cssTask = function () {
        var postcss = require('gulp-postcss');
        var tailwindcss = require('tailwindcss');

        return gulp.src('src/style.less')
        // ...
            .pipe(less())
            .pipe(postcss([
                // ...
                tailwindcss('./tailwind.config.js'),
                require('autoprefixer'),
                // ...
            ]))
            // ...
            .pipe(gulp.dest('build/'));
    };

gulp.task('css', cssTask);

gulp.task('watch', function () {
    gulp.watch(['src/*.less', 'src/**/*.less', 'tailwind.config.js'], cssTask);
});
