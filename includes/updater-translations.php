<?php
/** Copy into the host adapter and replace the connect-for-shopware textdomain with its literal domain. */
defined( 'ABSPATH' ) || exit;
return static function ( string $message ): string {
    // Literal calls let the host's normal translation extractor collect every source string.
    switch ( $message ) {
        case 'Private mode must be boolean.':
            return __( 'Private mode must be boolean.', 'connect-for-shopware' );
        case 'Invalid authentication provider.':
            return __( 'Invalid authentication provider.', 'connect-for-shopware' );
        case 'The plugin must be installed in a stable slug directory.':
            return __( 'The plugin must be installed in a stable slug directory.', 'connect-for-shopware' );
        case 'Invalid GitHub repository URL.':
            return __( 'Invalid GitHub repository URL.', 'connect-for-shopware' );
        case 'The private update could not be authorized. Check the repository credentials and refresh updates.':
            return __( 'The private update could not be authorized. Check the repository credentials and refresh updates.', 'connect-for-shopware' );
        case 'Could not create the update download file.':
            return __( 'Could not create the update download file.', 'connect-for-shopware' );
        case 'The private update download failed. Check credentials and try again.':
            return __( 'The private update download failed. Check credentials and try again.', 'connect-for-shopware' );
        case 'Could not access the update filesystem.':
            return __( 'Could not access the update filesystem.', 'connect-for-shopware' );
        case 'GitHub release does not contain the plugin main file.':
            return __( 'GitHub release does not contain the plugin main file.', 'connect-for-shopware' );
        case 'Could not prepare the GitHub release package.':
            return __( 'Could not prepare the GitHub release package.', 'connect-for-shopware' );
        case 'See the release on GitHub.':
            return __( 'See the release on GitHub.', 'connect-for-shopware' );
        default:
            return $message;
    }
};
