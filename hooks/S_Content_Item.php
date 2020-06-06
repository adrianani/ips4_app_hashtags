//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class hashtags_hook_S_Content_Item extends _HOOK_CLASS_
{

	public static function createFromForm( $values, \IPS\Node\Model $container = NULL, $sendNotification = TRUE ) {
		$tagInserts = [];

		if( isset( static::$databaseColumnMap['content'] ) ) {
			$values[ static::$formLangPrefix . static::$databaseColumnMap['content'] ] = preg_replace_callback( 
				'/(^|\s)(#(([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+)))/iu',
				function( $matches ) use ( $container, $tagInserts ){
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
					$member = \IPS\Member::loggedIn();
	
					$tagInserts[] = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[3],
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_parent_id' => $container->_id,
							'meta_member_id' => $member->member_id ?: 0,
							'created' => time(),
						]
					);
	
					return "{$matches[1]}<a a href='{$url}' rel='tag'>{$matches[2]}</a>";
				}, 
				$values[ static::$formLangPrefix . static::$databaseColumnMap['content'] ]
			);
		}

		$obj = parent::createFromForm( $values, $container, $sendNotification );

		if( !empty($tagInserts) ) {
			$columnId = $obj::$databaseColumnId;
			\IPS\Db::i()->update(
				'hashtags_hashtags',
				[
					'meta_id' => $obj->$columnId,
				],
				"id IN (" . implode(',', $tagInserts) . ")"
			);
		}

		return $obj;
	}

	/* public function processAfterEdit( $values ) {

		$columnId = static::$databaseColumnId;
		$container = $this->container();
		$columnAuthor = static::$databaseColumnMap['author'];

		\IPS\Log::log($this->$columnAuthor, 'authorID');
		$author = \IPS\Member::load( $this->$columnAuthor );

		\IPS\Log::log($author, 'authorData');

		\IPS\Db::i()->delete(
			'hashtags_hashtags',
			[
				"meta_id=? AND meta_app=? AND meta_module=? AND meta_parent_id=? AND meta_member_id=?",
				$this->$columnId,
				static::$application,
				static::$module,
				$container->_id,
				$author->member_id ?: 0,
			]
		);

		if ( isset( static::$commentClass ) and static::$firstCommentRequired ) {
			$comment = $this->firstComment();
			$contentColumn = $comment::$databaseColumnMap['content'];

			$comment->$contentColumn = preg_replace_callback( 
				'/(^|\s)(#(([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+)))/iu',
				function( $matches ) use ( $author, $container, $columnId ) {
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
	
					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[3],
							'meta_id' => $this->$columnId,
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_parent_id' => $container->_id,
							'meta_member_id' => $author->member_id ?: 0,
							'created' => time(),
						]
					);
	
					return "{$matches[1]}<a a href='{$url}' rel='tag'>{$matches[2]}</a>";
				}, 
				$values[ static::$formLangPrefix . 'content' ]
			);

			$comment->save();
		} else {
			$contentColumn = static::$databaseColumnMap['content'];
			
			$this->$contentColumn = preg_replace_callback( 
				'/(^|\s)(#(([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+)))/iu',
				function( $matches ) use ( $author, $container, $columnId ) {
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
	
					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[3],
							'meta_id' => $this->$columnId,
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_parent_id' => $container->_id,
							'meta_member_id' => $author->member_id ?: 0,
							'created' => time(),
						]
					);
	
					return "{$matches[1]}<a a href='{$url}' rel='tag'>{$matches[2]}</a>";
				}, 
				$values[ static::$formLangPrefix . 'content' ]
			);

			$this->save();
		}

		parent::processAfterEdit($values);
	} */

}
