const elixir = require('laravel-elixir');
var Promise = require('es6-promise').Promise;
var shell = require('gulp-shell');

/*
 |--------------------------------------------------------------------------
 | Elixir Asset Management
 |--------------------------------------------------------------------------
 |
 | Elixir provides a clean, fluent API for defining some basic Gulp tasks
 | for your Laravel application. By default, we are compiling the Sass
 | file for our application, as well as publishing vendor resources.
 |
 */

gulp.task('langjs', shell.task('php artisan lang:js -c public/js/messages.js'));

elixir(function(mix) {
   mix.task('langjs');
   mix.sass('new-style.scss', 'public/user/css/new-style.css')
   .sass('style.scss', 'public/user/css/style.css')
   .sass('socialize-bookmarks.scss', 'public/user/css/socialize-bookmarks.css')
   .sass('home.scss', 'public/user/css/home.css')
   .sass('admin-style-1.scss', 'public/admin/css/admin-style-1.css')
   .sass('admin-pages.scss', 'public/admin/css/admin-pages.css')
   .scripts('app.js')
   .scripts('socket.js', 'public/user/js/socket.js')
   .scripts('question.js', 'public/user/js/question.js')
   .scripts('functions.js', 'public/user/js/functions.js')
   .scripts('jquery.bxslider.min.js', 'public/user/js/jquery.bxslider.min.js')
   .scripts('check.min.js', 'public/user/js/check.min.js')
   .scripts('validate.js', 'public/user/js/validate.js')
   .scripts('jquery.wizard.js', 'public/user/js/jquery.wizard.js')
   .scripts('component.js', 'public/user/js/component.js')
   .scripts('alert.js', 'public/user/js/alert.js')
   .scripts('step-wizard.js', 'public/user/js/step-wizard.js')
   .scripts('survey.js', 'public/admin/js/survey.js')
   .scripts('form-request.js', 'public/admin/js/form-request.js')
   .scripts('admin-script.js', 'public/admin/js/admin-script.js')
   .copy('resources/assets/fonts', 'public/admin/fonts')
   .copy('resources/assets/css/404.css', 'public/user/errors/404.css')
   .copy('resources/assets/images', 'public/user/images')
   .copy([
      'public/bower/bootstrap/dist/css/bootstrap.css',
      'public/bower/bootstrap/dist/css/bootstrap.min.css'
   ], 'public/admin/css')
   .copy([
      'public/bower/bootstrap/dist/js/bootstrap.min.js',
      'public/bower/bootstrap/dist/js/bootstrap.js',
      'public/bower/jquery/dist/jquery.js'
   ], 'public/admin/js')
   .copy('node_modules/flipclock/compiled/flipclock.css', 'public/user/css')
   .copy('node_modules/flipclock/compiled/flipclock.min.js', 'public/user/js')
   .version(['public/user/css/*.css', 'public/user/js/*.js', 'public/js/app.js']);
});
