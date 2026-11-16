/**
 * Vite configuration file.
 *
 * Compiles `resources/scss/screen.scss` to `public/assets/css/screen.css`.
 *
 * @package   Backdrop Custom Portfolio
 * @author    Benjamin Lu <benlumia007@gmail.com>
 * @copyright 2014-2023 Benjamin Lu
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GPL-2.0-or-later
 * @link      https://github.com/techkodeninja/backdrop-custom-portfolio
 */

import { defineConfig } from 'vite';
import { resolve } from 'node:path';

// The build only needs the stylesheet, so drop the empty JS chunk Vite emits
// for a CSS-only entry point.
const dropEmptyJs = () => ( {
	name: 'drop-empty-js',
	generateBundle( _, bundle ) {
		for ( const [ name, chunk ] of Object.entries( bundle ) ) {
			if ( chunk.type === 'chunk' ) {
				delete bundle[ name ];
			}
		}
	},
} );

export default defineConfig( {
	plugins: [ dropEmptyJs() ],
	publicDir: false,
	build: {
		outDir: 'public',
		// `public` also holds translations, so never wipe it.
		emptyOutDir: false,
		cssCodeSplit: false,
		rollupOptions: {
			input: { screen: resolve( __dirname, 'resources/scss/screen.scss' ) },
			output: {
				entryFileNames: 'assets/css/[name].js',
				assetFileNames: 'assets/css/[name][extname]',
			},
		},
	},
} );
