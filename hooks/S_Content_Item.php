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
				'/(^|\s)(<a (([a-zA-Z]+)=(\u0027|\u0022)([^\u0022]*|[^\u0027]*)(\u0027|\u0022)*)>)?(#([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+))(<\/a>)?/iu',
				function( $matches ) use ( $container, $tagInserts ){
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[9]);
					$member = \IPS\Member::loggedIn();
	
					$tagInserts[] = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[9],
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_member_id' => $member->member_id,
							'meta_node_id' => $container->_id,
							'created' => time(),
						]
					);
	
					return "{$matches[1]}<a href='{$url}'>{$matches[8]}</a>";
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
					'meta_item_id' => $obj->$columnId,
				],
				"id IN (" . implode(',', $tagInserts) . ")"
			);
		}

		return $obj;
	}

	public function processForm( $values ) {
		if( !$this->_new ) {
			$columnId = static::$databaseColumnId;
			$node = $this->container();
			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=?",
					$this->$columnId,
					static::$application,
					static::$module,
					$author,
					$node->_id
				]
			);

			if(isset( static::$databaseColumnMap['content'] )) {
				$columnContent = static::$databaseColumnMap['content'];

				$this->$columnContent = preg_replace_callback( 
					'/(^|\s)(<a (([a-zA-Z]+)=(\u0027|\u0022)([^\u0022]*|[^\u0027]*)(\u0027|\u0022)*)>)?(#([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+))(<\/a>)?/iu',
					function( $matches ) use ( $node, $author, $columnId ){
						$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[9]);
		
						$tagInserts[] = \IPS\Db::i()->insert(
							'hashtags_hashtags',
							[
								'hashtag' => $matches[9],
								'meta_app' => static::$application,
								'meta_module' => static::$module,
								'meta_member_id' => $author,
								'meta_node_id' => $node->_id,
								'meta_item_id' => $this->$columnId,
								'created' => time(),
							]
						);
		
						return "{$matches[1]}<a href='{$url}'>{$matches[8]}</a>";
					}, 
					$values[ static::$formLangPrefix . $columnContent ]
				);

			}
		}

		parent::processForm($values);
	}

	public function processAfterEdit( $values ) {
		if( isset( static::$commentClass ) and static::$firstCommentRequired ) {
			$comment = $this->firstComment();
			$commentAuhtorColumn = $comment::$databaseColumnMap['author'];
			$author = $comment->$commentAuhtorColumn;
			$node = $this->container();
			$columnId = static::$databaseColumnId;
			$commentColumnId = $comment::$databaseColumnId;

			$values[ static::$formLangPrefix . 'content' ] = preg_replace_callback( 
				'/(^|\s)(<a (([a-zA-Z]+)=(\u0027|\u0022)([^\u0022]*|[^\u0027]*)(\u0027|\u0022)*)>)?(#([a-zA-Z]([\w]+)|([0-9]+)[a-zA-Z]+))(<\/a>)?/iu',
				function( $matches ) use ( $author, $node, $columnId, $comment, $commentColumnId ){
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[9]);

					$tagInserts[] = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[9],
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $this->$columnId,
							'meta_comment_id' => $comment->$commentColumnId,
							'created' => time(),
						]
					);

					return "{$matches[1]}<a href='{$url}'>{$matches[8]}</a>";
				}, 
				$values[ static::$formLangPrefix . 'content' ]
			);
		}

		parent::processAfterEdit($values);
	}

}
