import path from 'path';
import fs from 'fs';
import mkdirp from 'mkdirp';
import gulp from 'gulp';
import sass from 'gulp-dart-sass';
import postcss from 'postcss';
import gulpPostCss from 'gulp-postcss';
import stylelint from 'gulp-stylelint-esm';

// Path configuration
const paths = {
  scss: {
    src: 'scss/', // Path to your SCSS files
    dest: 'css/dist/' // Path where compiled CSS will be saved
  }
};

function splitMedia({ outputDir, maintainMediaQuery = false }) {
  return {
    postcssPlugin: 'postcss-split-media',
    Once(root, { result }) {
      const mediaQueries = {};

      const { from } = result?.opts;
      const { name } = path.parse(from);
      const ext = path.extname(from);
      const parentDir = from.split(path.join(paths.scss.src))[1].split(`${name}${ext}`)[0]; // Ensure the directory structure is the same as the scss

      // Iterate through all the rules in the CSS
      root.walkAtRules('media', (atRule) => {
        const media = atRule.params.replace(/[^\w-]/g, ''); // Clean the media query string for filenames
        if (!mediaQueries[media]) {
          mediaQueries[media] = postcss.root();
        }

        if (maintainMediaQuery) {
          mediaQueries[media].append(atRule.clone());
        } else {
          atRule.walkRules(rule => {
            mediaQueries[media].append(rule.clone());
          });
        }
        atRule.remove();
      });

      // Write the media query CSS to separate files
      Object.keys(mediaQueries).forEach((media) => {
        const mediaCSS = mediaQueries[media].toString();
        const filePath = path.join(outputDir, parentDir, `${name}-${media}${ext}`);
        mkdirp.sync(path.dirname(filePath)); // Ensure the directory exists
        fs.writeFileSync(filePath, mediaCSS);
      });

      // Remove all media queries from the main file
      root.walkAtRules('media', (rule) => {
        rule.remove();
      });
    }
  };
}
splitMedia.postcss = true;

export function watchScss() {
  gulp.watch(path.join(paths.scss.src, '**/*.scss'), gulp.series(compileScss));
}

export function compileScss() {
  return gulp.src(path.join(paths.scss.src, '**/*.scss'))
    .pipe(sass().on('error', sass.logError)) // Compile SCSS to CSS
    .pipe(gulpPostCss([
      splitMedia({ outputDir: paths.scss.dest }) // Split CSS by media queries
    ]))
    .pipe(gulp.dest(paths.scss.dest)); // Save the output CSS files
}

export function lintScss() {
  return gulp.src(path.join(paths.scss.src, '**/*.scss'))
    .pipe(stylelint({
      reporters: [
        { formatter: 'string', console: true }
      ]
    }));
}

export function lintAndFixScss() {
  return gulp.src(path.join(paths.scss.src, '**/*.scss'))
    .pipe(stylelint({
      fix: true
    }))
    .pipe(gulp.dest(paths.scss.src));
}

export function lintCss() {
  return gulp.src(path.join(paths.scss.dest, '**/*.css'))
    .pipe(stylelint({
      reporters: [
        { formatter: 'string', console: true }
      ]
    }));
}

export function lintAndFixCss() {
  return gulp.src(path.join(paths.scss.dest, '**/*.css'))
    .pipe(stylelint({
      fix: true
    }))
    .pipe(gulp.dest(paths.scss.dest));
}

// Register the tasks with Gulp
gulp.task('compileScss', compileScss);
gulp.task('watchScss', watchScss);
gulp.task('lintScss', lintScss);
gulp.task('lintAndFixScss', lintAndFixScss);
gulp.task('lintCss', lintCss);
gulp.task('lintAndFixCss', lintAndFixCss);

// Default task with linting
export default gulp.series('compileScss', 'lintAndFixCss', 'lintCss');
