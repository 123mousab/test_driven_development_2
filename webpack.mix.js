const mix = require('laravel-mix');

mix.js('resources/assets/js//app.js', 'public/js/app.js')
    .webpackConfig({
        output: {
            chunkFilename: 'js/[name].js' + (mix.inProduction() ? '?id=[chunkhash]' : ''),
        },
        resolve: {
            alias: {
                '@': path.resolve(__dirname, '/resources/js/app'),
            },
        },
    });
