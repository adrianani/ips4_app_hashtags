//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class hashtags_hook_S_Content_Comment extends _HOOK_CLASS_
{

/* 	public static function create( $item, $comment, $first=FALSE, $guestName=NULL, $incrementPostCount=NULL, $member=NULL, \IPS\DateTime $time=NULL, $ipAddress=NULL, $hiddenStatus=NULL ) {

		if ( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}

		$obj = parent::create($item, $comment, $first, $guestName, $incrementPostCount, $member, $time, $ipAddress, $hiddenStatus);

		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) ) {
			$columnContent = static::$databaseColumnMap['content'];
			$columnId = static::$databaseColumnId;
			$comment = preg_replace_callback(
				'/(^|\s|\B)(<(?<span>span) data-hashtag="(?<hashtag1>\w*(?:[^\x00-\x7F]|\pL)+\w*)" data-hashtag-id=\"(?<id>\pN+)\">)?(#(?<hashtag2>\w*(?:[^\x00-\x7F]|\pL)+\w*))(<\/\k<span>>)?($|\s|\b)/iu',
				function( $matches ) use ( $item, $member, $columnId, $obj ) {
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
					$itemColumnId = $item::$databaseColumnId;

					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[3],
							'meta_item_id' => $item->$itemColumnId,
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $member->member_id,
							'meta_node_id' => $item->container()->_id,
							'meta_comment_id' => $obj->$columnId,
							'created' => time(),
						]
					);

					return !empty($matches[4]) ? "{$matches[2]}{$matches[4]}</a>" : "{$matches[1]}<a href='{$url}'>{$matches[2]}</a>";
				},
				$comment
			);

			$obj->$columnContent = $comment;

			$obj->save();
		}

		return $obj;
	}

	public function editContents( $newContent ) {

		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) ) {

			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;
			$item = $this->item();
			$node = $item->container();
			$itemColumnId = $item::$databaseColumnId;
			$columnId = static::$databaseColumnId;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=? AND meta_comment_id=?",
					$item->$itemColumnId,
					$item::$application,
					$item::$module,
					$author,
					$node->_id,
					$this->$columnId,
				]
			);

			$newContent = preg_replace_callback( 
				'/(^|\s|\B)(<(?<span>span) data-hashtag="(?<hashtag1>\w*(?:[^\x00-\x7F]|\pL)+\w*)" data-hashtag-id=\"(?<id>\pN+)\">)?(#(?<hashtag2>\w*(?:[^\x00-\x7F]|\pL)+\w*))(<\/\k<span>>)?($|\s|\b)/iu',
				function( $matches ) use ( $node, $author, $item, $itemColumnId, $columnId ){
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
					
					\IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches[3],
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $item->$itemColumnId,
							'meta_comment_id' => $this->$columnId,
							'created' => time(),
						]
					);

					return !empty($matches[4]) ? "{$matches[2]}{$matches[4]}</a>" : "{$matches[1]}<a href='{$url}'>{$matches[2]}</a>";
				}, 
				$newContent
			);
		}

		parent::editContents( $newContent );
	} */

	public function save() {

		$columnAuthor = static::$databaseColumnMap['author'];
		$author = $this->$columnAuthor;
		$item = $this->item();
		$node = $item->container();
		$itemColumnId = $item::$databaseColumnId;
		$columnId = static::$databaseColumnId;
		$insertIds = [];

		if ( $this->_new )
		{
			$data = $this->_data;
		}
		else
		{
			$data = $this->changed;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=? AND meta_comment_id=?",
					$item->{$item::$databaseColumnId},
					$item::$application,
					$item::$module,
					$author,
					$node->_id,
					$this->$columnId,
				]
			);
		}

		if( !empty( $data[ static::$databaseColumnMap['content'] ] ) ) {
			$data[ static::$databaseColumnMap['content'] ] = preg_replace_callback( 
				'/(^|\s|\B)(<(?<span>span) data-hashtag="(?<hashtag1>\w*(?:[^\x00-\x7F]|\pL)+\w*)" data-hashtag-id=\"(?<id>\pN+)\">)?(#(?<hashtag2>\w*(?:[^\x00-\x7F]|\pL)+\w*))(<\/\k<span>>)?($|\s|\b)/iu',
				function( $matches ) use ( $node, $author, $item, $itemColumnId, $columnId, &$insertIds ){
					$url = \IPS\Http\Url::internal('app=hashtags&module=hashtags&controller=search&hashtag=' . $matches[3]);
					\IPS\Log::log($matches, 'test');
					$insertIds[] = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => !empty($matches[4]) ? "{$matches[3]}{$matches[5]}" : $matches[3],
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $item->$itemColumnId,
							'meta_comment_id' => $this->$columnId,
							'created' => time(),
						]
					);

					return !empty($matches[4]) ? "{$matches[2]}{$matches[5]}</a>" : "{$matches[1]}<a href='{$url}'>{$matches[2]}</a>";
				}, 
				$data[ static::$databaseColumnMap['content'] ]
			);
		}

		if ( $this->_new )
		{
			$this->_data = $data;
		}
		else
		{
			$this->changed = $data;
		}

		$wasNew = $this->_new;

		parent::save();

		if( $wasNew && !empty($insertIds) ) {
			
		  \IPS\Db::i()->update(
				'hashtags_hashtags',
				[
					'meta_comment_id' => $this->$columnId,
				],
				[
					"id IN (?)",
					implode( ',', $insertIds ),
				]
			);
		}
	}

	public function delete() {

		if( $this instanceof \IPS\Content\Searchable ) {
			$columnAuthor = static::$databaseColumnMap['author'];
			$author = $this->$columnAuthor;
			$item = $this->item();
			$node = $item->container();
			$itemColumnId = $item::$databaseColumnId;
			$columnId = static::$databaseColumnId;

			\IPS\Db::i()->delete(
				'hashtags_hashtags',
				[
					"meta_item_id=? AND meta_app=? AND meta_module=? AND meta_member_id=? AND meta_node_id=? AND meta_comment_id=?",
					$item->$itemColumnId,
					$item::$application,
					$item::$module,
					$author,
					$node->_id,
					$this->$columnId,
				]
			);
		}

		parent::delete();
	}

}
