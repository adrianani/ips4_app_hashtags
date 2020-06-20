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
			$hashtags = iterator_to_array(\IPS\Db::i()->select('search_index_id', 'hashtags_search_index', [ \IPS\Db::i()->in('hashtag', explode(',', \IPS\Request::i()->hashtags)) ]));
			
			$where[] = \IPS\Db::i()->in('index_id', $hashtags);
		}

		return $where;

	}

}
