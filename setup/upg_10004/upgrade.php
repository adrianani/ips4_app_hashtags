<?php


namespace IPS\hashtags\setup\upg_10004;

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	header( ( isset( $_SERVER['SERVER_PROTOCOL'] ) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0' ) . ' 403 Forbidden' );
	exit;
}

/**
 * 1.0.4 Upgrade Code
 */
class _Upgrade
{
	/**
	 * ...
	 *
	 * @return	array	If returns TRUE, upgrader will proceed to next step. If it returns any other value, it will set this as the value of the 'extra' GET parameter and rerun this step (useful for loops)
	 */
	public function step1()
	{
		
		$oldHashtags = iterator_to_array( \IPS\Db::i()->select( 
			'hashtags_hashtags.hashtag, core_search_index.index_id', 
			'hashtags_hashtags', 
			[
				'meta_app=?',
				'forums'
			]
		)->join(
			'core_search_index',
			'core_search_index.index_item_id=hashtags_hashtags.meta_item_id AND core_search_index.index_author=hashtags_hashtags.meta_member_id AND core_search_index.index_object_id=hashtags_hashtags.meta_comment_id'
		) );
		

		foreach( $oldHashtags as $hashtag ) {
			\IPS\Db::i()->insert('hashtags_search_index', [
				'meta_app' => 'forums',
				'search_index_id' => $hashtag['index_id'],
				'hashtag' => $hashtag['hashtag']
			]);
		}

		return TRUE;
	}

	public function step2() {
		\IPS\Db::i()->dropTable('hashtags_hashtags');

		return TRUE;
	}
	
	// You can create as many additional methods (step2, step3, etc.) as is necessary.
	// Each step will be executed in a new HTTP request
}