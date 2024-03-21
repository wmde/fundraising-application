<?php

declare( strict_types = 1 );

namespace WMDE\Fundraising\Frontend\BucketTesting;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class CampaignConfiguration implements ConfigurationInterface {

	public function getConfigTreeBuilder(): TreeBuilder {
		$treeBuilder = new TreeBuilder( 'bucket_tests' );
		$rootNode = $treeBuilder->getRootNode();

		$rootNode
			->children()
				->arrayNode( 'campaigns' )
					->useAttributeAsKey( 'name' )
					->arrayPrototype()
						->children()
							->scalarNode( 'description' )
								->info( 'What this campaign is about' )
							->end()
							->scalarNode( 'reference' )
								->info( 'URL with more information about this campaign, e.g. Phabricator ticket' )
							->end()
							->scalarNode( 'start' )
								->info( 'Start date of campaign, format YYYY-MM-DD or YYYY-MM-DD HH:MM:SS. Timezone is configured in app config' )
								->isRequired()
							->end()
							->scalarNode( 'end' )
								->info( 'Start date of campaign, format YYYY-MM-DD or YYYY-MM-DD HH:MM:SS. Timezone is configured in app config' )
								->isRequired()
							->end()
							->booleanNode( 'active' )
								->info( 'If campaign is active. Allows for campaigns to run as long as they want' )
								->isRequired()
							->end()
							->arrayNode( 'buckets' )
								->info( 'Name of the buckets the user is put in' )
								->scalarPrototype()->end()
								->isRequired()
							->end()
							->scalarNode( 'default_bucket' )
								->info( 'Bucket to use when campaign is not active' )
								->isRequired()
							->end()
							->scalarNode( 'url_key' )
								->info( 'URL parameter key used for assigning buckets to people' )
								->isRequired()
							->end()
							->booleanNode( 'param_only' )
								->info( 'Returns the default bucket when the "url_key" parameter is missing in a request' )
								->defaultFalse()
							->end()
						->end()
					->end()
				->end();

		return $treeBuilder;
	}

}
