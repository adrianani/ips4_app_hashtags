//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hashtags_hook_Content_Search_Mysql_index extends _HOOK_CLASS_
{
	public function removeFromSearchIndex( \IPS\Content\Searchable $object ) {
		$class = \get_class( $object );
		$idColumn = $class::$databaseColumnId;
		$class = \IPS\Db::i()->escape_string( $class );
		\IPS\Db::i()->query("DELETE FROM `hashtags_search_index` WHERE `search_index_id` IN (SELECT index_id FROM `core_search_index` WHERE `index_class`='{$class}' AND `index_object_id`={$object->$idColumn})");

		parent::removeFromSearchIndex( $object );
	}

	public function index( \IPS\Content\Searchable $object ) {

		parent::index( $object );

		$class = \get_class( $object );

		if( !( $object instanceof \IPS\Content\Item ) || ( $object instanceof \IPS\Content\Item && isset( $class::$databaseColumnMap['content'] ) ) ) {
			$contentColumn = $class::$databaseColumnMap['content'];
			$application = ( $object instanceof \IPS\Content\Item ) ? $class::$application : $class::$itemClass::$application;
			$searchIndex = $this->getIndexId( $object );
			$idColumn = $class::$databaseColumnId;
			$class = \IPS\Db::i()->escape_string( $class );
			\IPS\Db::i()->query("DELETE FROM `hashtags_search_index` WHERE `search_index_id` IN (SELECT index_id FROM `core_search_index` WHERE `index_class`='{$class}' AND `index_object_id`={$object->$idColumn})");

			$object->$contentColumn = preg_replace_callback( 
				'/(?<start>^|\s|\B)(<(?<span>span) data-hashtag=[^>]*>)?(#(?<hashtag>(?![a-f0-9]{3}(\W|$)|[a-f0-9]{6}(\W|$))([a-z]+[0-9]*[a-z]*|[a-z]*[0-9]*[a-z]+){2,140}))(<\/\k<span>>)?/iu',
				function( $matches ) use ( $searchIndex, $application ) {
		
					$hashtagId = \IPS\Db::i()->insert(
						'hashtags_search_index',
						[
							'hashtag' => $matches['hashtag'],
							'meta_app' => $application,
							'search_index_id' => $searchIndex,
							'created' => time()
						]
					);

					return "{$matches['start']}<span data-hashtag=\"{$matches['hashtag']}\" data-hashtag-id=\"{$hashtagId}\">#{$matches['hashtag']}</span>";
				}, 
				$object->$contentColumn
			);

			$object->save();
		}
	}
}
