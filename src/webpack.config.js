"use strict";
let path = require('path'),
    TextPlugin = require('extract-text-webpack-plugin'),
    webpack = require('webpack');

let appRoot = __dirname;

module.exports = {
    context: appRoot,
    entry: {
        bundle: './ts/app.ts',
    },
    output: {
        path: path.resolve( appRoot, '..', 'assets' ),
        filename: '[name].js',
    },
    resolve: {
        extensions: ['.js', '.ts', '.styl'],
        alias: {
            styl: path.resolve(appRoot, 'styl'),
            ts: path.resolve(appRoot, 'ts'),
        },
    },
    module: {
        rules: [
            { test: /\.ts$/, loader: 'ts-loader?rtts' },
            { test: /\.styl$/, loader: TextPlugin.extract({fallback: 'style-loader', use: [
                'css-loader?minimize',
                {
                    loader: 'postcss-loader',
                    options: {
                        plugins:[
                            require('autoprefixer'),
                        ],
                    },
                },
                'resolve-url-loader',
                'stylus-loader?sourceMap',
            ]})},
        ],
    },
    plugins: [
        new TextPlugin('[name].css'),
        new webpack.optimize.UglifyJsPlugin(),
    ],

    devtool: 'eval-source-map',
};
