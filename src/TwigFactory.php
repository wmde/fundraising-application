<?php


namespace WMDE\Fundraising\Frontend;

use Twig_Environment;
use Twig_Lexer;
use Twig_Loader_Filesystem;

/**
 * @license GNU GPL v2+
 * @author Gabriel Birke < gabriel.birke@wikimedia.de >
 */
class TwigFactory {

	public static function newFromConfig( array $config ): Twig_Environment {
		$options = [];

		if ( $config['enable-twig-cache'] ) {
			$options['cache'] = __DIR__ . '/../app/cache';
		}

		$templateDir = $config['template-dir']  ?: __DIR__ . '/../app/templates';
		$twig = new Twig_Environment(
			new Twig_Loader_Filesystem( $templateDir ),
			$options
		);

		$lexer = new Twig_Lexer( $twig, [
			'tag_comment'   => [ '{#', '#}' ],
			'tag_block'     => [ '{%', '%}' ],
			'tag_variable'  => [ '{$', '$}' ]
		] );
		$twig->setLexer($lexer);

		return $twig;
	}
}