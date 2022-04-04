<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'mytort' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '` ?ODYl#,f6k_:thv}In)Rguq,Sg2v<+&FQ^?spafl5~J-?|NyXq)[R]Edhe]:`m' );
define( 'SECURE_AUTH_KEY',  '9>?Y@4G!q73$un|([[054V?XA-F$d.!sg?E(</XS6}Uaw^%-RbcK<~E}9/gTt^w0' );
define( 'LOGGED_IN_KEY',    '[W^vH:[^SPhqf1{A9nF`d03::QthL$G`FlcG63h9m9anB;iz$h$sspko?:sB@5`F' );
define( 'NONCE_KEY',        '?[C|MAXgB>YV:e.XTZYHaVzS<>%j:fc3wYBU#zU1mp3f~@O=su5)-8)oW?smNMDH' );
define( 'AUTH_SALT',        '+@9Mwn-Y? }Sh2/^%Mu/C_Zr9QA/Z3.} IB]2!bxv3rt9jbE_q2Z7cv)yWbNdK&U' );
define( 'SECURE_AUTH_SALT', 't`u{>D9lPUdlhS}l]G aUmZxED9(OpC0OL}F64/!up<:/$IY cap_> _)dSf7^|A' );
define( 'LOGGED_IN_SALT',   '8h$th 2oC:]-vjr6?do&o.{OXi<`tJqf0kXz%Bwqo4oP-N bG61k-GjN(#w.!Oyd' );
define( 'NONCE_SALT',       'h z=tsLTS+f}wj-(4Y-[$dF%^$FX*V3,BAt @:c+i/!yf7bSV5rKm&?C+KKyQF-i' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'mt_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
