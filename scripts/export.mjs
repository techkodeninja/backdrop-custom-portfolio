/**
 * Plugin Export Script
 *
 * Copies only the files needed in production into `backdrop-custom-portfolio/`
 * so it can be zipped for release. Edit `files` or `folders` to change it.
 */

import { cpSync, existsSync, rmSync } from 'node:fs';

const exportPath = 'backdrop-custom-portfolio';
const files      = [ 'backdrop-custom-portfolio.php', 'readme.txt' ];
const folders    = [ 'app', 'public', 'vendor' ];

// Start clean.
rmSync( exportPath, { recursive: true, force: true } );

[ ...files, ...folders ].forEach( ( item ) => {
	if ( existsSync( item ) ) {
		cpSync( item, `${ exportPath }/${ item }`, { recursive: true } );
	}
} );

// Leftovers from Composer that aren't needed in production.
[ 'vendor/bin', 'vendor/composer/installers' ].forEach( ( dir ) => {
	rmSync( `${ exportPath }/${ dir }`, { recursive: true, force: true } );
} );
