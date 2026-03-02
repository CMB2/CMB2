#!/usr/bin/env node

/**
 * CSS banner injection and minification script.
 *
 * Usage:
 *   node scripts/build-banner.js          # Add expanded banners to CSS files
 *   node scripts/build-banner.js --minify # Create .min.css files with one-line banner
 */

const fs = require( 'fs' );
const path = require( 'path' );

const pkg = require( path.join( __dirname, '..', 'package.json' ) );
const date = new Date().toISOString().slice( 0, 10 );
const year = new Date().getFullYear();

const cssFiles = [
	'css/cmb2.css',
	'css/cmb2-front.css',
	'css/cmb2-display.css',
	'css/cmb2-rtl.css',
	'css/cmb2-front-rtl.css',
	'css/cmb2-display-rtl.css',
];

const expandedBanner =
	'/*!\n' +
	' * ' + pkg.title + ' - v' + pkg.version + ' - ' + date + '\n' +
	' * ' + pkg.homepage + '\n' +
	' * Copyright (c) ' + year + '\n' +
	' * Licensed GPLv2+\n' +
	' */\n';

const minBanner =
	'/*! ' + pkg.title + ' - v' + pkg.version + ' - ' + date +
	' | ' + pkg.homepage +
	' | Copyright (c) ' + year + ' ' + pkg.author.name +
	' | Licensed ' + pkg.license +
	' */';

const isMinify = process.argv.includes( '--minify' );

if ( isMinify ) {
	// Minify mode: create .min.css files with one-line banner.
	let CleanCSS;
	try {
		CleanCSS = require( 'clean-css' );
	} catch ( e1 ) {
		try {
			// clean-css may be nested inside clean-css-cli
			CleanCSS = require( 'clean-css-cli/node_modules/clean-css' );
		} catch ( e2 ) {
			console.error( 'clean-css not found. Install clean-css-cli: npm install clean-css-cli' );
			process.exit( 1 );
		}
	}

	const cleancss = new CleanCSS( { level: 1 } );

	cssFiles.forEach( function( file ) {
		const content = fs.readFileSync( file, 'utf8' );
		const result = cleancss.minify( content );

		if ( result.errors.length ) {
			console.error( 'Error minifying ' + file + ':', result.errors );
			process.exit( 1 );
		}

		const outFile = file.replace( '.css', '.min.css' );
		fs.writeFileSync( outFile, minBanner + '\n' + result.styles );
		console.log( 'Minified: ' + outFile );
	} );
} else {
	// Banner mode: prepend expanded banner to CSS files.
	cssFiles.forEach( function( file ) {
		if ( ! fs.existsSync( file ) ) {
			console.warn( 'Skipping (not found): ' + file );
			return;
		}

		const content = fs.readFileSync( file, 'utf8' );

		// Strip any existing banner before adding new one.
		const stripped = content.replace( /^\/\*![\s\S]*?\*\/\s*/, '' );
		fs.writeFileSync( file, expandedBanner + '\n' + stripped );
		console.log( 'Banner added: ' + file );
	} );
}
