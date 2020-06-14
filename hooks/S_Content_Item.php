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
					'/(?<start>^|\s|\B)((?!(#[a-fA-F0-9]{3})(\W|$)|(#[a-fA-F0-9]{6})(\W|$))#(?<hashtag>\w*(?:[^\x00-\x7F]|\pL)+\w*))(?<end>$|\s|\b)/iu',
					function( $matches ) use ( $container, $tagInserts ){
						
						$member = \IPS\Member::loggedIn();
		
						$tagInserts[] = \IPS\Db::i()->insert(
							'hashtags_hashtags',
							[
								'hashtag' => $matches['hashtag'],
								'meta_app' => static::$application,
								'meta_module' => static::$module,
								'meta_member_id' => $member->member_id,
								'meta_node_id' => $container->{$container::$databaseColumnId},
								'created' => time(),
							]
						);

						$hashtagId = end($tagInserts);

						return "{$matches['start']}<span data-hashtag=\"{$matches['hashtag']}\" data-hashtag-id=\"{$hashtagId}\">#{$matches['hashtag']}</span>{$matches['end']}";
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

				$values[ static::$formLangPrefix . $columnContent ] = preg_replace_callback( 
					'/(?<start>^|\s|\B)((?!(#[a-fA-F0-9]{3})(\W|$)|(#[a-fA-F0-9]{6})(\W|$))#(?<hashtag>\w*(?:[^\x00-\x7F]|\pL)+\w*))(?<end>$|\s|\b)/iu',
					function( $matches ) use ( $node, $author, $columnId ) {
		
						$hashtagId = \IPS\Db::i()->insert(
							'hashtags_hashtags',
							[
								'hashtag' => $matches['hashtag'],
								'meta_app' => static::$application,
								'meta_module' => static::$module,
								'meta_member_id' => $author,
								'meta_node_id' => $node->_id,
								'meta_item_id' => $this->$columnId,
								'created' => time(),
							]
						);

						return "{$matches['start']}<span data-hashtag=\"{$matches['hashtag']}\" data-hashtag-id=\"{$hashtagId}\">#{$matches['hashtag']}</span>{$matches['end']}";
					}, 
					$values[ static::$formLangPrefix . $columnContent ]
				);

				$this->$columnContent = $values[ static::$formLangPrefix . $columnContent ];

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
				'/(?<start>^|\s|\B)((?!(#[a-fA-F0-9]{3})(\W|$)|(#[a-fA-F0-9]{6})(\W|$))#(?<hashtag>\w*(?:[^\x00-\x7F]|\pL)+\w*))(?<end>$|\s|\b)/iu',
				function( $matches ) use ( $author, $node, $columnId, $comment, $commentColumnId ) {

					$hashtagId = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches['hashtag'],
							'meta_app' => static::$application,
							'meta_module' => static::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $this->$columnId,
							'meta_comment_id' => $comment->$commentColumnId,
							'created' => time(),
						]
					);

					return "{$matches['start']}<span data-hashtag=\"{$matches['hashtag']}\" data-hashtag-id=\"{$hashtagId}\">#{$matches['hashtag']}</span>{$matches['end']}";
				}, 
				$values[ static::$formLangPrefix . 'content' ]
			);
		}

		parent::processAfterEdit($values);
	}

	public function delete() {

		if( $this instanceof \IPS\Content\Searchable ) {
			$columnId = static::$databaseColumnId;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=?",
					$this->$columnId,
					static::$application,
					static::$module
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
