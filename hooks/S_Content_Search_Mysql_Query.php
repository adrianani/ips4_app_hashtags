//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

class hashtags_hook_S_Content_Search_Mysql_Query extends _HOOK_CLASS_
{

	protected function _searchWhereClause( $term = NULL, $tags = NULL, $method = 1, $operator = 'and' ) {

		$where = parent::_searchWhereClause( $term, $tags, $method, $operator );
		
		if(isset(\IPS\Request::i()->hashtags)) {
			$hashtags = iterator_to_array(\IPS\Db::i()->select('meta_item_id, meta_member_id, meta_comment_id', 'hashtags_hashtags', [ \IPS\Db::i()->in('hashtag', explode(',', \IPS\Request::i()->hashtags)) ]));
			
			$where = array_merge( 
				$where, 
				[
					\IPS\Db::i()->in('index_item_id', array_column($hashtags, 'meta_item_id')),
					\IPS\Db::i()->in('index_author', array_column($hashtags, 'meta_member_id')),
					\IPS\Db::i()->in('index_object_id', array_column($hashtags, 'meta_comment_id')),
				]
			);
		}

		return $where;

	}

}
