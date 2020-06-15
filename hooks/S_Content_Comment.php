//<?php

/* To prevent PHP errors (extending class does not exist) revealing path */
if ( !\defined( '\IPS\SUITE_UNIQUE_KEY' ) )
{
	exit;
}

abstract class hashtags_hook_S_Content_Comment extends _HOOK_CLASS_
{

	public static function create( $item, $comment, $first=FALSE, $guestName=NULL, $incrementPostCount=NULL, $member=NULL, \IPS\DateTime $time=NULL, $ipAddress=NULL, $hiddenStatus=NULL ) {

		if ( $member === NULL )
		{
			$member = \IPS\Member::loggedIn();
		}

		$obj = parent::create($item, $comment, $first, $guestName, $incrementPostCount, $member, $time, $ipAddress, $hiddenStatus);

		if( \in_array( 'IPS\Content\Searchable', class_implements( \get_called_class() ) ) ) {
			$columnContent = static::$databaseColumnMap['content'];
			$columnId = static::$databaseColumnId;
			$comment = preg_replace_callback( 
				'/(?<start>^|\s|\B)(<(?<span>span) data-hashtag=[^>]*>)?((?!(#[a-fA-F0-9]{3})(\W|$)|(#[a-fA-F0-9]{6})(\W|$))#(?<hashtag>\w*(?:[^\x00-\x7F]|\pL)+\w*))(<\/\k<span>>)?/iu',
				function( $matches ) use ( $item, $member, $columnId, $obj ) {
	
					$hashtagId = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches['hashtag'],
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $member->member_id,
							'meta_node_id' => $item->container()->_id,
							'meta_item_id' => $item->{$item::$databaseColumnId},
							'meta_comment_id' => $obj->$columnId,
							'created' => time(),
						]
					);

					return "{$matches['start']}<span data-hashtag=\"{$matches['hashtag']}\" data-hashtag-id=\"{$hashtagId}\">#{$matches['hashtag']}</span>";
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

			\IPS\Log::log($newContent);

			$newContent = preg_replace_callback( 
				'/(?<start>^|\s|\B)(<(?<span>span) data-hashtag=[^>]*>)?((?!(#[a-fA-F0-9]{3})(\W|$)|(#[a-fA-F0-9]{6})(\W|$))#(?<hashtag>\w*(?:[^\x00-\x7F]|\pL)+\w*))(<\/\k<span>>)?/iu',
				function( $matches ) use ( $node, $author, $item, $itemColumnId, $columnId ) {
					
					$hashtagId = \IPS\Db::i()->insert(
						'hashtags_hashtags',
						[
							'hashtag' => $matches['hashtag'],
							'meta_app' => $item::$application,
							'meta_module' => $item::$module,
							'meta_member_id' => $author,
							'meta_node_id' => $node->_id,
							'meta_item_id' => $item->$itemColumnId,
							'meta_comment_id' => $this->$columnId,
							'created' => time(),
						]
					);

					\IPS\Log::log($matches);

					return "{$matches['start']}<span data-hashtag=\"{$matches['hashtag']}\" data-hashtag-id=\"{$hashtagId}\">#{$matches['hashtag']}</span>";
				}, 
				$newContent
			);
		}

		parent::editContents( $newContent );
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
