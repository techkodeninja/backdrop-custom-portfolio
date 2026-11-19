// node-wp-i18n always writes the wordpress.org support URL into
// Report-Msgid-Bugs-To, but this plugin is not listed on wordpress.org.
// Point bug reports at the GitHub issues instead (the URL in
// package.json's "bugs" field), so every regeneration — local or in the
// release workflow — gets the right address.
import { readFileSync, writeFileSync } from 'node:fs';

const potFile = 'public/lang/backdrop-custom-portfolio.pot';
const bugsUrl = JSON.parse( readFileSync( 'package.json', 'utf8' ) ).bugs.url;

const contents = readFileSync( potFile, 'utf8' );

// The header is usually split over two lines:
//   "Report-Msgid-Bugs-To: "
//   "https://wordpress.org/support/plugin/backdrop-custom-portfolio\n"
// Collapse it into one line with the real address.
const twoLine = contents.replace(
	/^"Report-Msgid-Bugs-To: "\n"https:\/\/wordpress\.org\/support\/plugin\/backdrop-custom-portfolio\\n"$/m,
	`"Report-Msgid-Bugs-To: ${bugsUrl}\\n"`
);

const fixed = twoLine === contents
	? contents.replace(
		/^"Report-Msgid-Bugs-To: https:\/\/wordpress\.org\/support\/plugin\/backdrop-custom-portfolio\\n"$/m,
		`"Report-Msgid-Bugs-To: ${bugsUrl}\\n"`
	)
	: twoLine;

if ( fixed === contents ) {
	console.error( `Could not find the Report-Msgid-Bugs-To line in ${potFile}.` );
	process.exit( 1 );
}

writeFileSync( potFile, fixed );
console.log( `Report-Msgid-Bugs-To set to ${bugsUrl} in ${potFile}.` );
