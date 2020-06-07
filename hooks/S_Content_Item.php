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

		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) ) {

			if( isset( static::$databaseColumnMap['content'] ) ) {
				$values[ static::$formLangPrefix . static::$databaseColumnMap['content'] ] = preg_replace_callback( 
					'/([^\p{L}])(<(a|b|i|u|s|em|strong)([\sa-z0-9=\x{0022}\x{0027}:\/\.?&\-_]+)?>)?(#([\p{L}_]+([0-9_]*)|(?:[0-9_]*)[\p{L}_]+))(<\/(a|b|i|u|s|em|strong)>)?(<\/|\b|\s|!|\?|\.|,|$)/iu',
					function( $matches ) use ( $container, $tagInserts ){
						$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[6]);
						$member = \IPS\Member::loggedIn();
		
						$tagInserts[] = \IPS\Db::i()->insert(
							'hashtags_hashtags',
							[
								'hashtag' => $matches[6],
								'meta_app' => static::$application,
								'meta_module' => static::$module,
								'meta_member_id' => $member->member_id,
								'meta_node_id' => $container->_id,
								'created' => time(),
							]
						);
		
						if( $matches[3] === 'a' ) {
							return "{$matches[1]}<a href='{$url}'>{$matches[5]}</a>{$matches[10]}";
						} else {
							return "{$matches[1]}{$matches[2]}<a href='{$url}'>{$matches[5]}</a>{$matches[8]}{$matches[10]}";
						}
					}, 
					$values[ static::$formLangPrefix . static::$databaseColumnMap['content'] ]
				);
			}
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
		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) && !$this->_new ) {
			$columnId = static::$databaseColumnId;
			$node = $this->container();
			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;
			$comment = ( isset( static::$commentClass ) and static::$firstCommentRequired ) ? $this->firstComment() : 0;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=? AND meta_comment_id=?",
					$this->$columnId,
					static::$application,
					static::$module,
					$author,
					$node->_id,
					($comment === 0) ?: $comment->{$comment::$databaseColumnId},
				]
			);

			if(isset( static::$databaseColumnMap['content'] )) {
				$columnContent = static::$databaseColumnMap['content'];

				$this->$columnContent = preg_replace_callback( 
					'/([^\p{L}])(<(a|b|i|u|s|em|strong)([\sa-z0-9=\x{0022}\x{0027}:\/\.?&\-_]+)?>)?(#([\p{L}_]+([0-9_]*)|(?:[0-9_]*)[\p{L}_]+))(<\/(a|b|i|u|s|em|strong)>)?(<\/|\b|\s|!|\?|\.|,|$)/iu',
					function( $matches ) use ( $node, $author, $columnId ){
						$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[6]);
		
						\IPS\Db::i()->insert(
							'hashtags_hashtags',
							[
								'hashtag' => $matches[6],
								'meta_app' => static::$application,
								'meta_module' => static::$module,
								'meta_member_id' => $author,
								'meta_node_id' => $node->_id,
								'meta_item_id' => $this->$columnId,
								'created' => time(),
							]
						);
		
						if( $matches[3] === 'a' ) {
							return "{$matches[1]}<a href='{$url}'>{$matches[5]}</a>{$matches[10]}";
						} else {
							return "{$matches[1]}{$matches[2]}<a href='{$url}'>{$matches[5]}</a>{$matches[8]}{$matches[10]}";
						}
					}, 
					$values[ static::$formLangPrefix . $columnContent ]
				);

			}
		}

		parent::processForm($values);
	}

	public function processAfterEdit( $values ) {
		if( 
			\in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) && 
			isset( static::$commentClass ) && 
			static::$firstCommentRequired 
		) {
			$comment = $this->firstComment();
			$commentAuhtorColumn = $comment::$databaseColumnMap['author'];
			$author = $comment->$commentAuhtorColumn;
			$node = $this->container();
			$columnId = static::$databaseColumnId;
			$commentColumnId = $comment::$databaseColumnId;

			$values[ static::$formLangPrefix . 'content' ] = preg_replace_callback( 
				'/([^\p{L}])(<(a|b|i|u|s|em|strong)([\sa-z0-9=\x{0022}\x{0027}:\/\.?&\-_]+)?>)?(#([\p{L}_]+([0-9_]*)|(?:[0-9_]*)[\p{L}_]+))(<\/(a|b|i|u|s|em|strong)>)?(<\/|\b|\s|!|\?|\.|,|$)/iu',
				function( $matches ) use ( $author, $node, $columnId, $comment, $commentColumnId ) {
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[6]);

					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[6],
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $this->$columnId,
							'meta_comment_id' => $comment->$commentColumnId,
							'created' => time(),
						]
					);

					if( $matches[3] === 'a' ) {
						return "{$matches[1]}<a href='{$url}'>{$matches[5]}</a>{$matches[10]}";
					} else {
						return "{$matches[1]}{$matches[2]}<a href='{$url}'>{$matches[5]}</a>{$matches[8]}{$matches[10]}";
					}
				}, 
				$values[ static::$formLangPrefix . 'content' ]
			);
		}

		parent::processAfterEdit($values);
	}

	public function delete() {

		if( $this instanceof \IPS\Content\Searchable ) {
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
					$node->_id,
				]
			);
		}

		parent::delete();
	}

	public function move( \IPS\Node\Model $container, $keepLink=FALSE ) {

		if( $this instanceof \IPS\Content\Searchable ) {

			$columnId = static::$databaseColumnId;
			$node = $this->container();
			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;

			\IPS\Db::i()->update(
				'hashtags_hashtags',
				[
					'meta_node_id' => $container->{$container::$databaseColumnId}
				],
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=?",
					$this->$columnId,
					static::$application,
					static::$module,
					$author,
					$node->{$node::$databaseColumnId},
				]
			);
		}

		parent::move( $container, $keepLink );
	}

	public function mergeIn( array $items, $keepLinks=FALSE ) {
		
		if( $this instanceof \IPS\Content\Searchable ) {
	
			$columnId = static::$databaseColumnId;
			$node = $this->container();
			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;

			foreach( $items as $item ) {
				\IPS\Db::i()->update(
					'hashtags_hashtags',
					[
						'meta_item_id' => $this->$columnId,
					],
					[
						"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=?",
						$item->{$item::$databaseColumnId},
						static::$application,
						static::$module,
						$author,
						$node->_id,
					]
				);
			}

		}

		parent::mergeIn($items, $keepLinks);
	}

}
