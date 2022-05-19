<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', "wordpress" );

/** MySQL database username */
define( 'DB_USER', "root" );

/** MySQL database password */
define( 'DB_PASSWORD', "root" );

/** MySQL hostname */
define( 'DB_HOST', "localhost" );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '{Jwn5=3?p@%%/pjxBaV;5iI$en1Pb23Sq#O?X$H6GL^8bIquW ?I?%Q>Pn]9(|_8' );
define( 'SECURE_AUTH_KEY',  '-t,w`E1bhq),}QDKlc(IAVUL?8P)&hC@u*-aIuyh$fv1c2uW|j%uB>]`Ku%;6H<!' );
define( 'LOGGED_IN_KEY',    'E~G8Cq`0EF[~8TU@bFbe[Kh]GcNPv2/BH*lHQ{lr1v2vbs}LE[6Fj.m8<9gSc%9I' );
define( 'NONCE_KEY',        '<4$tQc6P~x/gr},/}0!,N+$zb*DEzw{_GP*2F;.%Xsu{Aif7-#+f@sPD,z2xdu%%' );
define( 'AUTH_SALT',        'HlpI+[/=v@l?IN}]#<WghhYkncjW6!Eqel8yxZB*3:K6hZToQ9kqc.[GLs)I9gO%' );
define( 'SECURE_AUTH_SALT', 'oN_I6Rk=<mI2<zPVHm{j04J-%4V!SI>Xx$*2eeD7!vR`#`EU^i7sQb}m(}`pbM#?' );
define( 'LOGGED_IN_SALT',   'Pl#b?@RT7}*vbFy35w#,Yd%kHd^gW=,6f{)GVlqF$gu)*2U Abic{44@.NcifPPP' );
define( 'NONCE_SALT',       '2YhIi*/WEi?x(pb$yTE^r8nAMgdq@@vN{0v{&4`ekcsnnU^=Gx3X xOuq[5bd%Y,' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

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

define( 'JETPACK_DEV_DEBUG', true
);

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
